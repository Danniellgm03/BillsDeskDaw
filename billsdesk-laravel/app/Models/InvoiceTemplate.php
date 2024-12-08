<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Models\CorrectionRule;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceTemplate extends Model
{

    use HasFactory;

    protected $collection = 'invoice_templates';
    protected $connection = 'mongodb';

    protected $fillable = [
        'company_id',
        'template_name',
        'column_mappings',
        'formulas',
        'validation_rules',
        'aggregations',
        'created_at',
        'updated_at',
    ];

    public function correctionRules()
    {
        return $this->hasMany(CorrectionRule::class, 'template_id', '_id');
    }

}
