<?php

namespace Modules\Profile\Contracts\Repositories;

interface PswServiceCategoryRepositoryInterface
{
    /**
     * List service categories for a given psw_id
     *
     * @param int $pswId
     * @return array
     */
    public function listByPswId(int $pswId): array;

    /**
     * Get service category ids currently attached to a psw_profile_id
     *
     * @param int $pswProfileId
     * @return array
     */
    public function getIdsByProfileId(int $pswProfileId): array;

    /**
     * Sync categories for a psw: remove missing and insert new ones.
     *
     * @param int $pswId
     * @param int $pswProfileId
     * @param array $categoryIds
     * @return array
     */
    public function syncForPsw(int $pswId, int $pswProfileId, array $categoryIds): array;
}
