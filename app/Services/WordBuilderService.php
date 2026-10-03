<?php

namespace App\Services;

use App\Models\Configuracion;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Builder programático de documentos Word para SIGEL-EGA.
 *
 * Reemplaza Html::addHtml() (que destruye tablas y pierde estilos)
 * con construcción nativa de phpword.
 */
class WordBuilderService
{
    private const FUENTE = 'DejaVu Sans';
    private const COLOR_PRIMARIO = '1E3A8A';
    private const COLOR_TEXTO = '1E293B';
    private const COLOR_MUTED = '64748B';
    private const COLOR_BORDE = 'CBD5E1';
    private const COLOR_HEADER_BG = '1E3A8A';
    private const COLOR_FILA_ALTERNA = 'F8FAFC';
    private const COLOR_AD = 'D1FAE5';
    private const COLOR_A = 'DBEAFE';
    private const COLOR_B = 'FEF3C7';
    private const COLOR_C = 'FECACA';

    private PhpWord $phpWord;
    private $section;
    private string $orientacion;

    public function __construct(string $orientacion = 'portrait')
    {
        $this->orientacion = $orientacion;
        $this->phpWord = new PhpWord();
        $this->phpWord->setDefaultFontName(self::FUENTE);
        $this->phpWord->setDefaultFontSize(9);

        $this->section = $this->phpWord->addSection([
            'orientation' => $orientacion,
            'marginTop' => 500, 'marginBottom' => 500,
            'marginLeft' => 500, 'marginRight' => 500,
        ]);
    }

