<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commune extends Model
{
    use HasFactory;

    protected $table = 'commune';
    protected $primaryKey = 'ID_COMMUNE';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'COMMUNE',
        'ID_WILAYA',
    ];

    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class, 'ID_WILAYA', 'ID_WILAYA');
    }
}
