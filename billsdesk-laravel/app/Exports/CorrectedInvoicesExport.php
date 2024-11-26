<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CorrectedInvoicesExport implements FromCollection
{
    protected $data; // Los datos corregidos que se exportarán

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Retorna la colección de datos para exportar.
     */
    public function collection()
    {
        return collect($this->data);
    }

    /**
     * Define los encabezados de las columnas.
     */
    public function headings(): array
    {
        if (isset($this->data[0])) {
            return array_keys($this->data[0]); // Obtiene las claves de la primera fila como encabezados
        }

        return [];
    }
}
