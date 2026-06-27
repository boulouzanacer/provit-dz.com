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
}
