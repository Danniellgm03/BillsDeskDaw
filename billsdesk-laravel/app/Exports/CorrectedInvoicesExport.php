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
        // Obtenemos todas las cabeceras
        $headings = $this->headings();

        // Filtrar las filas para remover columnas que contienen '_highlight'
        $dataWithoutHighlights = array_map(function ($row) use ($headings) {
            // Asegurarnos de que cada fila tenga todas las columnas
            $rowWithoutHighlights = [];

            foreach ($headings as $key) {
                // Si la clave existe en la fila, la agregamos; si no, asignamos null
                $rowWithoutHighlights[$key] = $row[$key] ?? null;
            }

            return $rowWithoutHighlights;
        }, $this->processedData);

        return $dataWithoutHighlights;
    }

   public function headings(): array
    {
        // Usar un array para almacenar todas las claves posibles
        $allKeys = [];

        // Recorremos todas las filas de datos
        foreach ($this->processedData as $row) {
            // Agregamos las claves de cada fila al array de todas las claves
            $allKeys = array_merge($allKeys, array_keys($row));
        }

        // Eliminar claves que contienen '_highlight' y devolver solo las claves únicas
        return array_filter(array_unique($allKeys), function ($key) {
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
                    $row['row_highlight'] = str_replace('#', '', $row['row_highlight']);
                    $sheet->getStyleByColumnAndRow($columnIndex, $rowIndex)
                        ->applyFromArray([
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => $row['row_highlight']]
                            ]
                        ]);
                }
            }

            // Aplicar colores a celdas individuales basadas en '_highlight'
            foreach ($row as $key => $value) {
                if (str_contains($key, '_highlight') && $value) {
                    $baseField = str_replace('_highlight', '', $key);
                    $columnIndex = array_search($baseField, array_keys($row)) + 1; // Posición en la fila
                    $value = str_replace('#', '', $value);
                    $sheet->getStyleByColumnAndRow($columnIndex, $rowIndex)
                        ->applyFromArray([
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => $value]
                            ]
                        ]);
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
