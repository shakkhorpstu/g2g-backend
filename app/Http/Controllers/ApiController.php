<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use Modules\Core\Exceptions\ServiceException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;

class ApiController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, ApiResponseTrait;

    /**
     * API Controller constructor.
     */
    public function __construct()
    {
        // Set JSON response for all API controllers
        request()->headers->set('Accept', 'application/json');
    }

    /**
     * Execute service method and return appropriate JSON response
     * 
     * This method handles the service layer integration properly:
     * - Catches ServiceExceptions and converts to API responses
     * - Handles other exceptions with generic error response
     * - Returns success responses for successful operations
     * 
     * @param callable $serviceMethod The service method to execute
     * @param string $successMessage Success message for the response
     * @param int $successStatusCode Success HTTP status code
     * @return JsonResponse
     */
    protected function executeService(
        callable $serviceMethod,
        string $successMessage = 'Operation successful',
        int $successStatusCode = 200
    ): JsonResponse {
        try {
            $result = $serviceMethod();
            
            // Handle different types of successful results
            if (is_array($result) && isset($result['data'], $result['message'])) {
                // Service returned structured response
                return $this->successResponse(
                    $result['data'],
                    $result['message'],
                    $result['status_code'] ?? $successStatusCode,
                    $result['meta'] ?? []
                );
            }
            
            // Service returned simple data
            return $this->successResponse($result, $successMessage, $successStatusCode);
            
        } catch (ServiceException $exception) {
            // Handle business logic errors from service layer
            if ($exception->isValidationError()) {
                return $this->validationErrorResponse(
                    $exception->getErrors(),
                    $exception->getMessage()
                );
            }
            
            return $this->errorResponse(
                $exception->getMessage(),
                $exception->getStatusCode(),
                $exception->getErrors()
            );
            
        } catch (\Throwable $exception) {
            // Handle unexpected errors
            return $this->handleException($exception, 'Operation failed');
        }
    }

    /**
     * Execute service method for resource creation
     * 
     * @param callable $serviceMethod
     * @param string $successMessage
     * @return JsonResponse
     */
    protected function executeServiceForCreation(
        callable $serviceMethod,
        string $successMessage = 'Resource created successfully'
    ): JsonResponse {
        return $this->executeService($serviceMethod, $successMessage, 201);
    }

    /**
     * Execute service method for resource update
     * 
     * @param callable $serviceMethod
     * @param string $successMessage
     * @return JsonResponse
     */
    protected function executeServiceForUpdate(
        callable $serviceMethod,
        string $successMessage = 'Resource updated successfully'
    ): JsonResponse {
        return $this->executeService($serviceMethod, $successMessage, 200);
    }

    /**
     * Execute service method for resource deletion
     * 
     * @param callable $serviceMethod
     * @param string $successMessage
     * @return JsonResponse
     */
    protected function executeServiceForDeletion(
        callable $serviceMethod,
        string $successMessage = 'Resource deleted successfully'
    ): JsonResponse {
        return $this->executeService($serviceMethod, $successMessage, 200);
    }

    /**
     * Execute service method that returns paginated data
     * 
     * @param callable $serviceMethod
     * @param string $successMessage
     * @return JsonResponse
     */
    protected function executeServiceForPagination(
        callable $serviceMethod,
        string $successMessage = 'Data retrieved successfully'
    ): JsonResponse {
        try {
            $result = $serviceMethod();
            
            // If result has pagination methods, use paginatedResponse
            if (is_object($result) && method_exists($result, 'total')) {
                return $this->paginatedResponse($result, $successMessage);
            }
            
            // If result is collection, use collectionResponse
            if (is_array($result) || $result instanceof \Countable) {
                return $this->collectionResponse($result, $successMessage);
            }
            
            return $this->successResponse($result, $successMessage);
            
        } catch (ServiceException $exception) {
            return $this->errorResponse(
                $exception->getMessage(),
                $exception->getStatusCode(),
                $exception->getErrors()
            );
            
        } catch (\Throwable $exception) {
            return $this->handleException($exception, 'Failed to retrieve data');
        }
    }
}