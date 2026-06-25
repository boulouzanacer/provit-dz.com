<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProduitQuantityPrice extends Model
{
    use HasFactory;

    protected $table = 'produit_quantity_prices';

    protected $fillable = [
        'id_produit',
        'quantity_min',
        'quantity_max',
        'price',
    ];

    protected $casts = [
        'quantity_min' => 'integer',
        'quantity_max' => 'integer',
        'price' => 'float',
    ];

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class, 'id_produit', 'id');
    }
}

