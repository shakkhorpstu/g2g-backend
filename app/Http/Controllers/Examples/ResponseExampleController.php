<?php

namespace App\Http\Controllers\Examples;

use App\Http\Controllers\ApiController;
use Modules\Core\Models\User;
use Illuminate\Http\Request;

/**
 * Example controller demonstrating all API response methods
 */
class ResponseExampleController extends ApiController
{
    /**
     * Example of success response with data
     */
    public function successWithData()
    {
        $data = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        return $this->successResponse($data, 'User data retrieved successfully');
    }

    /**
     * Example of success response without data
     */
    public function successWithoutData()
    {
        return $this->successResponse(null, 'Operation completed successfully');
    }

    /**
     * Example of created response (201)
     */
    public function created()
    {
        $newResource = [
            'id' => 123,
            'name' => 'New Resource',
            'created_at' => now()
        ];

        return $this->createdResponse($newResource, 'Resource created successfully');
    }

    /**
     * Example of collection response (array of items)
     */
    public function collection()
    {
        $items = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
            ['id' => 3, 'name' => 'Item 3'],
        ];

        return $this->collectionResponse($items, 'Items retrieved successfully');
    }

    /**
     * Example of paginated response
     */
    public function paginated()
    {
        // Simulate paginated data (normally from Model::paginate())
        $users = User::paginate(10);

        return $this->paginatedResponse($users, 'Users retrieved successfully');
    }

    /**
     * Example of error responses
     */
    public function errorExample()
    {
        return $this->errorResponse('Something went wrong', 400);
    }

    /**
     * Example of not found response
     */
    public function notFound()
    {
        return $this->notFoundResponse('User not found');
    }

    /**
     * Example of unauthorized response
     */
    public function unauthorized()
    {
        return $this->unauthorizedResponse('Please login to continue');
    }

    /**
     * Example of forbidden response
     */
    public function forbidden()
    {
        return $this->forbiddenResponse('You do not have permission to access this resource');
    }

    /**
     * Example of validation error response
     */
    public function validationError()
    {
        $errors = [
            'email' => ['The email field is required.'],
            'password' => ['The password must be at least 8 characters.']
        ];

        return $this->validationErrorResponse($errors);
    }

    /**
     * Example of server error response
     */
    public function serverError()
    {
        return $this->serverErrorResponse('Database connection failed');
    }

    /**
     * Example with meta data
     */
    public function withMeta()
    {
        $data = ['message' => 'Hello World'];
        $meta = [
            'version' => '1.0',
            'api_docs' => 'https://api.example.com/docs'
        ];

        return $this->successResponse($data, 'Data with metadata', 200, $meta);
    }

    /**
     * Example of authentication success response
     */
    public function authSuccess()
    {
        $user = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        $token = 'sample_jwt_token_here';

        return $this->authSuccessResponse($user, $token, 'Bearer', 'Login successful');
    }

    /**
     * Example of logout response
     */
    public function logout()
    {
        return $this->logoutResponse('Successfully logged out');
    }

    /**
     * Example of no content response (for deletions)
     */
    public function deleted()
    {
        return $this->noContentResponse('Resource deleted successfully');
    }
}