<?php

namespace Modules\Core\Services;

use Modules\Core\Contracts\Repositories\AuthRepositoryInterface;
use Modules\Core\Contracts\Repositories\PswRepositoryInterface;
use Modules\Core\Contracts\Repositories\AdminRepositoryInterface;
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
     * PSW repository instance
     *
     * @var PswRepositoryInterface
     */
    protected PswRepositoryInterface $pswRepository;

    /**
     * Admin repository instance
     *
     * @var AdminRepositoryInterface
     */
    protected AdminRepositoryInterface $adminRepository;

    /**
     * AuthService constructor
     *
     * @param AuthRepositoryInterface $authRepository
     * @param PswRepositoryInterface $pswRepository
     * @param AdminRepositoryInterface $adminRepository
     */
    public function __construct(AuthRepositoryInterface $authRepository, PswRepositoryInterface $pswRepository, AdminRepositoryInterface $adminRepository)
    {
        $this->authRepository = $authRepository;
        $this->pswRepository = $pswRepository;
        $this->adminRepository = $adminRepository;
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
            ], 'You have been registered successfully', 201);
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
            // Find admin by email
            $admin = $this->adminRepository->findByEmail($credentials['email']);
            
            if (!$admin) {
                $this->fail('Invalid credentials', 401);
            }

            // Check if admin is active
            if (!$admin->isActive()) {
                $this->fail('Account is not active. Please contact administrator.', 403);
            }

            // Verify password
            if (!Hash::check($credentials['password'], $admin->password)) {
                $this->fail('Invalid credentials', 401);
            }

            // Update last login
            $this->adminRepository->updateLastLogin($admin);

            // Generate token
            $token = $admin->createToken('admin_token')->accessToken;

            return $this->success([
                'admin' => $admin,
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

    /**
     * Register a new PSW
     *
     * @param array $data PSW registration data
     * @return array
     * @throws ServiceException
     */
    public function pswRegister(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            // Check if email already exists
            if ($this->pswRepository->findByEmail($data['email'])) {
                $this->fail('Email already exists', 422, ['email' => ['Email is already taken']]);
            }

            // Create PSW
            $psw = $this->pswRepository->create($data);

            // Generate token using Passport
            $token = $psw->createToken('psw_auth_token')->accessToken;

            return $this->success([
                'psw' => $psw,
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'You have been registered successfully', 201);
        });
    }

    /**
     * Login PSW and return access token
     *
     * @param array $credentials Login credentials
     * @return array
     * @throws ServiceException
     */
    public function pswLogin(array $credentials): array
    {
        return $this->executeWithTransaction(function () use ($credentials) {
            // Find PSW by email
            $psw = $this->pswRepository->findByEmail($credentials['email']);
            
            if (!$psw) {
                $this->fail('Invalid credentials', 401);
            }

            // Verify password
            if (!Hash::check($credentials['password'], $psw->password)) {
                $this->fail('Invalid credentials', 401);
            }

            // Update last login
            $this->pswRepository->updateLastLogin($psw);

            // Generate token
            $token = $psw->createToken('psw_auth_token')->accessToken;

            return $this->success([
                'psw' => $psw,
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'PSW login successful');
        });
    }

    /**
     * Logout PSW (revoke current token)
     *
     * @return array
     * @throws ServiceException
     */
    public function pswLogout(): array
    {
        return $this->executeWithTransaction(function () {
            $psw = Auth::guard('psw-api')->user();
            
            if (!$psw) {
                $this->fail('PSW not authenticated', 401);
            }

            // Revoke current token
            $psw->token()->revoke();

            return $this->success(null, 'PSW logout successful');
        });
    }

    /**
     * Get PSW profile
     *
     * @return array
     * @throws ServiceException
     */
    public function getPswProfile(): array
    {
        $psw = Auth::guard('psw-api')->user();
        
        if (!$psw) {
            $this->fail('PSW not authenticated', 401);
        }

        return $this->success($psw, 'PSW profile retrieved successfully');
    }
}