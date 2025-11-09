<?php

namespace Modules\Core\Services;

use Modules\Core\Contracts\Repositories\AuthRepositoryInterface;
use Modules\Core\Exceptions\ServiceException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Authentication Service
 * 
 * Handles all authentication-related business logic including:
 * - User registration and login
 * - Token management
 * - Password operations
 * - Admin authentication
 */
class AuthService extends BaseService
{
    /**
     * Auth repository instance
     *
     * @var AuthRepositoryInterface
     */
    protected AuthRepositoryInterface $authRepository;

    /**
     * AuthService constructor
     *
     * @param AuthRepositoryInterface $authRepository
     */
    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * Register a new user
     *
     * @param array $data User registration data
     * @return array
     * @throws ServiceException
     */
    public function register(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            // Check if email already exists
            if ($this->authRepository->findByEmail($data['email'])) {
                $this->fail('Email already exists', 422, ['email' => ['Email is already taken']]);
            }

            // Create user
            $user = $this->authRepository->create($data);

            // Generate token using Passport
            $token = $user->createToken('auth_token')->accessToken;

            return $this->success([
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'Registration successful', 201);
        });
    }

    /**
     * Login user and return access token
     *
     * @param array $credentials Login credentials
     * @return array
     * @throws ServiceException
     */
    public function login(array $credentials): array
    {
        return $this->executeWithTransaction(function () use ($credentials) {
            // Find user by email
            $user = $this->authRepository->findByEmail($credentials['email']);
            
            if (!$user) {
                $this->fail('Invalid credentials', 401);
            }

            // Verify password
            if (!Hash::check($credentials['password'], $user->password)) {
                $this->fail('Invalid credentials', 401);
            }

            // Update last login
            $this->authRepository->updateLastLogin($user);

            // Generate token
            $token = $user->createToken('auth_token')->accessToken;

            return $this->success([
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'Login successful');
        });
    }

    /**
     * Admin login with role verification
     *
     * @param array $credentials Login credentials
     * @return array
     * @throws ServiceException
     */
    public function adminLogin(array $credentials): array
    {
        return $this->executeWithTransaction(function () use ($credentials) {
            // Find user by email
            $user = $this->authRepository->findByEmail($credentials['email']);
            
            if (!$user) {
                $this->fail('Invalid credentials', 401);
            }

            // Verify password
            if (!Hash::check($credentials['password'], $user->password)) {
                $this->fail('Invalid credentials', 401);
            }

            // Check if user has admin role
            if ($user->role !== 'admin') {
                $this->fail('Access denied. Admin privileges required.', 403);
            }

            // Update last login
            $this->authRepository->updateLastLogin($user);

            // Generate token
            $token = $user->createToken('admin_token')->accessToken;

            return $this->success([
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'Admin login successful');
        });
    }

    /**
     * Logout user and revoke token
     *
     * @return array
     * @throws ServiceException
     */
    public function logout(): array
    {
        return $this->executeWithTransaction(function () {
            $user = Auth::user();
            
            if (!$user) {
                $this->fail('User not authenticated', 401);
            }

            // Logout from all devices by revoking all tokens
            $user->tokens->each(function ($token) {
                $token->revoke();
            });

            return $this->success(null, 'Logout successful');
        });
    }

    /**
     * Refresh user token
     *
     * @return array
     * @throws ServiceException
     */
    public function refresh(): array
    {
        return $this->executeWithTransaction(function () {
            $user = Auth::user();
            
            if (!$user) {
                $this->fail('User not authenticated', 401);
            }

            // Revoke all existing tokens
            $user->tokens->each(function ($token) {
                $token->revoke();
            });

            // Generate new token
            $token = $user->createToken('auth_token')->accessToken;

            return $this->success([
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'Token refreshed successfully');
        });
    }

    /**
     * Change user password
     *
     * @param array $data Password change data
     * @return array
     * @throws ServiceException
     */
    public function changePassword(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $user = Auth::user();
            
            if (!$user) {
                $this->fail('User not authenticated', 401);
            }

            // Verify current password
            if (!Hash::check($data['current_password'], $user->password)) {
                $this->fail('Current password is incorrect', 422, [
                    'current_password' => ['Current password is incorrect']
                ]);
            }

            // Update password using repository
            $this->authRepository->updatePassword($user, $data['new_password']);

            return $this->success(null, 'Password changed successfully');
        });
    }
}