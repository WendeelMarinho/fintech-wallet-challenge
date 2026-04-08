<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class Handler
{
    public static function validationApi(Request $request, ValidationException $e): ?JsonResponse
    {
        if (! $request->is('api/*')) {
            return null;
        }

        $errors = $e->errors();

        return response()->json([
            'success' => false,
            'message' => collect($errors)->flatten()->first() ?: 'Erro de validação.',
            'errors' => $errors,
        ], $e->status);
    }
}
