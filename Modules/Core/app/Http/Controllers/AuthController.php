<?php

namespace Modules\Core\Http\Controllers;

use Modules\Core\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Core\Exceptions\ServiceException;
use App\Http\Controllers\Controller;

/**
 * Authentication Controller
 * 
 * Handles authentication-related HTTP requests
 */
class AuthController extends Controller
{
    /**
     * AuthService instance
     *
     * @var AuthService
     */
    protected AuthService $authService;

    /**
     * AuthController constructor
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone_number' => 'sometimes|string|max:20',
            'gender' => 'sometimes|in:1,2,3',
        ]);

        try {
            $result = $this->authService->register($request->all());
            return response()->json($result, $result['status_code']);
        } catch (ServiceException $e) {
            return $e->toResponse();
        }
    }

    /**
     * Login user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $result = $this->authService->login($request->only(['email', 'password']));
            return response()->json($result, $result['status_code']);
        } catch (ServiceException $e) {
            return $e->toResponse();
        }
    }

    /**
     * Admin login
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function adminLogin(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $result = $this->authService->adminLogin($request->only(['email', 'password']));
            return response()->json($result, $result['status_code']);
        } catch (ServiceException $e) {
            return $e->toResponse();
        }
    }

    /**
     * Logout user
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        try {
            $result = $this->authService->logout();
            return response()->json($result, $result['status_code']);
        } catch (ServiceException $e) {
            return $e->toResponse();
        }
    }

    /**
     * Refresh user token
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        try {
            $result = $this->authService->refresh();
            return response()->json($result, $result['status_code']);
        } catch (ServiceException $e) {
            return $e->toResponse();
        }
    }

    /**
     * Change user password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $result = $this->authService->changePassword($request->all());
            return response()->json($result, $result['status_code']);
        } catch (ServiceException $e) {
            return $e->toResponse();
        }
    }
}