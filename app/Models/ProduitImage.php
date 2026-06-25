<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProduitImage extends Model
{
    use HasFactory;

    protected $table = 'produit_images';

    protected $fillable = [
        'id_produit',
        'filename',
        'url_principale',
        'url_thumbnail',
        'ordre',
    ];

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class, 'id_produit', 'id');
    }
}
