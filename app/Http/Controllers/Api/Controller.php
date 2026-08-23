<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Base API Controller untuk seluruh endpoint API v1.
 *
 * Standarisasi response format:
 * {
 *   "success": true|false,
 *   "message": "string",
 *   "data": mixed,
 *   "meta": object|null
 * }
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Format response sukses.
     */
    protected function success(
        mixed $data = null,
        string $message = 'OK',
        int $status = 200,
        ?array $meta = null,
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($data instanceof LengthAwarePaginator || $data instanceof \Illuminate\Contracts\Pagination\Paginator) {
            $payload['data'] = $data->items();
            $payload['meta'] = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ];
            if ($meta) {
                $payload['meta'] = array_merge($payload['meta'], $meta);
            }
        } else {
            $payload['data'] = $data;
            if ($meta !== null) {
                $payload['meta'] = $meta;
            }
        }

        return response()->json($payload, $status);
    }

    /**
     * Format response error.
     */
    protected function error(
        string $message = 'Error',
        int $status = 400,
        array $errors = [],
        mixed $data = null,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
        ], $status);
    }

    /**
     * Format 404 Not Found.
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Format 403 Forbidden.
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    /**
     * Format 422 Validation Error.
     */
    protected function validationError(
        array $errors,
        string $message = 'Validation failed',
    ): JsonResponse {
        return $this->error($message, 422, $errors);
    }
}
