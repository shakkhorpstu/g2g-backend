<?php

namespace Modules\Profile\Services;

use Modules\Profile\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Shared\Services\BaseService;
use Modules\Core\Services\OtpService;

class UserProfileService extends BaseService
{
    /**
     * User profile repository instance
     *
     * @var UserProfileRepositoryInterface
     */
    protected UserProfileRepositoryInterface $userProfileRepository;
    protected OtpService $otpService;

    /**
     * UserProfileService constructor
     *
     * @param UserProfileRepositoryInterface $userProfileRepository
     */
    public function __construct(UserProfileRepositoryInterface $userProfileRepository, OtpService $otpService)
    {
        $this->userProfileRepository = $userProfileRepository;
        $this->otpService = $otpService;
    }

    /**
     * Get user profile
     *
     * @return array
     */
    public function getProfile(): array
    {
        $user = $this->getAuthenticatedUserOrFail(['api'], 'User not authenticated');

        $userWithProfile = $this->userProfileRepository->getUserWithProfile($user->id);

        return $this->success([
            'user' => $userWithProfile,
            'profile' => $userWithProfile->profile
        ], 'User profile retrieved successfully');
    }

    /**
     * Update user profile
     *
     * @param array $data
     * @return array
     */
    public function updateProfile(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $user = $this->getAuthenticatedUserOrFail(['api'], 'User not authenticated');

            // Handle email change separately with OTP verification
            if (isset($data['email']) && $data['email'] !== $user->email) {
                // Send OTP to NEW email for verification
                $this->otpService->resendOtp(
                    $data['email'], // send to new email
                    'email_change_verification',
                    get_class($user),
                    $user->id
                );

                // Remove email from profile update data - don't update yet
                unset($data['email']);

                return $this->success([
                    'message' => 'Email verification code sent to new email address. Please verify to complete the email change.'
                ], 'Email verification code sent. Please verify your new email address to complete the change.');
            }

            // Update other profile data (excluding email)
            $profileData = $data;
            
            if(!empty($profileData)) {
                $this->userProfileRepository->updateOrCreate($user->id, $profileData);
            }

            // Get updated user with profile
            $userWithProfile = $this->userProfileRepository->getUserWithProfile($user->id);

            return $this->success([
                'user' => $userWithProfile,
            ], 'User profile updated successfully');
        });
    }

    /**
     * Verify email change with OTP and complete the update
     *
     * @param array $data
     * @return array
     */
    public function verifyEmailChange(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $user = $this->getAuthenticatedUserOrFail(['api'], 'User not authenticated');

            // Verify OTP - this will throw exception if invalid
            // The OTP record contains the new email in the 'identifier' field
            $this->otpService->verifyOtp(
                $data['new_email'],
                $data['otp_code'],
                'email_change_verification'
            );

            // OTP verified - now update the email
            $user->email = $data['new_email'];
            $user->save();

            // Get updated user with profile
            $userWithProfile = $this->userProfileRepository->getUserWithProfile($user->id);

            return $this->success([
                'user' => $userWithProfile,
            ], 'Email updated successfully');
        });
    }

    /**
     * Create initial profile for user
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function createInitialProfile(int $userId, array $data = []): array
    {
        return $this->executeWithTransaction(function () use ($userId, $data) {
            $profileData = array_merge(['language_id' => null], $data);
            
            $profile = $this->userProfileRepository->create($userId, $profileData);

            return $this->success($profile, 'User profile created successfully');
        });
    }

    /**
     * Delete user profile
     *
     * @return array
     */
    public function deleteProfile(): array
    {
        return $this->executeWithTransaction(function () {
            $user = $this->getAuthenticatedUserOrFail(['api'], 'User not authenticated');

            $deleted = $this->userProfileRepository->delete($user->id);

            if (!$deleted) {
                $this->fail('Profile not found', 404);
            }

            return $this->success(null, 'User profile deleted successfully');
        });
    }
}