<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Paciente extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $username = 'nombre';

    protected $fillable = [
        'nombre',
        'apellido',
        'fecha_de_nacimiento',
        'password',
        'email',
        'direccion',
        'telefono',
        'contacto_opcional',
        'activo'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Los siguientes campos no deben ser enviados en una API
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // Se define como se tratarán los campos en el aplicativo de laravel
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }
}
