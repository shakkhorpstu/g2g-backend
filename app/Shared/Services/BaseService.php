<?php

namespace App\Shared\Services;

use App\Shared\Exceptions\ServiceException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Base Service Class
 * 
 * Provides common functionality for all services including:
 * - Transaction management
 * - Error handling
 * - Consistent response formatting
 * - Multi-guard authentication
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

    /**
     * Get authenticated user from any available guard
     * 
     * This method supports multiple authentication guards and can be used
     * across all services that need to identify the current authenticated user.
     * 
     * The guards are configured in config/auth.php and point to:
     * - 'api' guard for regular Users (Modules\Core\Models\User)
     * - 'psw-api' guard for PSWs (Modules\Core\Models\Psw) 
     * - 'admin-api' guard for Admins (Modules\Core\Models\Admin)
     *
     * @param array|null $guards Optional array of specific guards to check
     * @return mixed The authenticated user model or null
     */
    protected function getAuthenticatedUser(?array $guards = null)
    {
        // Default guards in order of priority
        $defaultGuards = ['api', 'psw-api', 'admin-api'];
        $guardsToCheck = $guards ?? $defaultGuards;
        
        foreach ($guardsToCheck as $guard) {
            $user = Auth::guard($guard)->user();
            if ($user) {
                return $user;
            }
        }
        
        return null;
    }

    /**
     * Get authenticated user with failure handling
     * 
     * Same as getAuthenticatedUser() but throws an exception if no user is found
     *
     * @param array|null $guards Optional array of specific guards to check
     * @param string $errorMessage Custom error message
     * @return mixed The authenticated user model
     * @throws ServiceException When no authenticated user is found
     */
    protected function getAuthenticatedUserOrFail(?array $guards = null, string $errorMessage = 'User not authenticated')
    {
        $user = $this->getAuthenticatedUser($guards);
        
        if (!$user) {
            $this->fail($errorMessage, 401);
        }
        
        return $user;
    }
}