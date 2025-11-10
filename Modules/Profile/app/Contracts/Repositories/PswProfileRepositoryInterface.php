<?php

namespace Modules\Profile\Contracts\Repositories;

use Modules\Core\Models\PswProfile;
use Modules\Core\Models\Psw;

interface PswProfileRepositoryInterface
{
    /**
     * Find PSW profile by PSW ID
     *
     * @param int $pswId
     * @return PswProfile|null
     */
    public function findByPswId(int $pswId): ?PswProfile;

    /**
     * Create PSW profile
     *
     * @param int $pswId
     * @param array $data
     * @return PswProfile
     */
    public function create(int $pswId, array $data): PswProfile;

    /**
     * Update PSW profile
     *
     * @param int $pswId
     * @param array $data
     * @return PswProfile
     */
    public function update(int $pswId, array $data): PswProfile;

    /**
     * Update or create PSW profile
     *
     * @param int $pswId
     * @param array $data
     * @return PswProfile
     */
    public function updateOrCreate(int $pswId, array $data): PswProfile;

    /**
     * Delete PSW profile
     *
     * @param int $pswId
     * @return bool
     */
    public function delete(int $pswId): bool;

    /**
     * Get PSW with profile
     *
     * @param int $pswId
     * @return Psw|null
     */
    public function getPswWithProfile(int $pswId): ?Psw;
}