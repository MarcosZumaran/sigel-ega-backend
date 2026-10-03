<?php

namespace App\Services;

use Paperdoc\Support\DocumentManager;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PrinsFrank\PdfParser\PdfParser;

/**
 * Servicio unificado para lectura de documentos.
 * Soporta PDF, DOCX y XLSX.
 */
class DocumentReaderService
{
    /**
     * Extrae texto de un PDF.
     */
    public function leerPdf(string $ruta): string
    {
        $document = (new PdfParser())->parseFile($ruta);

        return $document->getText();
    }

    /**
     * Extrae texto de un DOCX.
     * Retorna un array de elementos estructurados (párrafos, tablas).
     */
    public function leerDocx(string $ruta): array
    {
        $doc = DocumentManager::open($ruta);
        $elementos = [];

        foreach ($doc->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $elementos[] = $element;
            }
        }

        return $elementos;
    }

    /**
     * Lee un XLSX y retorna las filas como array.
     */
    public function leerXlsx(string $ruta, bool $soloDatos = true): array
    {
        $reader = SpreadsheetIOFactory::createReaderForFile($ruta);
        if ($soloDatos) {
            $reader->setReadDataOnly(true);
        }
        $spreadsheet = $reader->load($ruta);
        $sheet = $spreadsheet->getActiveSheet();

        return $sheet->toArray(null, true, true, true);
    }

    /**
     * Detecta el tipo de archivo y lo lee con el método correspondiente.
     */
    public function leer(string $ruta): mixed
    {
        $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => $this->leerPdf($ruta),
            'docx' => $this->leerDocx($ruta),
            'xlsx', 'xls' => $this->leerXlsx($ruta),
            default => throw new \InvalidArgumentException("Formato no soportado: {$ext}"),
        };
    }
}
