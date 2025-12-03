<?php

namespace Modules\Core\Services;

use Modules\Core\Contracts\Repositories\AuthRepositoryInterface;
use Modules\Core\Contracts\Repositories\PswRepositoryInterface;
use Modules\Core\Contracts\Repositories\AdminRepositoryInterface;
use Modules\Profile\Contracts\Repositories\UserProfileRepositoryInterface;
use Modules\Profile\Contracts\Repositories\PswProfileRepositoryInterface;
use Modules\Core\Services\OtpService;
use App\Shared\Exceptions\ServiceException;
use App\Shared\Services\BaseService;
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

    protected UserProfileRepositoryInterface $userProfileRepository;
    protected PswProfileRepositoryInterface $pswProfileRepository;
    protected OtpService $otpService;

    /**
     * AuthService constructor
     *
     * @param AuthRepositoryInterface $authRepository
     * @param PswRepositoryInterface $pswRepository
     * @param AdminRepositoryInterface $adminRepository
     */
    public function __construct(
        AuthRepositoryInterface $authRepository,
        PswRepositoryInterface $pswRepository,
        AdminRepositoryInterface $adminRepository,
        UserProfileRepositoryInterface $userProfileRepository,
        PswProfileRepositoryInterface $pswProfileRepository,
        OtpService $otpService
    ) {
        $this->authRepository = $authRepository;
        $this->pswRepository = $pswRepository;
        $this->adminRepository = $adminRepository;
        $this->userProfileRepository = $userProfileRepository;
        $this->pswProfileRepository = $pswProfileRepository;
        $this->otpService = $otpService;
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

            // Check 2FA status on user profile
            $profile = $this->userProfileRepository->findByUserId($user->id);
            if ($profile && !empty($profile->{'2fa_enabled'})) {
                // Send OTP for two-factor login
                $identifier = ($profile->{'2fa_identifier_key'} ?? '') === 'phone' ? $user->phone_number : $user->email;
                if (!$identifier) {
                    $this->fail('Two-factor is enabled but identifier not configured', 422);
                }

                $this->otpService->resendOtp($identifier, 'two_factor', get_class($user), $user->id);

                return $this->success([
                    'requires_2fa' => true,
                    'identifier' => $profile->{'2fa_identifier_key'} ?? null,
                ], 'Two-factor authentication required');
            }

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

            // Check 2FA on PSW profile
            $profile = $this->pswProfileRepository->findByPswId($psw->id);
            if ($profile && !empty($profile->{'2fa_enabled'})) {
                $identifier = ($profile->{'2fa_identifier_key'} ?? '') === 'phone' ? $psw->phone_number : $psw->email;
                if (!$identifier) {
                    $this->fail('Two-factor is enabled but identifier not configured', 422);
                }

                $this->otpService->resendOtp($identifier, 'two_factor', get_class($psw), $psw->id);

                return $this->success([
                    'requires_2fa' => true,
                    'identifier' => $profile->{'2fa_identifier_key'} ?? null,
                ], 'Two-factor authentication required');
            }

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
     * Verify two-factor OTP (login-time) and create token
     *
     * @param array $data ['identifier' => '...', 'otp_code' => '123456']
     * @return array
     */
    public function verifyTwoFactor(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $userId = $data['user_id'] ?? null;
            $identifier = $data['identifier'] ?? null;
            $otpCode = $data['otp_code'] ?? null;

            if (!$userId || !$identifier || !$otpCode) {
                $this->fail('user_id, identifier and otp_code are required', 422);
            }

            $expectedType = null;
            $expectedId = null;

            // Check if identifier belongs to a user record with provided id
            $user = $this->authRepository->findById((int) $userId);
            if ($user) {
                if ($identifier === ($user->email ?? null) || $identifier === ($user->phone_number ?? null)) {
                    $expectedType = get_class($user);
                    $expectedId = $user->id;
                }
            }

            // If not matched with user, check PSW
            if (!$expectedType) {
                $psw = $this->pswRepository->findById((int) $userId);
                if ($psw) {
                    if ($identifier === ($psw->email ?? null) || $identifier === ($psw->phone_number ?? null)) {
                        $expectedType = get_class($psw);
                        $expectedId = $psw->id;
                    }
                }
            }

            if (!$expectedType || !$expectedId) {
                $this->fail('The provided identifier does not belong to the given user_id', 422);
            }

            // Verify OTP and get otpable info
            $result = $this->otpService->verifyOtp($identifier, $otpCode, 'two_factor');
            $otpData = $result['data'] ?? [];
            $otpableType = $otpData['otpable_type'] ?? null;
            $otpableId = $otpData['otpable_id'] ?? null;

            if (!$otpableType || !$otpableId) {
                $this->fail('Invalid OTP verification result', 400);
            }

            // Ensure OTP belongs to expected account
            if ($otpableType !== $expectedType || (int) $otpableId !== (int) $expectedId) {
                $this->fail('OTP does not belong to the provided user_id', 422);
            }

            // Instantiate model and find record
            if (!class_exists($otpableType)) {
                $this->fail('Unknown account type', 400);
            }

            $model = $otpableType::find($otpableId);
            if (!$model) {
                $this->fail('Account not found', 404);
            }

            // Create token depending on type
            if (stripos($otpableType, 'Psw') !== false) {
                $token = $model->createToken('psw_auth_token')->accessToken;
                return $this->success([
                    'psw' => $model,
                    'token' => $token,
                    'token_type' => 'Bearer'
                ], 'Login successful');
            }

            // Default to user
            $token = $model->createToken('auth_token')->accessToken;
            return $this->success([
                'user' => $model,
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'Login successful');
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