<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use App\Models\Wilaya;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Cache;

class GeoController extends Controller
{
    use ApiResponseTrait;

    public function wilayas()
    {
        $rows = Cache::remember('geo:wilayas', now()->addMinutes(15), function () {
            return Wilaya::query()
                ->orderBy('ID_WILAYA')
                ->get(['ID_WILAYA', 'WILAYA', 'WILAYA2']);
        });

        return $this->success($rows, 'Wilayas');
    }

    public function communes(int $wilaya)
    {
        $rows = Cache::remember("geo:communes:$wilaya", now()->addMinutes(15), function () use ($wilaya) {
            return Commune::query()
                ->where('ID_WILAYA', $wilaya)
                ->orderBy('COMMUNE')
                ->get(['ID_COMMUNE', 'COMMUNE', 'ID_WILAYA']);
        });

        return $this->success($rows, 'Communes');
    }
}
