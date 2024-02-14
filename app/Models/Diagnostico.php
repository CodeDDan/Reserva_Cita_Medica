<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Diagnostico extends Model
{
    use HasFactory;

    protected $fillable = [
        'cita_id',
        'detalles',
        'examenes',
        'observaciones'
    ];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function tratamiento(): HasOne
    {
        return $this->hasOne(Tratamiento::class);
    }
}
