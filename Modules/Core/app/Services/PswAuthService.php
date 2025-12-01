<?php

namespace Modules\Core\Services;

use Modules\Core\Contracts\Repositories\PswRepositoryInterface;
use Modules\Core\Contracts\Repositories\AddressRepositoryInterface;
use App\Shared\Exceptions\ServiceException;
use App\Shared\Services\BaseService;
use Modules\Core\Events\PswRegistered;
use Modules\Core\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * PSW Authentication Service
 * 
 * Handles Professional Service Worker authentication business logic
 */
class PswAuthService extends BaseService
{
    /**
     * PSW repository instance
     *
     * @var PswRepositoryInterface
     */
    protected PswRepositoryInterface $pswRepository;

    /**
     * OTP service instance
     *
     * @var OtpService
     */
    protected OtpService $otpService;

    /**
     * Address repository instance
     *
     * @var AddressRepositoryInterface
     */
    protected AddressRepositoryInterface $addressRepository;

    /**
     * PswAuthService constructor
     *
     * @param PswRepositoryInterface $pswRepository
     * @param OtpService $otpService
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        PswRepositoryInterface $pswRepository,
        OtpService $otpService,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->pswRepository = $pswRepository;
        $this->otpService = $otpService;
        $this->addressRepository = $addressRepository;
    }

    /**
     * Register a new PSW
     *
     * @param array $data PSW registration data
     * @return array
     * @throws ServiceException
     */
    public function register(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            // Check if email already exists
            if ($this->pswRepository->findByEmail($data['email'])) {
                $this->fail('Email already exists', 422, ['email' => ['Email is already taken']]);
            }

            // Create PSW
            $psw = $this->pswRepository->create($data);

            // Fire PSW registered event (Profile module will handle profile creation)
            event(new PswRegistered($psw, $data));

            // Create default address with static data
            $address = $data['address'] ?? [];
            $this->createDefaultAddress($psw, $address);

            // Send account verification OTP to email and include OTP context in response
            // $otp = $this->otpService->resendOtp(
            //     $psw->email,
            //     'account_verification',
            //     get_class($psw),
            //     $psw->id
            // );

            $psw->setAttribute('otpable_type', get_class($psw));
            $psw->setAttribute('otpable_id', $psw->id);
            return $this->success($psw, 'PSW registered successfully', 201);
        });
    }

    /**
     * Create default address for newly registered PSW
     *
     * @param mixed $psw
     * @param array $address
     * @return void
     */
    protected function createDefaultAddress($psw, $address = []): void
    {
        // Static default address data (will be replaced with form data later)
        $addressData = [
            'addressable_type' => get_class($psw),
            'addressable_id' => $psw->id,
            'label' => $address['label'] ?? 'HOME',
            'address_line' => $address['address_line'] ?? '',
            'city' => $address['city'] ?? '',
            'province' => $address['province'] ?? '',
            'postal_code' => $address['postal_code'] ?? '',
            'country_id' => $address['country_id'] ?? env('DEFAULT_COUNTRY_ID', 1), // Assuming country with ID 1 exists
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $this->addressRepository->create($addressData);
    }

    /**
     * Login PSW and return access token
     *
     * @param array $credentials Login credentials
     * @return array
     * @throws ServiceException
     */
    public function login(array $credentials): array
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

            // Block login if account not verified
            if (!(bool) $psw->is_verified) {
                $this->fail(
                    'Account not verified. Please verify your email to continue.',
                    403,
                    [
                        'requires_verification' => true,
                        'email' => $psw->email
                    ]
                );
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
    public function logout(): array
    {
        return $this->executeWithTransaction(function () {
            $psw = Auth::guard('psw-api')->user();
            
            if (!$psw) {
                $this->fail('PSW not authenticated', 401);
            }

            // Logout from all devices by revoking all tokens
            $psw->tokens->each(function ($token) {
                $token->revoke();
            });

            return $this->success(null, 'PSW logout successful');
        });
    }

    /**
     * Refresh PSW token (revoke current and generate new)
     *
     * @return array
     * @throws ServiceException
     */
    public function refresh(): array
    {
        return $this->executeWithTransaction(function () {
            $psw = Auth::guard('psw-api')->user();
            
            if (!$psw) {
                $this->fail('PSW not authenticated', 401);
            }

            // Revoke all existing tokens
            $psw->tokens->each(function ($token) {
                $token->revoke();
            });

            // Generate new token
            $token = $psw->createToken('psw_auth_token')->accessToken;

            return $this->success([
                'psw' => $psw,
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'PSW token refreshed successfully');
        });
    }

    /**
     * Change PSW password
     *
     * @param array $data Password change data
     * @return array
     * @throws ServiceException
     */
    public function changePassword(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $psw = Auth::guard('psw-api')->user();
            
            if (!$psw) {
                $this->fail('PSW not authenticated', 401);
            }

            // Verify current password
            if (!Hash::check($data['current_password'], $psw->password)) {
                $this->fail('Current password is incorrect', 422, ['current_password' => ['Current password is incorrect']]);
            }

            // Update password
            $this->pswRepository->updatePassword($psw, $data['new_password']);

            return $this->success(null, 'PSW password changed successfully');
        });
    }

    /**
     * Verify PSW account using OTP and mark as verified
     *
     * @param array $data ['email','otp_code']
     */
    public function verifyAccount(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $psw = $this->pswRepository->findByEmail($data['email']);

            if (!$psw) {
                $this->fail('PSW not found with this email address', 404);
            }

            // Verify OTP
            $this->otpService->verifyOtp(
                $data['identifier'],
                $data['otp_code'],
                'account_verification'
            );

            // Mark as verified
            $psw = $this->pswRepository->update($psw, [
                'is_verified' => true,
                'email_verified_at' => now(),
            ]);

            return $this->success($psw, 'Congratulations! Your account has been verified. You may now access all features');
        });
    }

    /**
     * Send password reset OTP to PSW email
     */
    public function forgotPassword(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $psw = $this->pswRepository->findByEmail($data['email']);

            if (!$psw) {
                $this->fail('PSW not found with this email address', 404);
            }

            $this->otpService->resendOtp(
                $psw->email,
                'password_reset',
                get_class($psw),
                $psw->id
            );

            return $this->success([
                'email' => $psw->email
            ], 'Password reset OTP sent to your email address');
        });
    }

    /**
     * Reset PSW password using OTP
     */
    public function resetPassword(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $psw = $this->pswRepository->findByEmail($data['email']);

            if (!$psw) {
                $this->fail('PSW not found with this email address', 404);
            }

            // Verify OTP
            $this->otpService->verifyOtp(
                $data['email'],
                $data['otp_code'],
                'password_reset'
            );

            // Update password
            $this->pswRepository->updatePassword($psw, $data['new_password']);

            // Revoke all tokens
            $psw->tokens->each(function ($token) {
                $token->revoke();
            });

            return $this->success(null, 'Password reset successfully. Please login with your new password.');
        });
    }
}