<?php

namespace Modules\Profile\Repositories;

use Modules\Profile\Contracts\Repositories\DocumentTypeRepositoryInterface;
use Modules\Profile\Models\DocumentType;
use Illuminate\Support\Facades\DB;

class DocumentTypeRepository implements DocumentTypeRepositoryInterface
{
    /**
     * Get all document types
     *
     * @param bool $activeOnly
     * @return array
     */
    public function getAll(bool $activeOnly = false): array
    {
        $query = DB::table('document_types')
            ->orderBy('sort_order', 'asc')
            ->orderBy('title', 'asc');

        if ($activeOnly) {
            $query->where('active', true);
        }

        $results = $query->get();

        return $results->map(function ($row) {
            return $this->mapToArray($row);
        })->toArray();
    }

    /**
     * Find document type by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array
    {
        $row = DB::table('document_types')
            ->where('id', $id)
            ->first();

        return $row ? $this->mapToArray($row) : null;
    }

    /**
     * Find document type by key
     *
     * @param string $key
     * @return array|null
     */
    public function findByKey(string $key): ?array
    {
        $row = DB::table('document_types')
            ->where('key', $key)
            ->first();

        return $row ? $this->mapToArray($row) : null;
    }

    /**
     * Create new document type
     *
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        // Convert allowed_mime to JSON if it's an array
        if (isset($data['allowed_mime']) && is_array($data['allowed_mime'])) {
            $data['allowed_mime'] = json_encode($data['allowed_mime']);
        }

        $id = DB::table('document_types')->insertGetId($data);

        return $this->find($id);
    }

    /**
     * Update document type
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update(int $id, array $data): array
    {
        // Convert allowed_mime to JSON if it's an array
        if (isset($data['allowed_mime']) && is_array($data['allowed_mime'])) {
            $data['allowed_mime'] = json_encode($data['allowed_mime']);
        }

        $data['updated_at'] = now();

        DB::table('document_types')
            ->where('id', $id)
            ->update($data);

        return $this->find($id);
    }

    /**
     * Delete document type
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return DB::table('document_types')
            ->where('id', $id)
            ->delete() > 0;
    }

    /**
     * Get active document types
     *
     * @return array
     */
    public function getActive(): array
    {
        return $this->getAll(true);
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
            'key' => $row->key,
            'title' => $row->title,
            'icon' => $row->icon,
            'description' => $row->description,
            'both_sided' => (bool) $row->both_sided,
            'both_sided_required' => (bool) $row->both_sided_required,
            'front_side_title' => $row->front_side_title,
            'back_side_title' => $row->back_side_title,
            'allowed_mime' => json_decode($row->allowed_mime ?? '[]', true),
            'max_size_kb' => $row->max_size_kb,
            'active' => (bool) $row->active,
            'is_required' => (bool) $row->is_required,
            'sort_order' => $row->sort_order,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ];
    }
}
