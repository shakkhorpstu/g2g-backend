<?php

namespace Modules\Core\Services;

use Modules\Core\Exceptions\ServiceException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Base Service Class
 * 
 * Provides common functionality for all services including:
 * - Transaction management
 * - Error handling
 * - Consistent response formatting
 */
abstract class BaseService
{
    /**
     * Execute operation with database transaction
     *
     * @param callable $operation The operation to execute
     * @param string $errorMessage Error message prefix
     * @return mixed Operation result
     * @throws ServiceException When operation fails
     */
    protected function executeWithTransaction(callable $operation, string $errorMessage = 'Operation failed')
    {
        try {
            return DB::transaction($operation);
        } catch (ServiceException $e) {
            // Re-throw service exceptions as-is
            throw $e;
        } catch (\Exception $e) {
            Log::error("{$errorMessage}: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new ServiceException(
                $errorMessage,
                500,
                ['error' => [$e->getMessage()]]
            );
        }
    }

    /**
     * Execute operation without transaction
     *
     * @param callable $operation The operation to execute
     * @param string $errorMessage Error message prefix
     * @return mixed Operation result
     * @throws ServiceException When operation fails
     */
    protected function execute(callable $operation, string $errorMessage = 'Operation failed')
    {
        try {
            return $operation();
        } catch (ServiceException $e) {
            // Re-throw service exceptions as-is
            throw $e;
        } catch (\Exception $e) {
            Log::error("{$errorMessage}: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new ServiceException(
                $errorMessage,
                500,
                ['error' => [$e->getMessage()]]
            );
        }
    }

    /**
     * Throw a service exception with formatted error
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $errors Detailed error array
     * @throws ServiceException
     */
    protected function fail(string $message, int $statusCode = 400, array $errors = []): never
    {
        throw new ServiceException($message, $statusCode, $errors);
    }

    /**
     * Create success response array
     *
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code
     * @return array
     */
    protected function success($data = null, string $message = 'Success', int $statusCode = 200): array
    {
        $response = [
            'message' => $message,
            'status_code' => $statusCode
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $response;
    }
}