<?php

namespace App\Console\Commands;

use App\Models\NormaDetectada;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class CheckNormaTecnica extends Command
{
    protected $signature = 'sigel:check-norma-tecnica {anio : Año escolar a verificar} {--guardar : Guardar el resultado en normas_detectadas}';

    protected $description = 'Verifica si la Norma Técnica del año escolar está publicada en el repositorio MINEDU y extrae los rangos de bimestres';

    private const BASE = 'https://repositorio.minedu.gob.pe';

    public function handle(): int
    {
        $anio = (int) $this->argument('anio');

        try {
            $itemUrl = $this->buscarItem($anio);
        } catch (\Throwable $e) {
            $this->error('Error de red consultando el repositorio: ' . $e->getMessage());
            Log::warning('sigel:check-norma-tecnica error de red', ['anio' => $anio, 'error' => $e->getMessage()]);
            return self::FAILURE;
        }

        if ($itemUrl === null) {
            $this->line('Aún no publicada');
            if ($this->option('guardar')) {
                NormaDetectada::query()->updateOrCreate(
                    ['anio' => $anio],
                    ['url_pdf' => null, 'fechas_json' => null, 'estado' => 'no_publicada']
                );
            }
            return self::SUCCESS;
        }

        $this->info("Norma encontrada: {$itemUrl}");

        try {
            $pdfUrl = $this->obtenerPdfUrl($itemUrl);
            $pdfPath = $this->descargarPdf($pdfUrl);
        } catch (\Throwable $e) {
            $this->error('Error descargando o procesando el PDF: ' . $e->getMessage());
            Log::warning('sigel:check-norma-tecnica error PDF', ['anio' => $anio, 'error' => $e->getMessage()]);
            return self::FAILURE;
        }

        try {
            $rangos = [];
            $texto = $this->textoPdfParser($pdfPath);
            if (trim($texto) !== '') {
                $rangos = $this->extraerRangos($texto);
                if (! $this->rangosValidos($rangos)) {
                    $rangos = [];
                }
            }
            if ($rangos === []) {
                $this->info('OCR-BANDAS-v2...');
                $rangos = $this->extraerRangosOcr($pdfPath);
            }
        } finally {
            $this->limpiarDir(dirname($pdfPath));
        }

        if (! $this->rangosValidos($rangos)) {
            $this->warn('No se pudieron extraer los 4 rangos de bimestres del PDF.');
            if ($this->option('guardar')) {
                NormaDetectada::query()->updateOrCreate(
                    ['anio' => $anio],
                    ['url_pdf' => $pdfUrl, 'fechas_json' => $rangos === [] ? null : $rangos, 'estado' => $rangos === [] ? 'sin_rangos' : 'revision_manual']
                );
            }
            return self::FAILURE;
        }

        foreach ($rangos as $i => $r) {
            $this->line('B' . ($i + 1) . ": {$r['inicio']} - {$r['fin']}");
        }

        if ($this->option('guardar')) {
            NormaDetectada::query()->updateOrCreate(
                ['anio' => $anio],
                ['url_pdf' => $pdfUrl, 'fechas_json' => $rangos, 'estado' => 'detectada']
            );
        }

        Log::info('sigel:check-norma-tecnica norma detectada', ['anio' => $anio, 'url' => $pdfUrl, 'rangos' => $rangos]);

        return self::SUCCESS;
    }

    private function buscarItem(int $anio): ?string
    {
        $resp = Http::timeout(60)->get(self::BASE . '/discover', [
            'query' => "Norma técnica para el año escolar {$anio}",
        ]);
        $resp->throw();

        if (! preg_match_all('#<a href="(/handle/[^"]+)"[^>]*>(.*?)</a>#s', $resp->body(), $m, PREG_SET_ORDER)) {
            return null;
        }

        $objetivo = $this->normalizar("año escolar {$anio}");
        $candidatos = [];
        foreach ($m as [, $href, $inner]) {
            $titulo = trim(html_entity_decode(strip_tags($inner), ENT_QUOTES, 'UTF-8'));
            if (strlen($titulo) < 20 || ! str_contains($this->normalizar($titulo), $objetivo)) {
                continue;
            }
            $candidatos[] = [$href, $titulo];
        }

        if ($candidatos === []) {
            return null;
        }

        foreach ($candidatos as [$href, $titulo]) {
            if (str_starts_with($this->normalizar($titulo), 'norma t')) {
                return self::BASE . $href;
            }
        }

        return self::BASE . $candidatos[0][0];
    }

    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower($texto, 'UTF-8');
        $texto = strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n']);

        return $texto;
    }

    private function obtenerPdfUrl(string $itemUrl): string
    {
        $resp = Http::timeout(30)->get($itemUrl);
        $resp->throw();
        $html = $resp->body();

        if (preg_match('#<meta name="citation_pdf_url" content="([^"]+)"#', $html, $m)) {
            return html_entity_decode($m[1]);
        }

        if (preg_match('#href="(/bitstream/handle/[^"]+?\.pdf[^"]*)"#i', $html, $b)) {
            return self::BASE . html_entity_decode($b[1]);
        }

        throw new \RuntimeException('No se encontró el enlace al PDF en la página del ítem.');
    }

    private function descargarPdf(string $pdfUrl): string
    {
        $dir = sys_get_temp_dir() . '/norma_' . uniqid();
        mkdir($dir);
        $path = $dir . '/norma.pdf';
        $resp = Http::timeout(180)->get($pdfUrl);
        $resp->throw();
        file_put_contents($path, $resp->body());

        return $path;
    }

    private function limpiarDir(string $dir): void
    {
        foreach (glob($dir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($dir);
    }

    private function textoPdfParser(string $pdfPath): string
    {
        try {
            return (new Parser())->parseFile($pdfPath)->getText();
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * OCR por bandas: renderiza a 400dpi, localiza las filas lectivas
     * (ordinal en una línea y "lectivas" en la misma o la siguiente,
     * pues el renglón se parte en dos) vía TSV, recorta cada banda,
     * la amplía al 200% y extrae el primer y último par (día, mes).
     *
     * @return array<int, array{inicio: string, fin: string}>
     */
    private function extraerRangosOcr(string $pdfPath): array
    {
        $dir = dirname($pdfPath);
        $prefijo = $dir . '/pg';
        exec('pdftoppm -r 400 -png ' . escapeshellarg($pdfPath) . ' ' . escapeshellarg($prefijo) . ' 2>&1', $out, $code);
        if ($code !== 0) {
            throw new \RuntimeException('pdftoppm falló: ' . implode("\n", $out));
        }

        $ordinales = ['primer' => 1, 'segundo' => 2, 'tercer' => 3, 'cuarto' => 4];
        $porPagina = [];

        foreach (glob($prefijo . '-*.png') ?: [] as $png) {
            $filas = array_values($this->filasTsv($png));
            foreach ($filas as $i => $fila) {
                $norm = $this->normalizar($fila['texto']);
                $sigTexto = $filas[$i + 1]['texto'] ?? '';
                $sigNorm = $this->normalizar($sigTexto);
                foreach ($ordinales as $palabra => $num) {
                    if (isset($porPagina[$png][$num]) || ! str_contains($norm, $palabra)) {
                        continue;
                    }
                    $yLect = null;
                    if (str_contains($norm, 'lectivas') && str_contains($fila['texto'], '09')) {
                        $yLect = $fila['y'];
                    } elseif (str_contains($sigNorm, 'lectivas') && str_contains($sigTexto, '09')) {
                        $yLect = $filas[$i + 1]['y'];
                    }
                    if ($yLect !== null) {
                        $porPagina[$png][$num] = ['png' => $png, 'y' => $fila['y'], 'y2' => $yLect];
                    }
                }
            }
        }

        foreach ($porPagina as $png => $bandas) {
            if (count($bandas) !== 4) {
                continue;
            }
            ksort($bandas);
            $rangos = [];
            foreach ($bandas as $num => $banda) {
                $y0 = (int) (min((float) $banda['y'], (float) $banda['y2']) - 100);
                $y1 = (int) (max((float) $banda['y'], (float) $banda['y2']) + 150);
                $texto = $this->ocrBandaRango($banda['png'], $y0, $y1);
                $pares = $this->paresFecha($texto);
                if (getenv('NORMA_DEBUG')) {
                    fwrite(STDERR, "DEBUG banda $num y=$y0-$y1 pares=" . json_encode($pares) . "\ntexto: " . substr(preg_replace('/\s+/', ' ', $texto), 0, 200) . "\n");
                }
                $fin = $this->seleccionarFin($pares);
                if ($fin === null) {
                    continue 2;
                }
                $rangos[] = [
                    'inicio' => sprintf('%02d-%02d', $pares[0][1], $pares[0][0]),
                    'fin' => sprintf('%02d-%02d', $fin[1], $fin[0]),
                ];
            }
            if ($this->rangosValidos($rangos)) {
                return $rangos;
            }
        }

        return [];
    }

    /**
     * El inicio es el primer par; el fin es el primer par posterior
     * cronológicamente mayor (evita colas de filas vecinas).
     *
     * @param array<int, array{0: int, 1: int}> $pares
     * @return array{0: int, 1: int}|null
     */
    private function seleccionarFin(array $pares): ?array
    {
        if ($pares === []) {
            return null;
        }
        [$dia0, $mes0] = $pares[0];
        foreach (array_slice($pares, 1) as [$dia, $mes]) {
            if ($mes > $mes0 || ($mes === $mes0 && $dia > $dia0)) {
                return [$dia, $mes];
            }
        }

        return null;
    }

    /** @return array<int, array{texto: string, y: float, h: float}> */
    private function filasTsv(string $png): array
    {
        exec('tesseract ' . escapeshellarg($png) . ' stdout -l spa --psm 6 tsv 2>/dev/null', $lines, $code);
        if ($code !== 0) {
            return [];
        }

        $lineas = [];
        foreach ($lines as $line) {
            $c = explode("\t", $line);
            if (count($c) < 12 || ($c[0] ?? '') !== '5' || trim($c[11] ?? '') === '') {
                continue;
            }
            $clave = $c[1] . '-' . $c[2] . '-' . $c[3] . '-' . $c[4];
            $lineas[$clave]['palabras'][] = $c[11];
            $lineas[$clave]['ys'][] = (float) $c[7] + (float) $c[9] / 2;
        }

        $filas = [];
        foreach ($lineas as $linea) {
            $filas[] = [
                'texto' => implode(' ', $linea['palabras']),
                'y' => array_sum($linea['ys']) / count($linea['ys']),
                'h' => 0,
            ];
        }

        return $filas;
    }

    private function ocrBandaRango(string $png, int $y0, int $y1): string
    {
        $info = getimagesize($png);
        if ($info === false) {
            return '';
        }
        [$ancho, $alto] = $info;
        $y0 = max(0, $y0);
        $altura = min($alto - $y0, max(120, $y1 - $y0));
        $banda = $png . '_banda.png';
        $magick = trim((string) shell_exec('command -v magick || command -v convert'));

        exec($magick . ' ' . escapeshellarg($png) . " -crop {$ancho}x{$altura}+0+{$y0} -resize 200% " . escapeshellarg($banda) . ' 2>&1', $out, $code);
        if ($code !== 0 || ! is_file($banda)) {
            return '';
        }

        exec('tesseract ' . escapeshellarg($banda) . ' stdout -l spa --psm 4 2>/dev/null', $lines, $c);
        @unlink($banda);

        return $c === 0 ? implode("\n", $lines) : '';
    }

    private function ocrBanda(string $png, int $yCentro, int $dpi): string
    {
        $info = getimagesize($png);
        if ($info === false) {
            return '';
        }
        [$ancho, $alto] = $info;
        $y0 = max(0, $yCentro - 120);
        $altura = min($alto - $y0, 240);
        $banda = $png . '_banda.png';
        $magick = trim((string) shell_exec('command -v magick || command -v convert'));

        foreach ([
            $magick . ' ' . escapeshellarg($png) . " -crop {$ancho}x{$altura}+0+{$y0} -resize 200% " . escapeshellarg($banda),
        ] as $cmd) {
            exec($cmd . ' 2>&1', $out, $code);
            if ($code === 0 && is_file($banda)) {
                break;
            }
        }

        if (! is_file($banda)) {
            return '';
        }

        exec('tesseract ' . escapeshellarg($banda) . ' stdout -l spa --psm 6 2>/dev/null', $lines, $c);
        @unlink($banda);

        return $c === 0 ? implode("\n", $lines) : '';
    }

    /**
     * Extrae pares [día, mes] recorriendo las palabras-mes y buscando
     * hacia atrás (máx. 14 tokens) el día más cercano. Se rechaza un día
     * seguido de "semana(s)" (marcador de duración, no fecha) y se sigue
     * buscando. Así se resuelven día y mes partidos en renglones.
     *
     * @return array<int, array{0: int, 1: int}>
     */
    private function paresFecha(string $texto): array
    {
        $plano = preg_replace('/[^a-záéíóúñü0-9\s]/iu', ' ', $texto) ?? '';
        $tokens = preg_split('/\s+/', $plano, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $pares = [];
        foreach ($tokens as $i => $tok) {
            $num = $this->mesNumero($tok);
            if ($num === null) {
                continue;
            }
            for ($j = $i - 1; $j >= max(0, $i - 14); $j--) {
                if (! preg_match('/^\d{1,2}$/', $tokens[$j])) {
                    continue;
                }
                $siguiente = mb_strtolower($tokens[$j + 1] ?? '');
                if (str_starts_with($siguiente, 'seman')) {
                    continue;
                }
                $dia = (int) $tokens[$j];
                if ($dia >= 1 && $dia <= 31) {
                    $pares[] = [$dia, $num];
                }
                break;
            }
        }

        return $pares;
    }

    private function mesNumero(string $palabra): ?int
    {
        $meses = ['enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4, 'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8, 'septiembre' => 9, 'setiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12];
        $w = strtolower($palabra);
        $w = strtr($w, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);
        if (isset($meses[$w])) {
            return $meses[$w];
        }
        $mejor = null;
        $mejorDist = 3;
        foreach ($meses as $nombre => $num) {
            $d = levenshtein($w, $nombre);
            if ($d < $mejorDist) {
                $mejorDist = $d;
                $mejor = $num;
            }
        }

        return $mejorDist <= 2 ? $mejor : null;
    }

    /** @param array<int, array{inicio: string, fin: string}> $rangos */
    private function rangosValidos(array $rangos): bool
    {
        if (count($rangos) !== 4) {
            return false;
        }
        $anterior = '00-00';
        foreach ($rangos as $r) {
            if (! isset($r['inicio'], $r['fin'])) {
                return false;
            }
            if ($r['inicio'] > $r['fin'] || $r['inicio'] <= $anterior) {
                return false;
            }
            $anterior = $r['fin'];
        }

        return true;
    }

    private function extraerRangos(string $texto): array
    {
        $meses = 'enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|setiembre|octubre|noviembre|diciembre';
        $patron = "/(primer|segundo|tercer|cuarto)\\s+bloque[^\\d]*?(\\d{1,2})\\s+de\\s+({$meses})\\s+(?:al|a)\\s+(\\d{1,2})\\s+de\\s+({$meses})/iu";

        if (! preg_match_all($patron, $texto, $m, PREG_SET_ORDER)) {
            return [];
        }

        $num = ['enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4, 'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8, 'septiembre' => 9, 'setiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12];
        $rangos = [];
        foreach (array_slice($m, 0, 4) as $r) {
            $rangos[] = [
                'inicio' => sprintf('%02d-%02d', $num[strtolower($r[3])], (int) $r[2]),
                'fin' => sprintf('%02d-%02d', $num[strtolower($r[5])], (int) $r[4]),
            ];
        }

        return $rangos;
    }
}