    /**
     * Agrega el membrete institucional (nombre IE, código modular, resolución, etc.).
     */
    public function addMembrete(string $titulo, ?string $subtitulo = null): self
    {
        $ie = $this->obtenerDatosIE();

        // Nombre IE
        $this->section->addText($ie['nombre'], [
            'name' => self::FUENTE, 'size' => 13, 'bold' => true, 'color' => self::COLOR_PRIMARIO,
        ], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

        // Línea de datos institucionales
        $linea2 = array_filter([
            $ie['codigo_modular'] ? 'Código Modular: '.$ie['codigo_modular'] : null,
            $ie['resolucion_creacion'] ? 'Resolución: '.$ie['resolucion_creacion'] : null,
            $ie['ugel'] ? 'UGEL: '.$ie['ugel'] : null,
            $ie['dre'] ? 'DRE: '.$ie['dre'] : null,
        ]);
        if (! empty($linea2)) {
            $this->section->addText(implode(' · ', $linea2), [
                'name' => self::FUENTE, 'size' => 8, 'color' => '334155',
            ], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        }

        // Dirección/teléfono
        $linea3 = array_filter([
            $ie['direccion'], $ie['telefono'] ? 'Tel: '.$ie['telefono'] : null, $ie['correo'],
        ]);
        if (! empty($linea3)) {
            $this->section->addText(implode(' · ', $linea3), [
                'name' => self::FUENTE, 'size' => 7.5, 'color' => self::COLOR_MUTED,
            ], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        }

        // Título del documento
        $this->section->addText($titulo, [
            'name' => self::FUENTE, 'size' => 12, 'bold' => true, 'color' => self::COLOR_PRIMARIO,
        ], ['alignment' => Jc::CENTER, 'spaceBefore' => 200]);

        if ($subtitulo) {
            $this->section->addText($subtitulo, [
                'name' => self::FUENTE, 'size' => 9, 'color' => '475569',
            ], ['alignment' => Jc::CENTER, 'spaceAfter' => 100]);
        }

        // Línea separadora
        $this->section->addText('', [], ['borderBottomSize' => 12, 'borderBottomColor' => self::COLOR_PRIMARIO]);

        return $this;
    }

    /**
     * Agrega una sección con título (barra azul).
     */
    public function addSeccionTitulo(string $titulo): self
    {
        $table = $this->section->addTable(['borderSize' => 0, 'cellMargin' => 60]);
        $table->addRow();
        $cell = $table->addCell(9000, ['bgColor' => self::COLOR_PRIMARIO]);
        $cell->addText($titulo, [
            'name' => self::FUENTE, 'size' => 9, 'bold' => true, 'color' => 'FFFFFF',
        ]);
        // Espacio después
        $this->section->addTextBreak(1);

        return $this;
    }

    /**
     * Agrega una tabla simple (campo → valor) o compleja (headers + rows).
     *
     * @param  array  $headers  Nombres de columnas (opcional, si se omite se asume 2 columnas)
     * @param  array  $rows     Filas de datos
     * @param  array  $opciones ['anchos' => [3000, 6000], 'colorear_celdas' => bool]
     */
    public function addTabla(array $headers, array $rows, array $opciones = []): self
    {
        $anchoTotal = $this->orientacion === 'landscape' ? 13500 : 9000;
        $numColumnas = ! empty($headers) ? count($headers) : (count($rows[0] ?? []) ?: 2);
        $anchoDefault = (int) ($anchoTotal / $numColumnas);
        $anchos = $opciones['anchos'] ?? array_fill(0, $numColumnas, $anchoDefault);

        $table = $this->section->addTable([
            'borderSize' => 6,
            'borderColor' => self::COLOR_BORDE,
            'cellMargin' => 80,
        ]);

        // Header row
        if (! empty($headers)) {
            $table->addRow();
            foreach ($headers as $i => $header) {
                $cell = $table->addCell($anchos[$i] ?? $anchoDefault, ['bgColor' => self::COLOR_HEADER_BG]);
                $cell->addText($header, [
                    'name' => self::FUENTE, 'size' => 8, 'bold' => true, 'color' => 'FFFFFF',
                ], ['alignment' => Jc::CENTER]);
            }
        }

        // Data rows
        foreach ($rows as $idx => $row) {
            $table->addRow();
            $valores = is_array($row) ? array_values($row) : [$row];

            foreach ($valores as $i => $valor) {
                $bgColor = ($idx % 2 === 1) ? self::COLOR_FILA_ALTERNA : null;

                // Colorear niveles CNEB si aplica
                $esNivelCneb = in_array($valor, ['AD', 'A', 'B', 'C'], true);
                if ($esNivelCneb && ($opciones['colorear_celdas'] ?? true)) {
                    $bgColor = match ($valor) {
                        'AD' => self::COLOR_AD, 'A' => self::COLOR_A,
                        'B' => self::COLOR_B, 'C' => self::COLOR_C,
                        default => $bgColor,
                    };
                }

                $estiloCelda = ['valign' => 'center'];
                if ($bgColor) {
                    $estiloCelda['bgColor'] = $bgColor;
                }

                $cell = $table->addCell($anchos[$i] ?? $anchoDefault, $estiloCelda);
                $texto = (string) ($valor ?? '');
                $fontStyle = ['name' => self::FUENTE, 'size' => 8, 'color' => self::COLOR_TEXTO];
                if ($esNivelCneb) {
                    $fontStyle['bold'] = true;
                }

                $cell->addText($texto, $fontStyle);
            }
        }

        return $this;
    }

    /**
     * Agrega firmas lado a lado.
     *
     * @param  array  $firmas [['nombre' => 'Docente Tutor', 'detalle' => 'Juan Pérez'], ...]
     */
    public function addFirmas(array $firmas): self
    {
        $this->section->addTextBreak(2);

        $anchoTotal = $this->orientacion === 'landscape' ? 13500 : 9000;
        $numFirmas = count($firmas) ?: 1;
        $anchoFirma = (int) ($anchoTotal / $numFirmas);

        $table = $this->section->addTable(['borderSize' => 0, 'cellMargin' => 60]);
        $table->addRow();

        foreach ($firmas as $firma) {
            $cell = $table->addCell($anchoFirma, ['borderSize' => 0, 'valign' => 'bottom']);
            $cell->addText('_________________________', [
                'name' => self::FUENTE, 'size' => 9, 'color' => '000000',
            ], ['alignment' => Jc::CENTER, 'spaceBefore' => 400]);
            $cell->addText($firma['nombre'] ?? '', [
                'name' => self::FUENTE, 'size' => 8, 'bold' => true, 'color' => self::COLOR_TEXTO,
            ], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
            if (! empty($firma['detalle'])) {
                $cell->addText($firma['detalle'], [
                    'name' => self::FUENTE, 'size' => 7, 'color' => self::COLOR_MUTED,
                ], ['alignment' => Jc::CENTER]);
            }
        }

        return $this;
    }

    /**
     * Agrega texto simple.
     */
    public function addTexto(string $texto, array $estilos = []): self
    {
        $this->section->addText($texto, array_merge([
            'name' => self::FUENTE, 'size' => 9, 'color' => self::COLOR_TEXTO,
        ], $estilos));

        return $this;
    }

    /**
     * Agrega el pie de página.
     */
    public function addPie(string $texto): self
    {
        $this->section->addText($texto, [
            'name' => self::FUENTE, 'size' => 7, 'color' => '94A3B8',
        ], ['alignment' => Jc::CENTER, 'spaceBefore' => 200]);

        return $this;
    }

    /**
     * Guarda el documento y retorna la ruta.
     */
    public function guardar(string $rutaDestino): string
    {
        $writer = IOFactory::createWriter($this->phpWord, 'Word2007');
        $writer->save($rutaDestino);

        return $rutaDestino;
    }

    private function obtenerDatosIE(): array
    {
        return [
            'nombre' => Configuracion::where('clave', 'nombre_institucion')->value('valor') ?? 'I.E. Pública EGA',
            'codigo_modular' => Configuracion::where('clave', 'codigo_modular')->value('valor'),
            'resolucion_creacion' => Configuracion::where('clave', 'resolucion_creacion')->value('valor'),
            'ugel' => Configuracion::where('clave', 'ugel')->value('valor'),
            'dre' => Configuracion::where('clave', 'dre')->value('valor'),
            'direccion' => Configuracion::where('clave', 'direccion')->value('valor'),
            'telefono' => Configuracion::where('clave', 'telefono')->value('valor'),
            'correo' => Configuracion::where('clave', 'correo')->value('valor'),
        ];
    }
}
