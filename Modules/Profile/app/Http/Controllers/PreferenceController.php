<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Profile\Models\Preference;
use Modules\Profile\Services\Admin\PreferenceService as AdminPreferenceService;

class PreferenceController extends ApiController
{
    protected AdminPreferenceService $preferenceService;

    public function __construct(AdminPreferenceService $preferenceService)
    {
        parent::__construct();
        $this->preferenceService = $preferenceService;
    }
    /**
     * Public: list all preferences
     */
    public function index(): JsonResponse
    {
        return $this->executeService(fn() => $this->preferenceService->getAll(), 'Preferences retrieved');
    }

    /**
     * Admin: create preference
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, ['title' => 'required|string|max:191']);

        $data = ['title' => $request->input('title')];
        return $this->executeServiceForCreation(fn() => $this->preferenceService->create($data));
    }

    /**
     * Admin: update preference
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->validate($request, ['title' => 'required|string|max:191']);

        $data = ['title' => $request->input('title')];
        return $this->executeService(fn() => $this->preferenceService->update($id, $data));
    }

    /**
     * Admin: delete preference
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->executeService(fn() => $this->preferenceService->delete($id), 'Preference deleted');
    }
}
