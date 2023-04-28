<?php

namespace App\Traits;

trait HttpResponses{
    protected function responseSuccess($data, $message = null, $code = 200)
    {
        return response()->json([
            'status' => 'Request was successful.',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function responseError($data, $message, $code)
    {
        return response()->json([
            'status' => 'Error has occurred...',
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
