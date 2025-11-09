<?php

namespace Modules\Core\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Service Exception Class
 * 
 * Custom exception for handling service-level errors
 * with structured error responses
 */
class ServiceException extends Exception
{
    /**
     * HTTP status code
     *
     * @var int
     */
    protected int $statusCode;

    /**
     * Detailed error data
     *
     * @var array
     */
    protected array $errors;

    /**
     * Create a new ServiceException instance
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $errors Detailed error array
     * @param Exception|null $previous Previous exception
     */
    public function __construct(
        string $message = 'Service error occurred',
        int $statusCode = 500,
        array $errors = [],
        ?Exception $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    /**
     * Get HTTP status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get error details
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if this is a validation error
     *
     * @return bool
     */
    public function isValidationError(): bool
    {
        return $this->statusCode === 422 && !empty($this->errors);
    }

    /**
     * Convert exception to JSON response
     *
     * @return JsonResponse
     */
    public function toResponse(): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $this->getMessage(),
            'status_code' => $this->getStatusCode(),
        ];

        if (!empty($this->errors)) {
            $response['errors'] = $this->errors;
        }

        return response()->json($response, $this->statusCode);
    }

    /**
     * Get exception as array
     *
     * @return array
     */
    public function toArray(): array
    {
        $response = [
            'message' => $this->getMessage(),
            'status_code' => $this->getStatusCode(),
        ];

        if (!empty($this->errors)) {
            $response['errors'] = $this->errors;
        }

        return $response;
    }
}