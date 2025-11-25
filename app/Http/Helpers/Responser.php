<?php

namespace App\Http\Helpers;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class Responser
{
    public static function success($status = Response::HTTP_OK, $message = 'Success', $data = [])
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function fail($status = Response::HTTP_UNPROCESSABLE_ENTITY, $message = 'Error', $data = [], Exception|Throwable|null $throwable = null)
    {
        if ($throwable) {
            $trace = $throwable->getTraceAsString();
            $trace = Str::limit($trace, 10000, ' …[truncated]');

            Log::error("=============== [ERROR CATCH] ===============\n" . ($message ?: 'Service error') . ': ' . print_r([
                'exception' => get_class($throwable),
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'trace' => $trace,
                'url' => request()->fullUrl() ?? null,
                'method' => request()->method() ?? null,
                'ip' => request()->ip() ?? null,
                'user_id' => optional(auth('user')->user())->id,
            ], true));
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function custom($status, $message, $data = [])
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
