<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cmd1 extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cmd1';

    protected $fillable = [
        'id_client',
        'id_frs',
        'date_cmd',
        'statut',
        'montant_total',
        'adresse_livraison',
        'id_wilaya',
        'id_commune',
        'notes',
        'synced_pme',
    ];

    protected $casts = [
        'date_cmd' => 'datetime',
        'montant_total' => 'float',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client', 'id');
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'id_frs', 'id');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(Cmd2::class, 'id_cmd', 'id');
    }
}
