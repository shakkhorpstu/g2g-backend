<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ServiceException;

abstract class BaseService
{
    /**
     * Execute service method with database transaction and error handling
     * 
     * @param callable $callback The business logic to execute
     * @param string $errorMessage Default error message for exceptions
     * @return mixed The result of the callback
     * @throws ServiceException When business logic fails
     */
    protected function executeWithTransaction(callable $callback, string $errorMessage = 'Operation failed')
    {
        DB::beginTransaction();
        
        try {
            $result = $callback();
            
            DB::commit();
            
            return $result;
            
        } catch (ServiceException $exception) {
            DB::rollback();
            
            // Re-throw service exceptions (business logic errors)
            throw $exception;
            
        } catch (\Throwable $exception) {
            DB::rollback();
            
            Log::error('Service Transaction Error: ' . $exception->getMessage(), [
                'service' => static::class,
                'exception' => $exception,
                'trace' => $exception->getTraceAsString(),
            ]);
            
            // Wrap in service exception
            throw new ServiceException($errorMessage, 500, $exception);
        }
    }

    /**
     * Execute service method without transaction but with error handling
     * 
     * @param callable $callback The business logic to execute
     * @param string $errorMessage Default error message for exceptions
     * @return mixed The result of the callback
     * @throws ServiceException When business logic fails
     */
    protected function execute(callable $callback, string $errorMessage = 'Operation failed')
    {
        try {
            return $callback();
            
        } catch (ServiceException $exception) {
            // Re-throw service exceptions (business logic errors)
            throw $exception;
            
        } catch (\Throwable $exception) {
            Log::error('Service Error: ' . $exception->getMessage(), [
                'service' => static::class,
                'exception' => $exception,
                'trace' => $exception->getTraceAsString(),
            ]);
            
            // Wrap in service exception
            throw new ServiceException($errorMessage, 500, $exception);
        }
    }

    /**
     * Throw business logic validation error
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $errors Validation errors array
     * @throws ServiceException
     */
    protected function fail(string $message, int $statusCode = 400, array $errors = []): void
    {
        throw new ServiceException($message, $statusCode, null, $errors);
    }

    /**
     * Validate required data exists
     * 
     * @param mixed $data The data to validate
     * @param string $fieldName Name of the field for error message
     * @throws ServiceException
     */
    protected function validateRequired($data, string $fieldName): void
    {
        if (empty($data)) {
            $this->fail("The {$fieldName} is required", 422, [
                $fieldName => ["The {$fieldName} field is required."]
            ]);
        }
    }

    /**
     * Check if user has permission (override in child services)
     * 
     * @param string $permission Permission to check
     * @param mixed $user User to check permission for
     * @return bool
     */
    protected function checkPermission(string $permission, $user = null): bool
    {
        // Override in child services for specific permission logic
        return true;
    }

    /**
     * Assert user has permission or throw exception
     * 
     * @param string $permission Permission to check
     * @param mixed $user User to check permission for
     * @throws ServiceException
     */
    protected function assertPermission(string $permission, $user = null): void
    {
        if (!$this->checkPermission($permission, $user)) {
            $this->fail('Access denied', 403);
        }
    }

    /**
     * Get authenticated user
     * 
     * @return mixed
     */
    protected function getAuthenticatedUser()
    {
        return auth()->user();
    }

    /**
     * Get guard-specific authenticated user
     * 
     * @param string $guard The guard name
     * @return mixed
     */
    protected function getAuthenticatedUserByGuard(string $guard = 'api')
    {
        return auth($guard)->user();
    }

    /**
     * Assert user is authenticated or throw exception
     * 
     * @param string $guard The guard name
     * @throws ServiceException
     */
    protected function assertAuthenticated(string $guard = 'api'): void
    {
        if (!$this->getAuthenticatedUserByGuard($guard)) {
            $this->fail('Authentication required', 401);
        }
    }
}