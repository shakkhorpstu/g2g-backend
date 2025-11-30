<?php

namespace Modules\Profile\Services;

use App\Shared\Services\BaseService;
use Modules\Profile\Contracts\Repositories\ProfileLanguageRepositoryInterface;

class ProfileLanguageService extends BaseService
{
    /**
     * Allowed guards for language management
     */
    protected array $allowedGuards = ['api', 'psw-api'];

    /**
     * ProfileLanguageRepository instance
     */
    protected ProfileLanguageRepositoryInterface $languageRepository;

    /**
     * ProfileLanguageService constructor
     */
    public function __construct(ProfileLanguageRepositoryInterface $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * Get language for authenticated user
     */
    public function getLanguage(): array
    {
        return $this->execute(function () {
            $user = $this->getAuthenticatedUserOrFail($this->allowedGuards);
            $languages = $this->languageRepository->getForOwner($user);

            $all = config('languages', []);

            if (empty($languages)) {
                return $this->success([], 'No language preferences found');
            }

            $mapped = array_map(function ($l) use ($all) {
                $code = $l->language;
                return [
                    'value' => $code,
                    'title' => $all[$code]['title'] ?? $code,
                ];
            }, $languages);

            return $this->success($mapped, 'Languages retrieved successfully');
        });
    }

    /**
     * Set or update language for authenticated user
     */
    public function setLanguage(array $languages): array
    {
        return $this->executeWithTransaction(function () use ($languages) {
            $user = $this->getAuthenticatedUserOrFail($this->allowedGuards);
            // only accept codes that exist in config/languages.php
            $available = array_keys(config('languages', []));
            $filtered = array_values(array_unique(array_filter($languages, fn($v) => in_array($v, $available, true))));

            $result = $this->languageRepository->syncForOwner($user, $filtered);

            $all = config('languages', []);
            $mapped = array_map(function ($l) use ($all) {
                $code = $l->language;
                return [
                    'value' => $code,
                    'title' => $all[$code]['title'] ?? $code,
                ];
            }, $result);

            return $this->success($mapped, 'Languages updated successfully');
        });
    }
}
