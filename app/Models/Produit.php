<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Produit extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'produit';

    protected $fillable = [
        'code_barre',
        'designation',
        'description',
        'pv_1',
        'pv_2',
        'pv_3',
        'stock',
        'image_principale',
        'id_category',
        'abonne_only',
        'enable_tier_pricing',
        'actif',
    ];

    protected $casts = [
        'pv_1' => 'float',
        'pv_2' => 'float',
        'pv_3' => 'float',
        'enable_tier_pricing' => 'boolean',
        'abonne_only' => 'integer',
        'actif' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'id_category', 'id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProduitImage::class, 'id_produit', 'id')->orderBy('ordre');
    }

    public function quantityPrices(): HasMany
    {
        return $this->hasMany(ProduitQuantityPrice::class, 'id_produit', 'id')->orderBy('quantity_min');
    }

    public function distributorStocks(): HasMany
    {
        return $this->hasMany(DistributorStock::class, 'id_produit', 'id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'id_produit', 'id');
    }

    public function prixPourTarif(int $tarif): float
    {
        $tarif = max(1, min(3, $tarif));

        return match ($tarif) {
            2 => (float) $this->pv_2,
            3 => (float) $this->pv_3,
            default => (float) $this->pv_1,
        };
    }

    public function prixPourClient(?Client $client): float
    {
        return $this->prixPourTarif((int) ($client?->tarif ?? 1));
    }

    public function isTierPricingEnabled(): bool
    {
        if ((bool) $this->enable_tier_pricing) {
            return true;
        }

        if ($this->relationLoaded('quantityPrices')) {
            $tiers = $this->getRelation('quantityPrices');

            return $tiers instanceof Collection && $tiers->isNotEmpty();
        }

        return $this->quantityPrices()->exists();
    }

    public function prixUnitairePourQuantite(?Client $client, int $quantite): float
    {
        $qty = max(1, $quantite);

        if ($this->isTierPricingEnabled()) {
            $match = $this->quantityPrices
                ->sortByDesc('quantity_min')
                ->first(function (ProduitQuantityPrice $tier) use ($qty) {
                    if ((int) $tier->quantity_min > $qty) {
                        return false;
                    }

                    return $tier->quantity_max === null || (int) $tier->quantity_max >= $qty;
                });

            if ($match) {
                return (float) $match->price;
            }
        }

        return $this->prixPourClient($client);
    }

    public function stockForDistributor(?int $frsId): int
    {
        if (! $frsId) {
            return 0;
        }

        if ($this->relationLoaded('distributorStocks')) {
            return (int) ($this->distributorStocks->firstWhere('id_frs', $frsId)?->quantite ?? 0);
        }

        return (int) ($this->distributorStocks()->where('id_frs', $frsId)->value('quantite') ?? 0);
    }
}
