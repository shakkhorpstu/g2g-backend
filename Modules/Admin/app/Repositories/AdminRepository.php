<?php

namespace Modules\Admin\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Admin\Contracts\Repositories\AdminRepositoryInterface;

class AdminRepository implements AdminRepositoryInterface
{
    protected string $table = 'admins';

    /**
     * Get paginated admins list
     *
     * @param int $perPage
     * @param string|null $search
     * @return array
     */
    public function paginate(int $perPage = 15, ?string $search = null): array
    {
        $query = DB::table($this->table)
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage);

        return [
            'users' => collect($paginator->items())->map(fn($row) => $this->mapToArray($row))->toArray(),
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    /**
     * Find admin by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array
    {
        $row = DB::table($this->table)->where('id', $id)->first();
        return $row ? $this->mapToArray($row) : null;
    }

    /**
     * Find admin by email
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        $row = DB::table($this->table)->where('email', $email)->first();
        return $row ? $this->mapToArray($row) : null;
    }

    /**
     * Create new admin
     *
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        $now = now();
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        $data['status'] = $data['status'] ?? 'active';

        $id = DB::table($this->table)->insertGetId($data);

        return $this->find($id);
    }

    /**
     * Update admin
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update(int $id, array $data): array
    {
        $data['updated_at'] = now();
        
        DB::table($this->table)
            ->where('id', $id)
            ->update($data);
            
        return $this->find($id);
    }

    /**
     * Delete admin
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return DB::table($this->table)->where('id', $id)->delete() > 0;
    }

    /**
     * Map database row to array
     *
     * @param object $row
     * @return array
     */
    protected function mapToArray(object $row): array
    {
        return [
            'id' => $row->id,
            'name' => $row->name,
            'email' => $row->email,
            'status' => $row->status ?? 'active',
            'last_login_at' => $row->last_login_at ?? null,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ];
    }
}
