<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'file_id',
        'status',
        'date_to_pay',
        'name_invoice',
        'template_id',
        'contact_id',
    ];

    // Relación con la empresa
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con el archivo
    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function template()
    {
        return $this->belongsTo(InvoiceTemplate::class, 'template_id', '_id');
    }

    /**
     * Relación con Contact.
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
