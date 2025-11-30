<?php

namespace Modules\Profile\Services\Admin;

use App\Shared\Services\BaseService;
use Modules\Profile\Models\Preference;

class PreferenceService extends BaseService
{
    /**
     * Get all preferences (public listing)
     *
     * @return array
     */
    public function getAll(): array
    {
        $prefs = Preference::orderBy('title')->get(['id', 'title'])->toArray();
        return $this->success($prefs, 'Preferences retrieved');
    }

    /**
     * Create a preference
     *
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $pref = Preference::create(['title' => $data['title']]);
            return $this->success($pref->toArray(), 'Preference created successfully');
        });
    }

    /**
     * Update a preference
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update(int $id, array $data): array
    {
        return $this->executeWithTransaction(function () use ($id, $data) {
            $pref = Preference::findOrFail($id);
            $pref->update(['title' => $data['title']]);
            return $this->success($pref->fresh()->toArray(), 'Preference updated successfully');
        });
    }

    /**
     * Delete a preference
     *
     * @param int $id
     * @return array
     */
    public function delete(int $id): array
    {
        return $this->executeWithTransaction(function () use ($id) {
            $pref = Preference::findOrFail($id);
            $pref->delete();
            return $this->success(null, 'Preference deleted successfully');
        });
    }
}
