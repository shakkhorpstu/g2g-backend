<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Token;
use App\Contracts\Repositories\AuthRepositoryInterface;

class AuthService extends BaseService
{
    public function __construct(public AuthRepositoryInterface $authRepository)
    {}

    /**
     * Register a new user
     * 
     * @param array $data User registration data
     * @return array Registration result with user and token
     * @throws ServiceException When registration fails
     */
    public function register(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            // Business logic validation
            if ($this->authRepository->emailExists($data['email'])) {
                $this->fail(
                    'Email already exists',
                    422,
                    ['email' => ['The email has already been taken.']]
                );
            }

            // Create the user
            $user = $this->authRepository->createUser($data);

            // Create authentication token
            $token = $user->createToken('auth_token')->accessToken;

            // Return structured result
            return [
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ],
                'message' => 'User registered successfully',
                'status_code' => 201
            ];
        }, 'Registration failed');
    }

    /**
     * Login user
     * 
     * @param array $credentials Login credentials
     * @return array Login result with user and token
     * @throws ServiceException When login fails
     */
    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            $this->fail(
                'Invalid credentials',
                401,
                ['credentials' => ['Invalid email or password']]
            );
        }

        $user = Auth::user();
        
        // Update last login time
        // $this->authRepository->updateLastLogin($user->id);
        
        $token = $user->createToken('auth_token')->accessToken;

        return [
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ],
            'message' => 'Login successful'
        ];
    }

    /**
     * Admin login user
     * 
     * @param array $credentials Admin login credentials
     * @return array Login result with admin user and token
     * @throws ServiceException When admin login fails
     */
    public function adminLogin(array $credentials): array
    {
        return $this->executeWithTransaction(function () use ($credentials) {
            // Find user by email
            $user = $this->authRepository->findByEmail($credentials['email']);
            if (!$user) {
                $this->fail(
                    'Invalid credentials',
                    401,
                    ['credentials' => ['Invalid email or password']]
                );
            }

            // Verify credentials
            if (!$this->authRepository->verifyCredentials($credentials['email'], $credentials['password'])) {
                $this->fail(
                    'Invalid credentials',
                    401,
                    ['credentials' => ['Invalid email or password']]
                );
            }

            // Check if user is admin
            if ($user->role !== 'admin') {
                $this->fail(
                    'Access denied',
                    403,
                    ['role' => ['Admin access required']]
                );
            }

            // Update last login
            $this->authRepository->updateLastLogin($user->id);

            // Create token
            $token = $user->createToken('admin_token')->accessToken;

            return [
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ],
                'message' => 'Admin login successful'
            ];
        }, 'Admin login failed');
    }

    /**
     * Logout user
     * 
     * @return array Logout result
     * @throws ServiceException When logout fails
     */
    public function logout(): array
    {
        try {
            $user = Auth::user();
            if ($user) {
                // Revoke current token
                $user->token()->revoke();
            }

            return [
                'message' => 'Logout successful'
            ];
        } catch (\Exception $e) {
            $this->fail('Logout failed', 500, ['error' => [$e->getMessage()]]);
        }
    }

    /**
     * Get authenticated user
     * 
     * @return array User data
     */
    public function me(): array
    {
        $user = Auth::user();
        
        return [
            'data' => [
                'user' => $user
            ],
            'message' => 'User retrieved successfully'
        ];
    }

    /**
     * Refresh user token
     * 
     * @return array New token data
     * @throws ServiceException When refresh fails
     */
    public function refresh(): array
    {
        try {
            $user = Auth::user();
            
            // Revoke current token
            $user->token()->revoke();
            
            // Create new token
            $token = $user->createToken('auth_token')->accessToken;

            return [
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ],
                'message' => 'Token refreshed successfully'
            ];
        } catch (\Exception $e) {
            $this->fail('Token refresh failed', 500, ['error' => [$e->getMessage()]]);
        }
    }

    /**
     * Change user password
     * 
     * @param array $data Password change data
     * @return array Password change result
     * @throws ServiceException When password change fails
     */
    public function changePassword(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $user = Auth::user();
            
            // Verify current password
            if (!Hash::check($data['current_password'], $user->password)) {
                $this->fail(
                    'Current password is incorrect',
                    422,
                    ['current_password' => ['The current password is incorrect']]
                );
            }

            // Update password
            $user->password = Hash::make($data['new_password']);
            $user->save();

            return [
                'message' => 'Password changed successfully'
            ];
        }, 'Password change failed');
    }
}