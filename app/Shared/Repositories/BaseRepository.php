<?php

namespace App\Shared\Repositories;

use App\Shared\Contracts\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 * Base Repository Implementation
 * 
 * Provides common CRUD operations that all module repositories can extend.
 * Each module repository should extend this class and implement their specific interface.
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct()
    {
        $this->model = $this->getModel();
    }

    /**
     * Get the model instance - must be implemented by child classes
     */
    abstract protected function getModel(): Model;

    /**
     * Get all records
     */
    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        try {
            $query = $this->model->newQuery();
            
            if (!empty($relations)) {
                $query->with($relations);
            }
            
            return $query->get($columns);
            
        } catch (\Throwable $exception) {
            Log::error('Repository All Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        try {
            $query = $this->model->newQuery();
            
            if (!empty($relations)) {
                $query->with($relations);
            }
            
            return $query->paginate($perPage, $columns);
            
        } catch (\Throwable $exception) {
            Log::error('Repository Paginate Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Find by ID
     */
    public function findById(int $id, array $columns = ['*'], array $relations = []): ?Model
    {
        try {
            $query = $this->model->newQuery();
            
            if (!empty($relations)) {
                $query->with($relations);
            }
            
            return $query->find($id, $columns);
            
        } catch (\Throwable $exception) {
            Log::error('Repository FindById Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'id' => $id,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Find by ID or fail
     */
    public function findByIdOrFail(int $id, array $columns = ['*'], array $relations = []): Model
    {
        try {
            $query = $this->model->newQuery();
            
            if (!empty($relations)) {
                $query->with($relations);
            }
            
            return $query->findOrFail($id, $columns);
            
        } catch (ModelNotFoundException $exception) {
            Log::warning('Repository FindByIdOrFail - Model Not Found', [
                'repository' => static::class,
                'id' => $id,
            ]);
            
            throw $exception;
            
        } catch (\Throwable $exception) {
            Log::error('Repository FindByIdOrFail Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'id' => $id,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Find by specific criteria
     */
    public function findBy(array $criteria, array $columns = ['*'], array $relations = []): Collection
    {
        try {
            $query = $this->model->newQuery();
            
            if (!empty($relations)) {
                $query->with($relations);
            }
            
            foreach ($criteria as $field => $value) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
            
            return $query->get($columns);
            
        } catch (\Throwable $exception) {
            Log::error('Repository FindBy Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'criteria' => $criteria,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Find first by criteria
     */
    public function findFirstBy(array $criteria, array $columns = ['*'], array $relations = []): ?Model
    {
        try {
            $query = $this->model->newQuery();
            
            if (!empty($relations)) {
                $query->with($relations);
            }
            
            foreach ($criteria as $field => $value) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
            
            return $query->first($columns);
            
        } catch (\Throwable $exception) {
            Log::error('Repository FindFirstBy Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'criteria' => $criteria,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Create new record
     */
    public function create(array $data): Model
    {
        try {
            return $this->model->create($data);
            
        } catch (\Throwable $exception) {
            Log::error('Repository Create Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'data' => $data,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Update record by ID
     */
    public function updateById(int $id, array $data): bool
    {
        try {
            $model = $this->findByIdOrFail($id);
            
            return $model->update($data);
            
        } catch (\Throwable $exception) {
            Log::error('Repository UpdateById Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'id' => $id,
                'data' => $data,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Update model instance
     */
    public function update(Model $model, array $data): bool
    {
        try {
            return $model->update($data);
            
        } catch (\Throwable $exception) {
            Log::error('Repository Update Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'model_id' => $model->id,
                'data' => $data,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Delete record by ID
     */
    public function deleteById(int $id): bool
    {
        try {
            $model = $this->findByIdOrFail($id);
            
            return $model->delete();
            
        } catch (\Throwable $exception) {
            Log::error('Repository DeleteById Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'id' => $id,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Delete model instance
     */
    public function delete(Model $model): bool
    {
        try {
            return $model->delete();
            
        } catch (\Throwable $exception) {
            Log::error('Repository Delete Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'model_id' => $model->id,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Count records with criteria
     */
    public function count(array $criteria = []): int
    {
        try {
            $query = $this->model->newQuery();
            
            foreach ($criteria as $field => $value) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
            
            return $query->count();
            
        } catch (\Throwable $exception) {
            Log::error('Repository Count Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'criteria' => $criteria,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Check if record exists
     */
    public function exists(array $criteria): bool
    {
        try {
            $query = $this->model->newQuery();
            
            foreach ($criteria as $field => $value) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
            
            return $query->exists();
            
        } catch (\Throwable $exception) {
            Log::error('Repository Exists Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'criteria' => $criteria,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Get fresh model instance
     */
    public function fresh(Model $model): ?Model
    {
        try {
            return $model->fresh();
            
        } catch (\Throwable $exception) {
            Log::error('Repository Fresh Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'model_id' => $model->id,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Get new query builder instance
     */
    public function newQuery(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Get model instance
     */
    public function getModelInstance(): Model
    {
        return $this->model;
    }
}