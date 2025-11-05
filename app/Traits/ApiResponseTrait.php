<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

trait ApiResponseTrait
{
    /**
     * Success response method.
     */
    protected function successResponse(
        $data = null,
        string $message = 'Operation successful',
        int $statusCode = Response::HTTP_OK,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'status' => 'success',
            'message' => $message,
            'status_code' => $statusCode,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        $response['timestamp'] = now()->toISOString();

        return response()->json($response, $statusCode);
    }

    /**
     * Error response method.
     */
    protected function errorResponse(
        string $message = 'Operation failed',
        int $statusCode = Response::HTTP_BAD_REQUEST,
        $errors = null,
        $data = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'status' => 'error',
            'message' => $message,
            'status_code' => $statusCode,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        $response['timestamp'] = now()->toISOString();

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error response.
     */
    protected function validationErrorResponse(
        $errors,
        string $message = 'Validation failed',
        int $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY
    ): JsonResponse {
        return $this->errorResponse($message, $statusCode, $errors);
    }

    /**
     * Not found error response.
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Unauthorized error response.
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized access'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Forbidden error response.
     */
    protected function forbiddenResponse(string $message = 'Access forbidden'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Created response (for successful resource creation).
     */
    protected function createdResponse(
        $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return $this->successResponse($data, $message, Response::HTTP_CREATED);
    }

    /**
     * No content response (for successful deletion).
     */
    protected function noContentResponse(string $message = 'Resource deleted successfully'): JsonResponse
    {
        return $this->successResponse(null, $message, Response::HTTP_OK);
    }

    /**
     * Paginated response for lists with pagination.
     */
    protected function paginatedResponse(
        $paginatedData,
        string $message = 'Data retrieved successfully'
    ): JsonResponse {
        $meta = [
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
                'from' => $paginatedData->firstItem(),
                'to' => $paginatedData->lastItem(),
                'has_more_pages' => $paginatedData->hasMorePages(),
            ]
        ];

        return $this->successResponse(
            $paginatedData->items(),
            $message,
            Response::HTTP_OK,
            $meta
        );
    }

    /**
     * Collection response for arrays of data.
     */
    protected function collectionResponse(
        $collection,
        string $message = 'Data retrieved successfully',
        array $meta = []
    ): JsonResponse {
        if (is_array($collection) || $collection instanceof \Countable) {
            $meta['count'] = count($collection);
        }

        return $this->successResponse($collection, $message, Response::HTTP_OK, $meta);
    }

    /**
     * Return a server error response
     */
    protected function serverErrorResponse(string $message = 'Internal server error', array $errors = [], int $statusCode = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status' => 'error',
            'message' => $message,
            'status_code' => $statusCode,
            'errors' => $errors,
            'timestamp' => now()->toISOString(),
        ], $statusCode);
    }

    /**
     * Handle exception and return appropriate error response
     */
    protected function handleException(\Throwable $exception, string $defaultMessage = 'An error occurred'): JsonResponse
    {
        // Log the exception for debugging
        Log::error('API Exception: ' . $exception->getMessage(), [
            'exception' => $exception,
            'trace' => $exception->getTraceAsString(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        // Handle specific exception types
        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFoundResponse('Resource not found');
        }

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return $this->validationErrorResponse(
                $exception->errors(),
                'Validation failed'
            );
        }

        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return $this->unauthorizedResponse('Authentication required');
        }

        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return $this->forbiddenResponse('Access denied');
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
            return $this->errorResponse(
                $exception->getMessage() ?: $defaultMessage,
                $exception->getStatusCode()
            );
        }

        // For development environment, show detailed error
        if (config('app.debug')) {
            return $this->serverErrorResponse(
                $exception->getMessage(),
                [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => explode("\n", $exception->getTraceAsString())
                ]
            );
        }

        // For production, return generic error
        return $this->serverErrorResponse($defaultMessage);
    }

    /**
     * Execute service method with try-catch and automatic error handling
     */
    protected function executeService(callable $serviceMethod, string $errorMessage = 'Operation failed'): JsonResponse
    {
        try {
            $result = $serviceMethod();
            
            // If result is already a JsonResponse, return it
            if ($result instanceof JsonResponse) {
                return $result;
            }
            
            // If result is an array with 'success' key (service response format)
            if (is_array($result) && isset($result['success'])) {
                if ($result['success']) {
                    return $this->successResponse(
                        $result['data'] ?? null,
                        $result['message'] ?? 'Operation successful',
                        $result['status_code'] ?? 200,
                        $result['meta'] ?? null
                    );
                } else {
                    return $this->errorResponse(
                        $result['message'] ?? $errorMessage,
                        $result['status_code'] ?? 400,
                        $result['errors'] ?? [],
                        $result['data'] ?? null
                    );
                }
            }
            
            // Default success response for simple data
            return $this->successResponse($result, 'Operation successful');
            
        } catch (\Throwable $exception) {
            return $this->handleException($exception, $errorMessage);
        }
    }
}