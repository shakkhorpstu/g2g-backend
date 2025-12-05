<?php

namespace Modules\Admin\Services;

use App\Shared\Services\BaseService;
use Modules\Core\Services\UserService;
use Modules\Profile\Services\UserProfileService;

class AdminClientService extends BaseService
{
    protected UserService $userService;
    protected UserProfileService $userProfileService;

    public function __construct(
        UserService $userService,
        UserProfileService $userProfileService
    ) {
        $this->userService = $userService;
        $this->userProfileService = $userProfileService;
    }

    /**
     * Get paginated list of clients with their profiles
     *
     * @param int $perPage
     * @param array $filters
     * @return array
     */
    public function index(int $perPage = 15, array $filters = []): array
    {
        // Use Core module service for user listing with filters
        $paginator = $this->userService->paginate($perPage, $filters);

        // Use Profile module service to get complete user data with profile
        $users = $paginator->map(function ($user) {
            return $this->userProfileService->getUserWithProfileById($user->id);
        })->filter()->values();

        return $this->success(
            [
                'data' => $users,
                'meta' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
            'Clients retrieved successfully.'
        );
    }

    /**
     * Get single client with profile
     *
     * @param int $id
     * @return array
     */
    public function show(int $id): array
    {
        // Check if user exists via Core service
        $user = $this->userService->findById($id);

        if (!$user) {
            $this->fail('Client not found.', 404);
        }

        // Get complete user data via Profile service
        $userData = $this->userProfileService->getUserWithProfileById($id);

        if (!$userData) {
            $this->fail('Client profile not found.', 404);
        }

        return $this->success(
            $userData,
            'Client retrieved successfully.'
        );
    }
}
