<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class InvoiceTemplate extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'invoice_templates';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'company_id',
        'template_name',
        'column_mappings',
    ];

    // Definir tipos de datos para atributos específicos (opcional)
    protected $casts = [
        'company_id' => 'string',
        'column_mappings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
