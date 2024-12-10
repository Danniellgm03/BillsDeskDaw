<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InvoiceImport implements ToCollection, WithHeadingRow
{
    protected $template;
    protected $rules; // Reglas de corrección
    public $processedData = [];
    public $aggregations = []; // Resultados de las agregaciones
    protected $seenRows = []; // Para llevar un registro de las filas procesadas

    public function __construct($template, $rules)
    {
        $this->template = $template;
        $this->rules = $rules;
    }

    public function collection(Collection $rows)
    {
        // Inicializar agregaciones
        $this->initializeAggregations();

        $mappedRowResult = [];
        foreach ($rows as $row) {
            // Mapear columnas
            try{
                $mappedRow = $this->mapColumns($row);
            } catch(\Exception $e){
                throw new \Exception('Error mapping columns: ' . $e->getMessage());
            }

            // Primero, marcar las filas duplicadas (separado de la validación)
            $mappedRow = $this->markDuplicateRows($mappedRow);

            // Aplicar correcciones (correction_rules)
            $correctedRow = $this->applyCorrections($mappedRow);

            // Crear nuevas columnas basadas en fórmulas
            $correctedRow = $this->applyFormulas($correctedRow);

            // Validar y destacar filas según validation_rules
            $correctedRow = $this->applyValidationRules($correctedRow);

            // Actualizar agregaciones
            $this->updateAggregations($correctedRow);

            // Guardar la fila procesada
            $this->processedData[] = $correctedRow;
        }

        if(!empty($this->processedData) && !empty($this->template['aggregations']) && count($this->aggregations) > 0){
            // Agregar las agregaciones al resultado final
            $this->addAggregationsToProcessedData($this->processedData);
        }
    }
    // Detectar filas duplicadas según un campo especificado en validation_rules
    private function markDuplicateRows($row)
    {
        // Verificar si existe una regla de duplicados en validation_rules
        foreach ($this->template['validation_rules'] as $rule) {
            if (isset($rule['duplicate_field'])) {
                $duplicateField = $rule['duplicate_field'];  // Columna a verificar para duplicados
                $rowHighlight = $rule['row_highlight'] ?? 'yellow'; // Color por defecto si no se especifica

                // Verificar si la fila ya ha sido procesada basándonos en la columna duplicada
                if (isset($row[$duplicateField])) {
                    if (in_array($row[$duplicateField], $this->seenRows)) {
                        // Si ya existe, marcar la fila como duplicada
                        $row['row_highlight'] = $rowHighlight; // Aplicar el color especificado
                        $row['duplicate'] = true; // Marcar la fila como duplicada
                    } else {
                        // Si no existe, agregarla a los registros procesados
                        $this->seenRows[] = $row[$duplicateField];
                    }
                }
            }
        }

        return $row;
    }

    private function mapColumns($row)
    {
        $mappedRow = [];

        if(empty($this->template['column_mappings'])){
            throw new \Exception('Column mappings not defined');
        }

        foreach ($this->template['column_mappings'] as $csvColumn => $internalField) {
            $key = !empty($internalField) ? $internalField : $csvColumn;
            $mappedRow[$key] = $row[$csvColumn] ?? null;
        }
        return $mappedRow;
    }

    private function applyFormulas($row)
    {
        if (!isset($this->template['formulas'])) {
            return $row;
        }

        foreach ($this->template['formulas'] as $formula) {
            $newColumn = $formula['new_column'];
            $expression = $formula['formula'];

            // Reemplazar campos en la fórmula con sus valores en la fila
            foreach ($row as $field => $value) {
                if (strpos($expression, $field) === false) {
                    continue;
                }

                $expression = preg_replace('/\b' . preg_quote($field, '/') . '\b/', $value, $expression);
            }

            // Comprobar que la expresión solo contiene caracteres válidos para cálculos matemáticos
            if (!preg_match('#^[0-9+\-*/().\s]+$#', $expression)) {
                $row[$newColumn] = null; // Si no es válida, asignar null
                continue;
            }

            // Evaluar la fórmula
            try {
                $row[$newColumn] = eval("return $expression;");
            } catch (\Throwable $e) {
                $row[$newColumn] = null; // Si hay un error, asignar null
            }
        }

        return $row;
    }



    private function applyValidationRules($row)
    {
        if (!isset($this->template['validation_rules'])) {
            return $row;
        }

        foreach ($this->template['validation_rules'] as $rule) {
            $applyRowHighlight = false; // Variable para determinar si se aplica el row_highlight

            if (isset($rule['conditions'])) {
                foreach ($rule['conditions'] as $condition) {
                    if ($this->validateCondition($row, $condition)) {
                        // Si hay un campo 'row_highlight' en la condición, se aplica
                        if (isset($condition['row_highlight'])) {
                            $row['row_highlight'] = $condition['row_highlight']; // Asignar color a la fila
                            $applyRowHighlight = true;
                        }
                        // Si hay un campo 'highlight', se aplica como antes
                        if (isset($condition['highlight'])) {
                            $row["{$condition['field']}_highlight"] = $condition['highlight'];
                        }
                    }
                }
            } else {
                if ($this->validateCondition($row, $rule)) {
                    // Si hay un campo 'row_highlight' en la regla, se aplica
                    if (isset($rule['row_highlight'])) {
                        $row['row_highlight'] = $rule['row_highlight']; // Asignar color a la fila
                        $applyRowHighlight = true;
                    }
                    // Si hay un campo 'highlight', se aplica como antes
                    if (isset($rule['highlight'])) {
                        $row["{$rule['field']}_highlight"] = $rule['highlight'];
                    }
                }
            }
        }

        return $row;
    }

    private function validateCondition($row, $condition)
    {
        if(!isset($condition['field']) || !isset($condition['operator']) || !isset($condition['value'])){
            return false;
        }

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

    private function applyCorrections($row)
    {
        foreach ($this->rules as $rule) {
            $applyCorrections = true;

            foreach ($rule['conditions'] as $condition) {
                if (!$this->validateCondition($row, $condition)) {
                    $applyCorrections = false;
                    break;
                }
            }

            if ($applyCorrections) {
                $row = $this->applyMultipleCorrections($row, $rule['corrections']);
            }
        }

        return $row;
    }

    private function applyMultipleCorrections($row, $corrections)
    {
        foreach ($corrections as $correction) {
            $field = $correction['field'];
            $newColumn = isset($correction['new_column']) ? $correction['new_column'] : null;
            $action = $correction['action'];
            $value = $correction['value'];

            // Si la columna no existe, inicializa con 0
            if (!array_key_exists($field, $row)) {
                $row[$field] = 0;
            }

            // Calcula el nuevo valor para la nueva columna, si se especifica
            if ($newColumn) {
                // Calculamos el valor que debe tener la nueva columna
                switch ($action) {
                    case 'add':
                        $row[$newColumn] = $row[$field] + $this->resolveValue($value, $row[$field]);
                        break;
                    case 'subtract':
                        $row[$newColumn] = $row[$field] - $this->resolveValue($value, $row[$field]);
                        break;
                    case 'update':
                        $row[$newColumn] = $this->resolveValue($value, $row[$field]);
                        break;
                }
                continue;
            }

            // Aplica la corrección al campo original
            switch ($action) {
                case 'add':
                    $row[$field] += $this->resolveValue($value, $row[$field]);
                    break;
                case 'subtract':
                    $row[$field] -= $this->resolveValue($value, $row[$field]);
                    break;
                case 'update':
                    $row[$field] = $this->resolveValue($value, $row[$field]);
                    break;
            }
        }

        return $row;
    }

    private function resolveValue($value, $currentValue)
    {
        // Verifica si 'value' es un array de rangos
        if (is_array($value) && !empty($value[0]['min']) && !empty($value[0]['max'])) {
            foreach ($value as $range) {
                // Verifica si el rango tiene 'step' y 'step_increment'
                if (isset($range['step'])) {
                    // Validación para valores fuera del rango de 'min' y 'max'
                    if ($currentValue >= $range['min']) {
                        $baseValue = $range['value']; // Empieza con el valor base

                        // Si 'step_increment' está presente, calculamos el incremento
                        if (isset($range['step_increment'])) {
                            // Calculamos cuántos incrementos caben entre el valor actual y el mínimo
                            $stepCount = floor(($currentValue - $range['min']) / $range['step']);
                            $increment = $stepCount * $range['step_increment'];

                            return $baseValue + $increment;
                        } else {
                            return $baseValue;
                        }
                    }
                }
                // Rango sin 'step', solo verificamos si el valor está dentro del rango 'min' a 'max'
                elseif ($currentValue >= $range['min'] && $currentValue < $range['max']) {
                    return $range['value'];
                }
            }
        }

        // Si no es un array ni se aplica 'step', simplemente devuelve el valor

        if(is_array($value)){
            $value = $value[0]['value'];
        }

        return $value;
    }

    private function initializeAggregations()
    {
        if (!isset($this->template['aggregations'])) {
            return;
        }

        $seenFields = []; // Para rastrear qué campos ya están asignados a un tipo de agregación

        foreach ($this->template['aggregations'] as $aggregation) {
            $type = $aggregation['type'];
            $fields = $aggregation['fields'] ?? [];

            foreach ($fields as $field) {
                if (isset($seenFields[$field])) {
                    continue; // Si el campo ya está asignado a un tipo de agregación, saltar
                }

                $seenFields[$field] = $type; // Registrar el campo como usado por este tipo de agregación

                // Inicializar las agregaciones para el campo
                $this->aggregations[$type][$field] = [];
            }
        }
    }

    private function updateAggregations($row)
    {
        if (!isset($this->template['aggregations'])) {
            return;
        }

        foreach ($this->template['aggregations'] as $aggregation) {
            foreach ($aggregation['fields'] as $field) {
                if (isset($row[$field])) {
                    $this->aggregations[$aggregation['type']][$field][] = $row[$field];
                }
            }
        }
    }

    private function addAggregationsToProcessedData($mappedRowResult)
    {
        // Crear un array con todas las columnas presentes en el resultado procesado, inicializadas en null
        $aggregatedRow = array_fill_keys(array_keys($mappedRowResult[0]), null);

        // Rellenar los campos de agregación con sus valores calculados
        foreach ($this->aggregations as $type => $fields) {
            foreach ($fields as $field => $values) {
                // Filtrar valores no numéricos
                $numericValues = array_filter($values, function ($value) {
                    return is_numeric($value);
                });

                // Continuar si no hay valores numéricos
                if (empty($numericValues)) {
                    continue;
                }

                if ($type === 'sum') {
                    $aggregatedRow[$field] = array_sum($numericValues);
                } elseif ($type === 'average') {
                    $aggregatedRow[$field] = count($numericValues) > 0 ? array_sum($numericValues) / count($numericValues) : null;
                }
            }
        }

        // Agregar la fila de agregaciones al conjunto de datos procesados
        $this->processedData[] = $aggregatedRow;
    }


}
