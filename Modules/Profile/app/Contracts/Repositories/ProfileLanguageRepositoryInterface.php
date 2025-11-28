<?php

namespace Modules\Profile\Contracts\Repositories;

use Modules\Profile\Models\ProfileLanguage;

interface ProfileLanguageRepositoryInterface
{
    /**
     * Get languages for owner
     *
     * Returns an array of ProfileLanguage models (may be empty)
     */
    public function getForOwner($owner): array;

    /**
     * Sync languages for owner (create/delete as needed)
     *
     * Accepts an array of language strings and returns created/remaining models.
     */
    public function syncForOwner($owner, array $languages): array;

    /**
     * Delete all languages for owner
     */
    public function delete($owner): bool;
}
