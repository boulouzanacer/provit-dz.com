<?php

use App\Http\Controllers\Api\V1\GeoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:public')->group(function () {
    // The legacy SafeSoft mobile/PME API is intentionally disabled in Pro-Vit.
    // Only lightweight endpoints still useful to the current web platform remain exposed.
    Route::get('/status', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => [
                'app' => config('app.name', 'Pro-Vit'),
                'version' => 'v1',
                'scope' => 'web-platform',
                'timestamp' => now()->toISOString(),
                'ip' => $request->ip(),
            ],
            'message' => 'API Pro-Vit disponible',
            'errors' => null,
        ]);
    });

    Route::get('/wilayas', [GeoController::class, 'wilayas']);
    Route::get('/communes/{wilaya}', [GeoController::class, 'communes']);
});
