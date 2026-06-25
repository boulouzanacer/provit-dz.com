<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    protected function success(mixed $data = null, string $message = 'OK', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'errors' => null,
        ], $code);
    }

    protected function error(string $message, mixed $errors = null, int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    protected function notFound(string $message = 'Ressource introuvable'): JsonResponse
    {
        return $this->error($message, null, 404);
    }

    protected function unauthorized(string $message = 'Non autorisé'): JsonResponse
    {
        return $this->error($message, null, 401);
    }

    protected function serverError(string $message = 'Erreur serveur'): JsonResponse
    {
        return $this->error($message, null, 500);
    }
}

