<?php

namespace App\Traits;

use Illuminate\Http\Response;

trait HttpResponses{
    protected function responseSuccess($data, $message = null, $code = 200)
    {
        return response()->json([
            'status' => 'Successful',
            'message' => $message,
            'data' => $data,
            'code' => $code,
        ], $code);
    }

    protected function responseError($data, $message, $code)
    {
        return response()->json([
            'status' => 'Fail',
            'message' => $message,
            'data' => $data,
            'code' => $code,
        ], $code);
    }

    protected function responseResult($data){
        if(!$data->count()){
            return $this->responseError('', 'Record not found', Response::HTTP_NOT_FOUND);
        }

        return $this->responseSuccess($data);
    }
}
