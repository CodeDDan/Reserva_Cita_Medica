<?php

namespace App\Models;

use Filament\Http\Middleware\IdentifyTenant;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;

class Grupo extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'slug'];

    public function miembros(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'grupo_usuario', 'grupo_id', 'user_id');
    }

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class);
    }

    public function nombreTenant(): string
    {
        // Colocar el nombre de la columna cuyo valor se usará para nombrar al grupo
        // Recuerda que debe coincidir con el modelo de eloquent de grupo
        return 'nombre';
    }
}
