<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Fournisseur extends Authenticatable
{
    use HasFactory;
    use SoftDeletes;
    use Notifiable;

    protected $table = 'frs';

    protected $fillable = [
        'nom_frs',
        'email',
        'password',
        'telephone',
        'logo_path',
        'adresse',
        'ville',
        'id_wilaya',
        'id_commune',
        'latitude',
        'longitude',
        'token',
        'actif',
        'is_visible',
    ];

    protected $hidden = [
        'password',
        'token',
    ];

    protected $appends = [
        'logo_url',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (blank($model->token)) {
                $model->token = (string) Str::uuid();
            }
        });
    }

    public function getLogoUrlAttribute(): string
    {
        $raw = trim((string) ($this->logo_path ?? ''));

        if ($raw === '') {
            return '';
        }

        $lower = strtolower($raw);
        if (str_starts_with($lower, 'http://') || str_starts_with($lower, 'https://')) {
            return $raw;
        }

        if (str_starts_with($raw, '/')) {
            return url($raw);
        }

        return Storage::url($raw);
    }

    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class, 'id_wilaya', 'ID_WILAYA');
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'id_commune', 'ID_COMMUNE');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'id_frs', 'id');
    }

    public function stockItems(): HasMany
    {
        return $this->hasMany(DistributorStock::class, 'id_frs', 'id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'id_frs', 'id');
    }

    public function cmd1(): HasMany
    {
        return $this->hasMany(Cmd1::class, 'id_frs', 'id');
    }
}
