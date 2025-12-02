<?php

namespace Modules\Profile\Contracts\Repositories;

interface AdminProfileRepositoryInterface
{
    /**
     * Get admin with profile picture
     *
     * @param int $adminId
     * @return array|null
     */
    public function getAdminWithProfile(int $adminId): ?array;
}
