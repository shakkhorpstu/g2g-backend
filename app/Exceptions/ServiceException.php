<?php

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Service Exception
 * 
 * Custom exception for service layer business logic errors.
 * This allows services to throw meaningful errors that controllers can handle properly.
 */
class ServiceException extends Exception
{
    protected int $statusCode;
    protected array $errors;

    /**
     * Create a new service exception instance.
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param Throwable|null $previous Previous exception
     * @param array $errors Validation errors array
     */
    public function __construct(
        string $message = 'Service error occurred',
        int $statusCode = 400,
        ?Throwable $previous = null,
        array $errors = []
    ) {
        parent::__construct($message, 0, $previous);
        
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    /**
     * Get the HTTP status code
     * 
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the validation errors
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
     * Check if this is a business logic error
     * 
     * @return bool
     */
    public function isBusinessError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Check if this is a server error
     * 
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500;
    }

    /**
     * Convert to array for API response
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