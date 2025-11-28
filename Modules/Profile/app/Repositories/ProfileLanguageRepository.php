<?php

namespace Modules\Profile\Repositories;

use Modules\Profile\Contracts\Repositories\ProfileLanguageRepositoryInterface;
use Modules\Profile\Models\ProfileLanguage;
use Illuminate\Support\Facades\DB;

class ProfileLanguageRepository implements ProfileLanguageRepositoryInterface
{
    /**
     * Get languages for owner
     *
     * Returns an array of ProfileLanguage models
     */
    public function getForOwner($owner): array
    {
        $rows = DB::table('profile_languages')
            ->where('languageable_type', get_class($owner))
            ->where('languageable_id', $owner->id)
            ->get()
            ->toArray();

        if (empty($rows)) {
            return [];
        }

        $attrs = array_map(fn($r) => (array) $r, $rows);
        return ProfileLanguage::hydrate($attrs)->all();
    }

    /**
     * Sync languages for owner (create/delete as needed)
     *
     * Returns the current languages as ProfileLanguage models
     */
    public function syncForOwner($owner, array $languages): array
    {
        $languages = array_values(array_filter(array_map('trim', $languages)));
        $languages = array_values(array_unique($languages));

        // current languages
        $current = DB::table('profile_languages')
            ->where('languageable_type', get_class($owner))
            ->where('languageable_id', $owner->id)
            ->pluck('language')
            ->map(fn($v) => (string) $v)
            ->toArray();

        $toDelete = array_diff($current, $languages);
        $toInsert = array_diff($languages, $current);

        if (!empty($toDelete)) {
            DB::table('profile_languages')
                ->where('languageable_type', get_class($owner))
                ->where('languageable_id', $owner->id)
                ->whereIn('language', $toDelete)
                ->delete();
        }

        if (!empty($toInsert)) {
            $now = now();
            $payload = array_map(fn($lang) => [
                'languageable_type' => get_class($owner),
                'languageable_id' => $owner->id,
                'language' => $lang,
                'created_at' => $now,
                'updated_at' => $now,
            ], $toInsert);

            DB::table('profile_languages')->insert($payload);
        }

        return $this->getForOwner($owner);
    }

    /**
     * Delete all languages for owner
     */
    public function delete($owner): bool
    {
        return DB::table('profile_languages')
            ->where('languageable_type', get_class($owner))
            ->where('languageable_id', $owner->id)
            ->delete() > 0;
    }
}
