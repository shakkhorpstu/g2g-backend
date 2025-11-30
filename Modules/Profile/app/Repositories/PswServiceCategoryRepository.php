<?php

namespace Modules\Profile\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Profile\Contracts\Repositories\PswServiceCategoryRepositoryInterface;
use Carbon\Carbon;

class PswServiceCategoryRepository implements PswServiceCategoryRepositoryInterface
{
    protected string $table = 'psw_service_categories';
    protected string $serviceTable = 'service_categories';

    public function listByPswId(int $pswId): array
    {
        $rows = DB::table("{$this->table} as psc")
            ->join("{$this->serviceTable} as sc", 'sc.id', '=', 'psc.service_category_id')
            ->where('psc.psw_id', $pswId)
            ->select('sc.id', 'sc.title', 'sc.subtitle')
            ->orderBy('sc.title')
            ->get();

        return $rows->map(fn($r) => (array) $r)->toArray();
    }

    public function getIdsByProfileId(int $pswProfileId): array
    {
        $ids = DB::table($this->table)
            ->where('psw_profile_id', $pswProfileId)
            ->pluck('service_category_id')
            ->map(fn($v) => (int) $v)
            ->toArray();

        return $ids;
    }

    public function syncForPsw(int $pswId, int $pswProfileId, array $categoryIds): array
    {
        return DB::transaction(function () use ($pswId, $pswProfileId, $categoryIds) {
            $categoryIds = array_values(array_unique(array_map('intval', $categoryIds)));

            $existing = DB::table($this->table)
                ->where('psw_profile_id', $pswProfileId)
                ->pluck('service_category_id')
                ->map(fn($v) => (int) $v)
                ->toArray();

            $toInsert = array_values(array_diff($categoryIds, $existing));
            $toDelete = array_values(array_diff($existing, $categoryIds));

            if (!empty($toDelete)) {
                DB::table($this->table)
                    ->where('psw_profile_id', $pswProfileId)
                    ->whereIn('service_category_id', $toDelete)
                    ->delete();
            }

            if (!empty($toInsert)) {
                $now = Carbon::now();
                $inserts = [];
                foreach ($toInsert as $catId) {
                    $inserts[] = [
                        'psw_profile_id' => $pswProfileId,
                        'psw_id' => $pswId,
                        'service_category_id' => $catId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                DB::table($this->table)->insert($inserts);
            }

            return $this->listByPswId($pswId);
        });
    }
}
