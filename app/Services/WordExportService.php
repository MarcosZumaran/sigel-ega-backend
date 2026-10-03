<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;

/**
 * Convierte vistas Blade (HTML) a documentos Word (.docx).
 *
 * ⚠️ Limitaciones de phpword + Html::addHtml():
 * - No soporta todos los estilos CSS (flex, grid, gradientes)
 * - Las tablas con estilos complejos pueden simplificarse
 * - Los bordes y colores se respetan si están inline o en style
 *
 * Para documentos oficiales (acta, nómina, boleta, FUM, orden) es suficiente.
 */
class WordExportService
{
    /**
     * Convierte un HTML completo a .docx y lo guarda en la ruta indicada.
     */
    public function convertirHtml(string $html, string $rutaDestino): string
    {
        $phpWord = new PhpWord();

        // Configurar fuente por defecto
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(9);

        // Crear sección
        $section = $phpWord->addSection([
            'marginTop' => 400,
            'marginBottom' => 400,
            'marginLeft' => 400,
            'marginRight' => 400,
        ]);

        // Convertir HTML a Word (errores internos para no ensuciar el log;
        // el parser de phpword es estricto con HTML de vistas Blade y la v1.4
        // emite E_DEPRECATED en PHP >= 8.4 por offsets nulos en Style.php,
        // tanto al parsear como al guardar, por eso cubre ambas llamadas)
        $prevReporting = error_reporting(E_ALL & ~E_DEPRECATED);
        $prev = libxml_use_internal_errors(true);
        try {
            Html::addHtml($section, $html, false, false);

            // Guardar
            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($rutaDestino);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
            error_reporting($prevReporting);
        }

        return $rutaDestino;
    }

    /**
     * Renderiza una vista Blade y la convierte a Word.
     *
     * @deprecated Todos los documentos oficiales ya migraron a WordBuilderService
     *             (FUM, acta, nómina, orden y boleta). Se conserva solo como
     *             fallback genérico HTML→DOCX.
     */
    public function desdeVista(string $vistaBlade, array $datos, string $rutaDestino): string
    {
        $html = view($vistaBlade, $datos)->render();

        return $this->convertirHtml($this->fragmentoBody($html), $rutaDestino);
    }

    /**
     * Html::addHtml() solo acepta fragmentos (contenido del <body>).
     * Extrae el body, elimina <style> (no soportados) y normaliza
     * etiquetas no cerradas (<br>, <hr>, <meta>) que rompen el parser XML.
     */
    private function fragmentoBody(string $html): string
    {
        if (preg_match('/<body[^>]*>(.*)<\/body>/is', $html, $m)) {
            $html = $m[1];
        }
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html);
        $html = str_ireplace(['<br>', '<hr>', '<meta>'], ['<br/>', '<hr/>', ''], $html);

        return trim($html);
    }
}
