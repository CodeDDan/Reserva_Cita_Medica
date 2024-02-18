<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empleado extends Model
{
    use HasFactory;

    protected $fillable = [
        'grupo_id',
        'nombre',
        'apellido',
        'edad',
        'direccion',
        'correo',
        'password',
        'telefono',
        'fecha_de_contratacion',
        'contacto_opcional',
        'activo'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    public function horarios(): BelongsToMany
    {
        return $this->belongsToMany(Horario::class, 'empleado_horario', 'empleado_id', 'horario_id')
            ->withPivot('activo')
            ->withTimestamps();
    }

    // Para el repeater
    public function empleadoHorario(): HasMany
    {
        return $this->hasMany(EmpleadoHorario::class);
    }
}
