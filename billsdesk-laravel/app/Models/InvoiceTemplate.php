<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Models\CorrectionRule;

class InvoiceTemplate extends Model
{
    protected $collection = 'invoice_templates';
    protected $connection = 'mongodb';

    protected $fillable = [
        'company_id',
        'template_name',
        'column_mappings',
        'created_at',
        'updated_at',
    ];

    public function correctionRules()
    {
        return $this->hasMany(CorrectionRule::class, 'template_id', '_id');
    }

}
