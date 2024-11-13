<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Role;
use Illuminate\Notifications\Notifiable;

class Invitation extends Model
{
    use HasFactory, Notifiable;

    // Especifica los campos que se pueden asignar masivamente
    protected $fillable = [
        'email',
        'token',
        'role_id',
        'company_id',
        'accepted',
    ];

    // Relación con el modelo Role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }


    // Relación con el modelo Company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Añade un método para verificar si la invitación ha sido aceptada
    public function isAccepted()
    {
        return $this->accepted;
    }
}
