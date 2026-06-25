<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wilaya extends Model
{
    use HasFactory;

    protected $table = 'wilaya';
    protected $primaryKey = 'ID_WILAYA';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'WILAYA',
        'WILAYA2',
    ];

    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class, 'ID_WILAYA', 'ID_WILAYA');
    }
}
