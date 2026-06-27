<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'group',
        'key',
        'value',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function putValue(string $key, mixed $value, string $group = 'general'): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['group' => $group, 'value' => $value]
        );
    }

    public static function getFileUrl(string $key): string
    {
        $raw = trim((string) static::getValue($key, ''));

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
}
