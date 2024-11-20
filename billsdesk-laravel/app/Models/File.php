<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use App\Models\User;


class File extends Model
{
    protected $fillable = [
        'company_id',
        'file_type',
        'file_path',
        'file_name',
        'file_extension',
        'file_size',
        'file_size_type',
        'file_mime_type',
        'file_description',
        'file_status',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_fav',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
