<?php

namespace Modules\Core\Services;

use App\Shared\Services\BaseService;

class ResourceService extends BaseService
{
    /**
     * Return languages list from root config
     */
    public function getLanguages(): array
    {
        return $this->execute(function () {
            $languages = config('languages', []);

            // Normalize to a numeric list of {value, title} objects
            $mapped = array_values(array_map(function ($item) {
                if (is_array($item) && isset($item['value'], $item['title'])) {
                    return [
                        'value' => $item['value'],
                        'title' => $item['title'],
                    ];
                }

                // Fallback: if config stored as 'code' => 'Title'
                if (is_string($item)) {
                    return [
                        'value' => null,
                        'title' => $item,
                    ];
                }

                return $item;
            }, $languages));

            return $this->success($mapped, 'Languages retrieved successfully');
        });
    }
}
