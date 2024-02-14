<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Se desactivo la utilización de tenants, en caso de necesitarla colocar implementar implements FilamentUser, HasTenants
// Para que pueda acceder en producción se debe agregar implements FilamentUser
class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function grupos(): BelongsToMany
    {
        // Se debe definir el nombre en ambos lados de la relacion pues por defecto intentará concatenar los nombres de las tablas
        return $this->belongsToMany(Grupo::class, 'grupo_usuario', 'user_id', 'grupo_id');
    }

    // Para que pueda acceder en producción
    public function canAccessPanel(Panel $panel): bool
    {
        // return str_ends_with($this->email, '@yourdomain.com') && $this->hasVerifiedEmail();
        return true;
    }

    // public function getTenants(Panel $panel): Collection
    // {
    //     return $this->grupos;
    // }

    // public function canAccessTenant(Model $tenant): bool
    // {
    //     return $this->grupos->contains($tenant);
    // }

    // public function canAccessPanel(Panel $panel): bool
    // {
    //     return true;
    // }

}
