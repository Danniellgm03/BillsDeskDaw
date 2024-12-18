<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Models\InvoiceTemplate;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CorrectionRule extends Model
{

    use HasFactory;

    protected $collection = 'correction_rules';
    protected $connection = 'mongodb';

    protected $fillable = [
        'company_id',
        'rule_name',
        'conditions',
        'corrections',
        'template_id',
        'created_at',
        'updated_at',
    ];

    public function template()
    {
        return $this->belongsTo(InvoiceTemplate::class, 'template_id', '_id');
    }
}
