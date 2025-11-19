<?php

namespace Modules\Core\Services;

use Modules\Core\Contracts\Repositories\AdminRepositoryInterface;
use App\Shared\Exceptions\ServiceException;
use App\Shared\Services\BaseService;
use Modules\Core\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Admin Authentication Service
 * 
 * Handles administrator authentication business logic
 */
class AdminAuthService extends BaseService
{
    /**
     * Admin repository instance
     *
     * @var AdminRepositoryInterface
     */
    protected AdminRepositoryInterface $adminRepository;

    /**
     * OTP service instance
     *
     * @var OtpService
     */
    protected OtpService $otpService;

    /**
     * AdminAuthService constructor
     *
     * @param AdminRepositoryInterface $adminRepository
     * @param OtpService $otpService
     */
    public function __construct(AdminRepositoryInterface $adminRepository, OtpService $otpService)
    {
        $this->adminRepository = $adminRepository;
        $this->otpService = $otpService;
    }

    /**
     * Admin login with role verification
     *
     * @param array $credentials Login credentials
     * @return array
     * @throws ServiceException
     */
    public function login(array $credentials): array
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
     * Logout admin (revoke current token)
     *
     * @return array
     * @throws ServiceException
     */
    public function logout(): array
    {
        return $this->executeWithTransaction(function () {
            $admin = Auth::guard('admin-api')->user();
            
            if (!$admin) {
                $this->fail('Admin not authenticated', 401);
            }

            // Logout from all devices by revoking all tokens
            $admin->tokens->each(function ($token) {
                $token->revoke();
            });

            return $this->success(null, 'Admin logout successful');
        });
    }

    /**
     * Refresh admin token (revoke current and generate new)
     *
     * @return array
     * @throws ServiceException
     */
    public function refresh(): array
    {
        return $this->executeWithTransaction(function () {
            $admin = Auth::guard('admin-api')->user();
            
            if (!$admin) {
                $this->fail('Admin not authenticated', 401);
            }

            // Revoke all existing tokens
            $admin->tokens->each(function ($token) {
                $token->revoke();
            });

            // Generate new token
            $token = $admin->createToken('admin_token')->accessToken;

            return $this->success([
                'admin' => $admin,
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'Admin token refreshed successfully');
        });
    }

    /**
     * Get current admin profile
     *
     * @return array
     * @throws ServiceException
     */
    public function getProfile(): array
    {
        $admin = Auth::guard('admin-api')->user();
        
        if (!$admin) {
            $this->fail('Admin not authenticated', 401);
        }

        return $this->success($admin, 'Admin profile retrieved successfully');
    }

    /**
     * Change admin password
     *
     * @param array $data Password change data
     * @return array
     * @throws ServiceException
     */
    public function changePassword(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $admin = Auth::guard('admin-api')->user();
            
            if (!$admin) {
                $this->fail('Admin not authenticated', 401);
            }

            // Verify current password
            if (!Hash::check($data['current_password'], $admin->password)) {
                $this->fail('Current password is incorrect', 422, ['current_password' => ['Current password is incorrect']]);
            }

            // Update password
            $this->adminRepository->updatePassword($admin, $data['new_password']);

            return $this->success(null, 'Admin password changed successfully');
        });
    }

    /**
     * Send password reset OTP to admin email
     */
    public function forgotPassword(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $admin = $this->adminRepository->findByEmail($data['email']);

            if (!$admin) {
                $this->fail('Admin not found with this email address', 404);
            }

            $this->otpService->resendOtp(
                $admin->email,
                'password_reset',
                get_class($admin),
                $admin->id
            );

            return $this->success([
                'email' => $admin->email
            ], 'Password reset OTP sent to your email address');
        });
    }

    /**
     * Reset admin password using OTP
     */
    public function resetPassword(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $admin = $this->adminRepository->findByEmail($data['email']);

            if (!$admin) {
                $this->fail('Admin not found with this email address', 404);
            }

            // Verify OTP
            $this->otpService->verifyOtp(
                $data['email'],
                $data['otp_code'],
                'password_reset'
            );

            // Update password
            $this->adminRepository->updatePassword($admin, $data['new_password']);

            // Revoke all tokens
            $admin->tokens->each(function ($token) {
                $token->revoke();
            });

            return $this->success(null, 'Password reset successfully. Please login with your new password.');
        });
    }
}