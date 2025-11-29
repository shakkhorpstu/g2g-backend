<?php

namespace Modules\Profile\Services;

use App\Shared\Services\BaseService;
use Modules\Profile\Contracts\Repositories\PswServiceCategoryRepositoryInterface;
use Modules\Profile\Models\PswProfile;

class PswServiceCategoryService extends BaseService
{
    protected PswServiceCategoryRepositoryInterface $repo;
    protected array $allowedGuards = ['psw-api'];

    public function __construct(PswServiceCategoryRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Get service categories for authenticated PSW
     *
     * @return array
     */
    public function listForAuthenticatedPsw(): array
    {
        $psw = $this->getAuthenticatedUserOrFail($this->allowedGuards, 'PSW not authenticated');

        $categories = $this->repo->listByPswId($psw->id);
        $profile = PswProfile::firstOrCreate(['psw_id' => $psw->id], []);

        return $this->success([
            'service_categories' => $categories,
            'psw_profile' => [
                'id' => $profile->id,
                'has_own_vehicle' => (bool) $profile->has_own_vehicle,
                'hourly_rate' => $profile->hourly_rate,
                'include_driving_allowance' => $profile->include_driving_allowance,
                'driving_allowance_per_km' => $profile->driving_allowance_per_km,
            ]
        ], 'Service categories retrieved successfully');
    }

    /**
     * Sync service categories for authenticated PSW (create/update/delete via single call)
     *
     * @param array $data [ 'service_category_ids' => [...], 'has_own_vehicle' => bool|null ]
     * @return array
     */
    public function syncForAuthenticatedPsw(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $psw = $this->getAuthenticatedUserOrFail($this->allowedGuards, 'PSW not authenticated');

            // ensure profile exists
            $profile = PswProfile::firstOrCreate(['psw_id' => $psw->id], []);

            if (array_key_exists('has_own_vehicle', $data) && $data['has_own_vehicle'] !== null) {
                $profile->has_own_vehicle = (bool) $data['has_own_vehicle'];
                $profile->save();
            }

            $categoryIds = $data['service_category_ids'] ?? [];

            $categories = $this->repo->syncForPsw($psw->id, $profile->id, $categoryIds);

            $profile->refresh();

            return $this->success([
                'service_categories' => $categories,
                'psw_profile' => [
                    'id' => $profile->id,
                    'has_own_vehicle' => (bool) $profile->has_own_vehicle,
                    'hourly_rate' => $profile->hourly_rate,
                    'include_driving_allowance' => $profile->include_driving_allowance,
                    'driving_allowance_per_km' => $profile->driving_allowance_per_km,
                ]
            ], 'Service categories synced successfully');
        });
    }
}
