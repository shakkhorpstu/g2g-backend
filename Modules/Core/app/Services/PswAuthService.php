<?php

namespace Modules\Core\Services;

use Modules\Core\Contracts\Repositories\PswRepositoryInterface;
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
     * PswAuthService constructor
     *
     * @param PswRepositoryInterface $pswRepository
     * @param OtpService $otpService
     */
    public function __construct(PswRepositoryInterface $pswRepository, OtpService $otpService)
    {
        $this->pswRepository = $pswRepository;
        $this->otpService = $otpService;
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

            // Send account verification OTP to email
            $this->otpService->resendOtp(
                $psw->email,
                'account_verification',
                get_class($psw),
                $psw->id
            );

            // Generate token using Passport
            $token = $psw->createToken('psw_auth_token')->accessToken;

            return $this->success([
                'psw' => $psw,
                'token' => $token,
                'token_type' => 'Bearer',
                'message' => 'Please verify your email with the OTP sent to your email address'
            ], 'PSW registered successfully. Verification OTP sent to email.', 201);
        });
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
}