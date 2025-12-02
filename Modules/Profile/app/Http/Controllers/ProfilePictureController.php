<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Profile\Http\Requests\UploadProfilePictureRequest;
use Modules\Profile\Services\ProfilePictureService;

class ProfilePictureController extends ApiController
{
    protected ProfilePictureService $profilePictureService;

    public function __construct(ProfilePictureService $profilePictureService)
    {
        parent::__construct();
        $this->profilePictureService = $profilePictureService;
    }

    /**
     * Upload profile picture for authenticated user/psw/admin
     *
     * @param string $userType - 'user', 'psw', or 'admin'
     * @param UploadProfilePictureRequest $request
     * @return JsonResponse
     */
    public function upload(string $userType, UploadProfilePictureRequest $request): JsonResponse
    {
        $file = $request->file('profile_picture');
        
        return $this->executeServiceForCreation(
            fn() => $this->profilePictureService->uploadProfilePicture($userType, $file)
        );
    }

    /**
     * Get profile picture for authenticated user
     *
     * @param string $userType - 'user', 'psw', or 'admin'
     * @return JsonResponse
     */
    public function show(string $userType): JsonResponse
    {
        return $this->executeService(
            fn() => $this->profilePictureService->getProfilePicture($userType)
        );
    }
}
