<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InvoiceImport implements ToCollection, WithHeadingRow
{
    protected $template;
    public $processedData = [];

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Mapear columnas del archivo al formato interno
            $mappedRow = $this->mapColumns($row);

            // Aplicar las correcciones
            $correctedRow = $this->applyCorrections($mappedRow);

            // Agregar la fila procesada a los datos finales
            $this->processedData[] = $correctedRow;
        }
    }

    private function mapColumns($row)
    {
        $mappedRow = [];
        foreach ($this->template->column_mappings as $csvColumn => $internalField) {
            $mappedRow[$internalField] = $row[$csvColumn] ?? null;
        }
        return $mappedRow;
    }

    private function applyCorrections($row)
    {
        $correctionRules = $this->template->correctionRules;

        foreach ($correctionRules as $rule) {
            $applyCorrections = true;

            // Validar todas las condiciones de la regla
            foreach ($rule['conditions'] as $condition) {
                if (!$this->validateCondition($row, $condition)) {
                    $applyCorrections = false;
                    break; // Si una condición falla, no aplicar correcciones
                }
            }

            // Si todas las condiciones se cumplen, aplicar las correcciones
            if ($applyCorrections) {
                $row = $this->applyMultipleCorrections($row, $rule['corrections']);
            }
        }

        return $row;
    }

    private function validateCondition($row, $condition)
    {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $value = $condition['value'];

        if (!isset($row[$field])) {
            return false; // Si el campo no existe, la condición falla automáticamente
        }

        switch ($operator) {
            case '>': return $row[$field] > $value;
            case '<': return $row[$field] < $value;
            case '>=': return $row[$field] >= $value;
            case '<=': return $row[$field] <= $value;
            case '==': return $row[$field] == $value;
            case '!=': return $row[$field] != $value;
            default: return false;
        }
    }

    private function applyMultipleCorrections($row, $corrections)
    {
        foreach ($corrections as $correction) {
            $field = $correction['field'];
            $action = $correction['action'];
            $value = $correction['value'];

            // Inicializar el campo si no existe
            if (!array_key_exists($field, $row)) {
                $row[$field] = 0; // Valor predeterminado
            }

            // Aplicar la corrección
            switch ($action) {
                case 'add':
                    $row[$field] += $value;
                    break;
                case 'subtract':
                    $row[$field] -= $value;
                    break;
                case 'update':
                    $row[$field] = $value;
                    break;
            }
        }

        return $row;
    }
}
