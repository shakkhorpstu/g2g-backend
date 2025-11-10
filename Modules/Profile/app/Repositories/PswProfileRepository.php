<?php

namespace Modules\Profile\Repositories;

use Modules\Profile\Contracts\Repositories\PswProfileRepositoryInterface;
use Modules\Core\Models\PswProfile;
use Modules\Core\Models\Psw;

class PswProfileRepository implements PswProfileRepositoryInterface
{
    /**
     * Find PSW profile by PSW ID
     *
     * @param int $pswId
     * @return PswProfile|null
     */
    public function findByPswId(int $pswId): ?PswProfile
    {
        return PswProfile::where('psw_id', $pswId)->first();
    }

    /**
     * Create PSW profile
     *
     * @param int $pswId
     * @param array $data
     * @return PswProfile
     */
    public function create(int $pswId, array $data): PswProfile
    {
        $data['psw_id'] = $pswId;
        return PswProfile::create($data);
    }

    /**
     * Update PSW profile
     *
     * @param int $pswId
     * @param array $data
     * @return PswProfile
     */
    public function update(int $pswId, array $data): PswProfile
    {
        $profile = $this->findByPswId($pswId);
        
        if (!$profile) {
            throw new \Exception('PSW profile not found');
        }

        $profile->update($data);
        return $profile->fresh();
    }

    /**
     * Update or create PSW profile
     *
     * @param int $pswId
     * @param array $data
     * @return PswProfile
     */
    public function updateOrCreate(int $pswId, array $data): PswProfile
    {
        return PswProfile::updateOrCreate(
            ['psw_id' => $pswId],
            $data
        );
    }

    /**
     * Delete PSW profile
     *
     * @param int $pswId
     * @return bool
     */
    public function delete(int $pswId): bool
    {
        $profile = $this->findByPswId($pswId);
        
        if (!$profile) {
            return false;
        }

        return $profile->delete();
    }

    /**
     * Get PSW with profile
     *
     * @param int $pswId
     * @return Psw|null
     */
    public function getPswWithProfile(int $pswId): ?Psw
    {
        return Psw::with('profile')->find($pswId);
    }
}