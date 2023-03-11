<?php

namespace App\Traits;

trait ApiResponser
{
    // References: https://google.github.io/styleguide/jsoncstyleguide.xml
    protected function successResponse(mixed $data, string $message = null, int $code = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse(string $message = null, int $code)
    {
        return response()->json([
            'error' => [
                'message' => $message
            ]
        ], $code);
    }
}
