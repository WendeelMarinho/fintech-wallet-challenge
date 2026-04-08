<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function success(mixed $data = [], ?string $message = null, int $status = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    protected function error(string $message, int $status = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
