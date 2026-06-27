<?php

namespace App\Services;

use App\Models\Produit;
use App\Models\ProduitImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class ImageProduitService
{
    public function storeUploadedImages(Produit $produit, array $uploadedFiles): void
    {
        $manager = null;

        try {
            if (extension_loaded('gd')) {
                $manager = ImageManager::gd();
            } elseif (extension_loaded('imagick')) {
                $manager = ImageManager::imagick();
            }
        } catch (\Throwable) {
            $manager = null;
        }

        $dir = 'produits/' . $produit->id;
        $nextOrder = (int) ($produit->images()->max('ordre') ?? -1) + 1;

        foreach ($uploadedFiles as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $baseName = Str::uuid()->toString() . '_' . now()->timestamp;
            $filename = $baseName . '.webp';
            $thumbName = $baseName . '_thumb.webp';
            $mainPath = $dir . '/' . $filename;
            $thumbPath = $dir . '/' . $thumbName;

            if ($manager) {
                try {
                    $image = $manager->read($file->getPathname())->cover(900, 900);
                    Storage::disk('public')->put($mainPath, (string) $image->toWebp(80));

                    $thumb = $manager->read($file->getPathname())->cover(260, 260);
                    Storage::disk('public')->put($thumbPath, (string) $thumb->toWebp(80));
                } catch (\Throwable) {
                    $manager = null;
                }
            }

            if (! $manager) {
                $extension = $file->getClientOriginalExtension() ?: 'jpg';
                $filename = $baseName . '.' . strtolower($extension);
                $mainPath = $file->storeAs($dir, $filename, 'public');
                $thumbName = $filename;
                $thumbPath = $mainPath;
            }

            // #region debug-point A:stored-image-paths
            $this->debugReport('A', '[DEBUG] Stored product image candidate paths', [
                'product_id' => $produit->id,
                'disk' => 'public',
                'main_path' => $mainPath,
                'thumb_path' => $thumbPath,
                'main_exists' => Storage::disk('public')->exists($mainPath),
                'thumb_exists' => Storage::disk('public')->exists($thumbPath),
                'raw_image_principale_before' => $produit->getRawOriginal('image_principale'),
            ]);
            // #endregion

            $imageRecord = ProduitImage::create([
                'id_produit' => $produit->id,
                'filename' => $filename,
                'url_principale' => $mainPath,
                'url_thumbnail' => $thumbPath,
                'ordre' => $nextOrder++,
            ]);

            if (blank($produit->image_principale)) {
                $produit->update(['image_principale' => $imageRecord->getRawOriginal('url_principale')]);
            }

            // #region debug-point B:image-record-persisted
            $this->debugReport('B', '[DEBUG] Persisted product image record', [
                'product_id' => $produit->id,
                'image_id' => $imageRecord->id,
                'raw_url_principale' => $imageRecord->getRawOriginal('url_principale'),
                'raw_url_thumbnail' => $imageRecord->getRawOriginal('url_thumbnail'),
                'raw_image_principale_after' => $produit->fresh()?->getRawOriginal('image_principale'),
            ]);
            // #endregion
        }
    }

    public function deleteImages(Produit $produit, array $imageIds): void
    {
        $images = ProduitImage::query()
            ->where('id_produit', $produit->id)
            ->whereIn('id', $imageIds)
            ->get();

        foreach ($images as $image) {
            foreach ([$image->getRawOriginal('url_principale'), $image->getRawOriginal('url_thumbnail')] as $url) {
                $path = $this->storagePathFromUrl((string) $url);
                if ($path !== null) {
                    Storage::disk('public')->delete($path);
                }
            }

            $image->delete();
        }

        $first = $produit->images()->orderBy('ordre')->first();
        $produit->update(['image_principale' => $first?->getRawOriginal('url_principale')]);
    }

    private function storagePathFromUrl(string $url): ?string
    {
        $raw = trim($url);

        if ($raw === '') {
            return null;
        }

        $path = parse_url($raw, PHP_URL_PATH);
        $path = is_string($path) && $path !== '' ? $path : $raw;
        $path = ltrim($path, '/');

        if (Str::startsWith($path, 'storage/')) {
            return substr($path, strlen('storage/'));
        }

        if (Str::startsWith($path, 'public/')) {
            return substr($path, strlen('public/'));
        }

        return $path;
    }

    private function debugReport(string $hypothesisId, string $message, array $data): void
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
            'location' => 'app/Services/ImageProduitService.php',
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
