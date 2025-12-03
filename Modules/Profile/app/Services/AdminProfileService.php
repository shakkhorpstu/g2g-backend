<?php

namespace Modules\Profile\Services;

use App\Shared\Services\BaseService;
use Modules\Profile\Contracts\Repositories\AdminProfileRepositoryInterface;

class AdminProfileService extends BaseService
{
    protected AdminProfileRepositoryInterface $adminProfileRepository;

    public function __construct(AdminProfileRepositoryInterface $adminProfileRepository)
    {
        $this->adminProfileRepository = $adminProfileRepository;
    }

    /**
     * Get admin profile with profile picture
     *
     * @return array
     */
    public function getProfile(): array
    {
        $admin = $this->getAuthenticatedUser(['admin-api']);

        if (!$admin) {
            $this->fail('Admin not authenticated', 401);
        }

        $adminData = $this->adminProfileRepository->getAdminWithProfile($admin->id);

        if (!$adminData) {
            $this->fail('Admin not found', 404);
        }

        return $this->success($adminData, 'Admin profile retrieved successfully');
    }
}
