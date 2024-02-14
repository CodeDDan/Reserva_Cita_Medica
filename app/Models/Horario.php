<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Horario extends Model
{
    use HasFactory;
    protected $fillable = [
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'estado'
    ];

    public function empleados(): BelongsToMany
    {
        return $this->belongsToMany(Empleado::class, 'empleado_horario', 'horario_id', 'empleado_id')->withTimestamps();
    }
}
