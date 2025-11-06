<?php

namespace App\Shared\Contracts\Repositories;

use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Ultra-Minimal Base Repository Interface
 * 
 * Contains ONLY what EVERY repository will absolutely need.
 * Each repository interface should extend this and define only their specific methods.
 */
interface BaseRepositoryInterface
{
    /**
     * Get a new query builder instance for raw DB queries
     * This is the only method every repository will definitely need
     */
    public function query(): QueryBuilder;
}
