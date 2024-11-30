<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CorrectedInvoicesExport implements FromArray, WithHeadings, WithStyles
{
    protected $processedData;
    protected $aggregations;

    public function __construct(array $processedData, array $aggregations)
    {
        $this->processedData = $processedData;
        // $this->aggregations = $aggregations;
    }

    public function array(): array
    {
        // Remover las columnas _highlight
        $dataWithoutHighlights = array_map(function ($row) {
            return array_filter($row, function ($key) {
                return !str_contains($key, '_highlight');
            }, ARRAY_FILTER_USE_KEY);
        }, $this->processedData);

        // Crear una fila para agregaciones inicializada con valores vacíos
        $aggregationRow = array_fill_keys(array_keys($dataWithoutHighlights[0]), null);

        return $dataWithoutHighlights; // No añadimos la fila de agregaciones directamente aquí
    }

    public function headings(): array
    {
        // Excluir cabeceras que contienen _highlight
        return array_filter(array_keys($this->processedData[0]), function ($key) {
            return !str_contains($key, '_highlight');
        });
    }

    public function styles(Worksheet $sheet)
    {
        $totalRows = count($this->processedData) + 1; // +1 para incluir la fila de encabezados

        // Aplicar estilos personalizados para las celdas coloreadas
        foreach ($this->processedData as $index => $row) {
            $rowIndex = $index + 2; // +2 porque Excel comienza en 1 y hay cabeceras

            // Verificar si la fila tiene un 'row_highlight'
            if (isset($row['row_highlight']) && $row['row_highlight']) {
                // Buscar las columnas con valores no vacíos en la fila
                $nonEmptyColumns = array_filter($row, function ($value) {
                    return !is_null($value) && $value !== '';
                });

                // Si la fila tiene columnas no vacías, aplicar el color de fila a esas columnas
                foreach ($nonEmptyColumns as $key => $value) {
                    $columnIndex = array_search($key, array_keys($row)) + 1; // Posición en la fila
                    $sheet->getStyleByColumnAndRow($columnIndex, $rowIndex)
                        ->getFill()->setFillType('solid')->getStartColor()->setARGB($row['row_highlight']);
                }
            }

            // Aplicar colores a celdas individuales basadas en '_highlight'
            foreach ($row as $key => $value) {
                if (str_contains($key, '_highlight') && $value) {
                    $baseField = str_replace('_highlight', '', $key);
                    $columnIndex = array_search($baseField, array_keys($row)) + 1; // Posición en la fila
                    $sheet->getStyleByColumnAndRow($columnIndex, $rowIndex)
                        ->getFill()->setFillType('solid')->getStartColor()->setARGB($value);
                }
            }
        }

        // Aplicar negrita a la fila de agregaciones
        $sheet->getStyle("A$totalRows:Z$totalRows")->getFont()->setBold(true);

        return [];
    }



    private function getColumnLetter(string $field, Worksheet $sheet)
    {
        $headings = $this->headings();
        $columnIndex = array_search($field, $headings);
        if ($columnIndex !== false) {
            return $sheet->getCellByColumnAndRow($columnIndex + 1, 1)->getColumn();
        }
        return null;
    }
}
