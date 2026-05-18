<?php

namespace Modules\School\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    use AuthorizesRequests;

    /**
     * success response method.
     */
    public function sendResponse(mixed $result, string $message, int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * return error response.
     *
     * @param  array<int|string, mixed>  $errorMessages
     */
    public function sendError(string $error, array $errorMessages = [], int $code = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if ($errorMessages !== []) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
