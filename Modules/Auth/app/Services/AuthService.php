<?php

namespace Modules\Auth\Services;

use App\Services\BaseService;
use App\Exceptions\ServiceException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Token;
use Modules\Auth\Contracts\Repositories\UserRepositoryInterface;

class AuthService extends BaseService
{
    public function __construct(public UserRepositoryInterface $userRepository)
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
            if ($this->userRepository->emailExists($data['email'])) {
                $this->fail(
                    'Email already exists',
                    422,
                    ['email' => ['The email has already been taken.']]
                );
            }

            // Create the user
            $user = $this->userRepository->createUser($data);

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
            $user = $this->userRepository->findByEmail($credentials['email']);
            if (!$user) {
                $this->fail(
                    'Invalid credentials',
                    401,
                    ['credentials' => ['Invalid email or password']]
                );
            }

            // Check if user is admin
            if (!$user->is_admin) {
                $this->fail(
                    'Access denied',
                    403,
                    ['access' => ['You do not have admin privileges']]
                );
            }

            // Verify password
            if (!Hash::check($credentials['password'], $user->password)) {
                $this->fail(
                    'Invalid credentials',
                    401,
                    ['credentials' => ['Invalid email or password']]
                );
            }

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
     * Get user profile
     * 
     * @param mixed $user Authenticated user
     * @return array User profile data
     */
    public function getProfile($user): array
    {
        return [
            'data' => [
                'user' => $user
            ],
            'message' => 'Profile retrieved successfully'
        ];
    }

    /**
     * Update user profile
     * 
     * @param mixed $user Authenticated user
     * @param array $data Profile update data
     * @return array Updated profile data
     * @throws ServiceException When profile update fails
     */
    public function updateProfile($user, array $data): array
    {
        return $this->executeWithTransaction(function () use ($user, $data) {
            // Check if email is being changed and already exists
            if (isset($data['email']) && $data['email'] !== $user->email) {
                if ($this->userRepository->emailExists($data['email'])) {
                    $this->fail(
                        'Email already exists',
                        422,
                        ['email' => ['The email has already been taken.']]
                    );
                }
            }

            // Update user
            $updatedUser = $this->userRepository->updateUser($user, $data);

            return [
                'data' => [
                    'user' => $updatedUser
                ],
                'message' => 'Profile updated successfully'
            ];
        }, 'Profile update failed');
    }

    /**
     * Change user password
     * 
     * @param mixed $user Authenticated user
     * @param array $data Password change data
     * @return array Success response
     * @throws ServiceException When password change fails
     */
    public function changePassword($user, array $data): array
    {
        return $this->executeWithTransaction(function () use ($user, $data) {
            // Verify current password
            if (!Hash::check($data['current_password'], $user->password)) {
                $this->fail(
                    'Invalid current password',
                    422,
                    ['current_password' => ['The current password is incorrect']]
                );
            }

            // Update password
            $this->userRepository->updatePassword($user, $data['new_password']);

            return [
                'data' => null,
                'message' => 'Password changed successfully'
            ];
        }, 'Password change failed');
    }

    /**
     * Logout user
     * 
     * @param mixed $user Authenticated user
     * @return array Success response
     * @throws ServiceException When logout fails
     */
    public function logout($user): array
    {
        return $this->executeWithTransaction(function () use ($user) {
            // Revoke the token
            $user->token()->revoke();

            return [
                'data' => null,
                'message' => 'Successfully logged out'
            ];
        }, 'Logout failed');
    }

        /**
     * Logout user from all devices
     * 
     * @param mixed $user Authenticated user
     * @return array Success response
     * @throws ServiceException When logout fails
     */
    public function logoutAllDevices($user): array
    {
        return $this->executeWithTransaction(function () use ($user) {
            // Revoke all tokens
            $user->tokens->each(function (Token $token) {
                $token->revoke();
            });

            return [
                'data' => null,
                'message' => 'Successfully logged out from all devices'
            ];
        }, 'Logout from all devices failed');
    }

    /**
     * Check if user is admin (customize based on your logic)
     */
    private function isAdmin($user): bool
    {
        // Example logic - customize based on your requirements
        return $user->role === 'admin' || $user->is_admin === true;
        
        // Or if you have separate admin table:
        // return AdminRepository::exists(['user_id' => $user->id]);
    }

    /**
     * Refresh token
     * 
     * @param int $userId User ID
     * @return array Token refresh result
     * @throws ServiceException When token refresh fails
     */
    public function refreshToken(int $userId): array
    {
        return $this->execute(function () use ($userId) {
            $user = $this->userRepository->findByIdOrFail($userId);
            
            // Create new token
            $token = $user->createToken('auth_token')->accessToken;
            
            return [
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ],
                'message' => 'Token refreshed successfully'
            ];
        }, 'Token refresh failed');
    }
}