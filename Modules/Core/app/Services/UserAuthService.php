<?php

namespace Modules\Core\Services;

use Modules\Core\Contracts\Repositories\UserRepositoryInterface;
use Modules\Core\Contracts\Repositories\OtpRepositoryInterface;
use Modules\Core\Contracts\Repositories\AddressRepositoryInterface;
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
     * OTP repository instance
     *
     * @var OtpRepositoryInterface
     */
    protected OtpRepositoryInterface $otpRepository;

    /**
     * Address repository instance
     *
     * @var AddressRepositoryInterface
     */
    protected AddressRepositoryInterface $addressRepository;

    /**
     * UserAuthService constructor
     *
     * @param UserRepositoryInterface $userRepository
     * @param OtpService $otpService
     * @param OtpRepositoryInterface $otpRepository
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        OtpService $otpService,
        OtpRepositoryInterface $otpRepository,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->userRepository = $userRepository;
        $this->otpService = $otpService;
        $this->otpRepository = $otpRepository;
        $this->addressRepository = $addressRepository;
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

            // Create default address with static data
            $this->createDefaultAddress($user);

            // Send account verification OTP to email
            // $this->otpService->resendOtp(
            //     $user->email,
            //     'account_verification',
            //     get_class($user),
            //     $user->id
            // );

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
     * Create default address for newly registered user
     *
     * @param mixed $user
     * @return void
     */
    protected function createDefaultAddress($user): void
    {
        // Static default address data (will be replaced with form data later)
        $addressData = [
            'addressable_type' => get_class($user),
            'addressable_id' => $user->id,
            'label' => 'HOME',
            'address_line' => '123 Default Street',
            'city' => 'Default City',
            'province' => 'Default Province',
            'postal_code' => '00000',
            'country_id' => 1, // Assuming country with ID 1 exists
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $this->addressRepository->create($addressData);
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

            // Block login if account not verified on user record
            if (!(bool) $user->is_verified) {
                $this->fail(
                    'Account not verified. Please verify your email to continue.',
                    403,
                    [
                        'requires_verification' => true,
                        'email' => $user->email
                    ]
                );
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

    /**
     * Request forgot password OTP
     *
     * @param array $data Email data
     * @return array
     * @throws ServiceException
     */
    public function forgotPassword(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            // Find user by email
            $user = $this->userRepository->findByEmail($data['email']);
            
            if (!$user) {
                $this->fail('User not found with this email address', 404);
            }

            // Send password reset OTP to email
            $this->otpService->resendOtp(
                $user->email,
                'password_reset',
                get_class($user),
                $user->id
            );

            return $this->success([
                'email' => $user->email
            ], 'Password reset OTP sent to your email address');
        });
    }

    /**
     * Reset password using OTP
     *
     * @param array $data Reset password data (email, otp_code, new_password)
     * @return array
     * @throws ServiceException
     */
    public function resetPassword(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            // Find user by email
            $user = $this->userRepository->findByEmail($data['email']);
            
            if (!$user) {
                $this->fail('User not found with this email address', 404);
            }

            // Verify OTP - this will throw exception if invalid
            $this->otpService->verifyOtp(
                $data['email'],
                $data['otp_code'],
                'password_reset'
            );

            // Update password
            $this->userRepository->updatePassword($user, $data['new_password']);

            // Revoke all existing tokens for security
            $user->tokens->each(function ($token) {
                $token->revoke();
            });

            return $this->success(null, 'Password reset successfully. Please login with your new password.');
        });
    }

    /**
     * Verify account using OTP and mark user as verified
     *
     * @param array $data ['email','otp_code']
     * @return array
     * @throws ServiceException
     */
    public function verifyAccount(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            // Find user by email
            $user = $this->userRepository->findByEmail($data['email']);
            
            if (!$user) {
                $this->fail('User not found with this email address', 404);
            }

            // Verify OTP - will throw on failure
            $this->otpService->verifyOtp(
                $data['email'],
                $data['otp_code'],
                'account_verification'
            );

            // Mark user as verified
            $user = $this->userRepository->markEmailAsVerified($user);

            return $this->success([
                'user' => $user
            ], 'Account verified successfully');
        });
    }
}