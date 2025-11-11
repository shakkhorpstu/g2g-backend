<?php

namespace Modules\Core\Services;

use Modules\Core\Contracts\Repositories\UserRepositoryInterface;
use App\Shared\Exceptions\ServiceException;
use App\Shared\Services\BaseService;
use Modules\Core\Events\UserRegistered;
use Modules\Core\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * User Authentication Service
 * 
 * Handles client/user authentication business logic
 */
class UserAuthService extends BaseService
{
    /**
     * User repository instance
     *
     * @var UserRepositoryInterface
     */
    protected UserRepositoryInterface $userRepository;

    /**
     * OTP service instance
     *
     * @var OtpService
     */
    protected OtpService $otpService;

    /**
     * UserAuthService constructor
     *
     * @param UserRepositoryInterface $userRepository
     * @param OtpService $otpService
     */
    public function __construct(UserRepositoryInterface $userRepository, OtpService $otpService)
    {
        $this->userRepository = $userRepository;
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
            if ($this->userRepository->findByEmail($data['email'])) {
                $this->fail('Email already exists', 422, ['email' => ['Email is already taken']]);
            }

            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = \Modules\Core\Models\User::STATUS_ACTIVE;
            }

            // Create user
            $user = $this->userRepository->create($data);

            // Fire user registered event (Profile module will handle profile creation)
            event(new UserRegistered($user, $data));

            // Send account verification OTP to email
            $this->otpService->resendOtp(
                $user->email,
                'account_verification',
                get_class($user),
                $user->id
            );

            // Generate token using Passport
            $token = $user->createToken('auth_token')->accessToken;

            return $this->success([
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
                'message' => 'Please verify your email with the OTP sent to your email address'
            ], 'User registered successfully. Verification OTP sent to email.', 201);
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
            $user = $this->userRepository->findByEmail($credentials['email']);
            
            if (!$user) {
                $this->fail('Invalid credentials', 401);
            }

            // Verify password
            if (!Hash::check($credentials['password'], $user->password)) {
                $this->fail('Invalid credentials', 401);
            }

            // Update last login
            $this->userRepository->updateLastLogin($user);

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
     * Logout user (revoke current token)
     *
     * @return array
     * @throws ServiceException
     */
    public function logout(): array
    {
        return $this->executeWithTransaction(function () {
            $user = Auth::guard('api')->user();
            
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
     * Refresh user token (revoke current and generate new)
     *
     * @return array
     * @throws ServiceException
     */
    public function refresh(): array
    {
        return $this->executeWithTransaction(function () {
            $user = Auth::guard('api')->user();
            
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
            $user = Auth::guard('api')->user();
            
            if (!$user) {
                $this->fail('User not authenticated', 401);
            }

            // Verify current password
            if (!Hash::check($data['current_password'], $user->password)) {
                $this->fail('Current password is incorrect', 422, ['current_password' => ['Current password is incorrect']]);
            }

            // Update password
            $this->userRepository->updatePassword($user, $data['new_password']);

            return $this->success(null, 'Password changed successfully');
        });
    }
}