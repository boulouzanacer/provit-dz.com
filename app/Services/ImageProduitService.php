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

            if ($manager) {
                try {
                    $image = $manager->read($file->getPathname())->cover(900, 900);
                    Storage::disk('public')->put($dir . '/' . $filename, (string) $image->toWebp(80));

                    $thumb = $manager->read($file->getPathname())->cover(260, 260);
                    Storage::disk('public')->put($dir . '/' . $thumbName, (string) $thumb->toWebp(80));
                } catch (\Throwable) {
                    $manager = null;
                }
            }

            if (! $manager) {
                $extension = $file->getClientOriginalExtension() ?: 'jpg';
                $filename = $baseName . '.' . strtolower($extension);
                $path = $file->storeAs($dir, $filename, 'public');
                $thumbName = $filename;
                $url = Storage::url($path);
                $thumbUrl = $url;
            } else {
                $url = Storage::url($dir . '/' . $filename);
                $thumbUrl = Storage::url($dir . '/' . $thumbName);
            }

            $imageRecord = ProduitImage::create([
                'id_produit' => $produit->id,
                'filename' => $filename,
                'url_principale' => $url,
                'url_thumbnail' => $thumbUrl,
                'ordre' => $nextOrder++,
            ]);

            if (blank($produit->image_principale)) {
                $produit->update(['image_principale' => $imageRecord->url_principale]);
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
            foreach ([$image->url_principale, $image->url_thumbnail] as $url) {
                $path = $this->storagePathFromUrl((string) $url);
                if ($path !== null) {
                    Storage::disk('public')->delete($path);
                }
            }

            $image->delete();
        }

        $first = $produit->images()->orderBy('ordre')->first();
        $produit->update(['image_principale' => $first?->url_principale]);
    }

    private function storagePathFromUrl(string $url): ?string
    {
        $needle = '/storage/';
        $position = strpos($url, $needle);

        if ($position === false) {
            return null;
        }

        return substr($url, $position + strlen($needle));
    }
}
