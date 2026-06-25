<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cmd2 extends Model
{
    use HasFactory;

    protected $table = 'cmd2';

    protected $fillable = [
        'id_cmd',
        'id_produit',
        'quantite',
        'prix_unitaire',
        'sous_total',
    ];

    protected $casts = [
        'prix_unitaire' => 'float',
        'sous_total' => 'float',
    ];

    public function cmd1(): BelongsTo
    {
        return $this->belongsTo(Cmd1::class, 'id_cmd', 'id');
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class, 'id_produit', 'id');
    }
}
