<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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

    public function getImagePrincipaleAttribute($value): ?string
    {
        $normalized = self::normalizeMediaUrl($value);

        // #region debug-point C:normalize-image-principale
        self::debugReport('C', '[DEBUG] Normalized produit.image_principale', [
            'product_id' => $this->attributes['id'] ?? null,
            'raw_value' => $value,
            'normalized_value' => $normalized,
        ]);
        // #endregion

        return $normalized;
    }

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

    public static function normalizeMediaUrl(?string $value): ?string
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return null;
        }

        $lower = strtolower($raw);

        if (Str::startsWith($lower, ['http://', 'https://'])) {
            $path = parse_url($raw, PHP_URL_PATH);

            if (is_string($path) && Str::startsWith($path, '/storage/')) {
                return asset(ltrim($path, '/'));
            }

            return $raw;
        }

        if (Str::startsWith($raw, '/storage/')) {
            return asset(ltrim($raw, '/'));
        }

        if (Str::startsWith($raw, 'storage/')) {
            return asset($raw);
        }

        return asset('storage/' . ltrim($raw, '/'));
    }

    private static function debugReport(string $hypothesisId, string $message, array $data): void
    {
        $envPath = base_path('.dbg/product-image-missing.env');
        $serverUrl = 'http://127.0.0.1:7777/event';
        $sessionId = 'product-image-missing';

        if (is_file($envPath)) {
            foreach ((array) file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (str_starts_with($line, 'DEBUG_SERVER_URL=')) {
                    $serverUrl = substr($line, strlen('DEBUG_SERVER_URL='));
                } elseif (str_starts_with($line, 'DEBUG_SESSION_ID=')) {
                    $sessionId = substr($line, strlen('DEBUG_SESSION_ID='));
                }
            }
        }

        $payload = json_encode([
            'sessionId' => $sessionId,
            'runId' => 'pre-fix',
            'hypothesisId' => $hypothesisId,
            'location' => 'app/Models/Produit.php',
            'msg' => $message,
            'data' => $data,
            'ts' => (int) round(microtime(true) * 1000),
        ]);

        if (! is_string($payload)) {
            return;
        }

        @file_get_contents($serverUrl, false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 1,
            ],
        ]));
    }
}
