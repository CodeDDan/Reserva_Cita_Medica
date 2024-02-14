<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tratamiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'diagnostico_id',
        'tipo_tratamiento',
        'medicamento',
        'dosis',
        'procedimiento',
        'notas'
    ];

    public function diagnostico(): BelongsTo
    {
        return $this->belongsTo(Diagnostico::class);
    }
}
