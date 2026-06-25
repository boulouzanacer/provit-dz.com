<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Client extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'client';

    protected $fillable = [
        'code_client',
        'nom',
        'prenom',
        'email',
        'email_verified_at',
        'email_verification_code_hash',
        'email_verification_expires_at',
        'password',
        'telephone',
        'adresse',
        'id_wilaya',
        'id_commune',
        'type_client',
        'tarif',
        'achat_client',
        'versement_client',
        'solde_client',
        'id_frs',
        'synced_pme',
        'actif',
    ];

    protected $hidden = [
        'password',
        'email_verification_code_hash',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_expires_at' => 'datetime',
        'achat_client' => 'float',
        'versement_client' => 'float',
        'solde_client' => 'float',
        'synced_pme' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('actif', 1);
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'id_frs', 'id');
    }

    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class, 'id_wilaya', 'ID_WILAYA');
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'id_commune', 'ID_COMMUNE');
    }

    public function commandes(): HasMany
    {
        return $this->hasMany(Cmd1::class, 'id_client', 'id');
    }

    public function getNomCompletAttribute(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }
}
