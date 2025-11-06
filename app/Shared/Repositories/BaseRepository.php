<?php

namespace App\Shared\Repositories;

use App\Shared\Contracts\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Ultra-Minimal Base Repository
 * 
 * Provides ONLY the essential foundation that EVERY repository needs.
 * Each repository implements only the specific methods it actually uses.
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;
    protected string $table;

    public function __construct()
    {
        $this->model = $this->getModel();
        $this->table = $this->model->getTable();
    }

    /**
     * Get the model instance - must be implemented by child classes
     */
    abstract protected function getModel(): Model;

    /**
     * Get a new query builder instance for raw DB queries
     * This is the only method guaranteed to be in the interface
     */
    public function query(): QueryBuilder
    {
        return DB::table($this->table);
    }

    /**
     * Get a new Eloquent query builder instance (when needed)
     * Helper method available to all repositories
     */
    protected function newQuery()
    {
        return $this->model->newQuery();
    }

    /**
     * Get the table name - helper method
     */
    protected function getTableName(): string
    {
        return $this->table;
    }
}
