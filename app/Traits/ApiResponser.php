<?php

namespace App\Traits;

trait ApiResponser
{
    protected function successResponse($data, $message, $code = 200)
    {
        return response()->json([
            'meta' => [
                'code' => $code,
                'message' => $message,
            ],
            'response' => $data,
        ]);
    }

    protected function errorResponse($message = null, $code = 404)
    {
        return response()->json([
            'meta' => [
                'code' => $code,
                'message' => $message,
            ]
        ]);
    }
}
