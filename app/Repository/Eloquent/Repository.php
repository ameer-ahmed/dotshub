<?php

namespace App\Repository\Eloquent;

use App\Repository\Contracts\RepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class Repository implements RepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getAll(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }

    public function getActive(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->where('is_active', true)->get($columns);
    }

    public function getById(
        $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): ?Model {
        return $this->model->select($columns)->with($relations)->findOrFail($modelId)->append($appends);
    }

    public function get(
        $byColumn,
        $value,
        array $columns = ['*'],
        array $relations = [],
    ): array|Collection {
        return $this->model::query()->select($columns)->with($relations)->where($byColumn, $value)->get();
    }

    public function first(
        $byColumn,
        $value,
        array $columns = ['*'],
        array $relations = [],
    ): Builder|Model|null {
        return $this->model::query()->select($columns)->with($relations)->where($byColumn, $value)->first();
    }

    public function getFirst(): ?Model
    {
        return $this->model->first();
    }

    public function create(array $payload): ?Model
    {
        $model = $this->model->create($payload);

        return $model->fresh();
    }

    public function insert(array $payload): bool
    {
        $model = $this->model::query()->insert($payload);

        return $model;
    }

    public function createMany(array $payload): bool
    {
        try {
            foreach ($payload as $record) {
                $this->model::query()->create($record);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function update($modelId, array $payload): ?Model
    {
        $model = $this->getById($modelId);

        return $model->update($payload)
            ? $model->refresh()
            : null;
    }

    public function delete($modelId): bool
    {
        $model = $this->getById($modelId);

        return $model->delete();
    }

    public function forceDelete($modelId): bool
    {
        $model = $this->getById($modelId);

        return $model->forceDelete();
    }

    public function paginate(int $perPage = 10, array $relations = [], $orderBy = 'ASC', $columns = ['*'])
    {
        return $this->model::query()->select($columns)->with($relations)->orderBy('id', $orderBy)->paginate($perPage);
    }

    public function paginateWithQuery(
        $query,
        int $perPage = 10,
        array $relations = [],
        $orderBy = 'ASC',
        $columns = ['*'],
    ) {
        return  $this->model::query()->select($columns)->where($query)->with($relations)->orderBy('id', $orderBy)->paginate($perPage);
    }


    public function whereHasMorph($relation, $class)
    {
        return $this->model::query()->whereHasMorph($relation, $class)->get();
    }

    /**
     * Apply model scopes to the query with optional parameters
     *
     * @param array $scopes Array of scopes. Can be:
     *                      - Simple array: ['scopeName1', 'scopeName2']
     *                      - Associative array with parameters: ['scopeName' => $param] or ['scopeName' => [$param1, $param2]]
     * @param array $columns Columns to select
     * @param bool $paginate Whether to paginate results
     * @param int $perPage Items per page (only used if $paginate is true)
     * @param array $relations Relations to eager load
     * @param string $orderBy Order direction (ASC or DESC)
     * @param string $orderColumn Column to order by
     * @return Collection|array|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @example
     * // Without parameters, no pagination
     * $repository->filter(['active', 'verified']);
     *
     * // With single parameter
     * $repository->filter(['status' => 'active', 'role' => 'admin']);
     *
     * // With multiple parameters
     * $repository->filter(['createdBetween' => ['2024-01-01', '2024-12-31']]);
     *
     * // Mixed with pagination
     * $repository->filter(
     *     ['active', 'status' => 'pending', 'type' => 'premium'],
     *     ['*'],
     *     true,
     *     15,
     *     ['profile'],
     *     'DESC',
     *     'created_at'
     * );
     */
    public function filter(
        array $scopes,
        array $columns = ['*'],
        bool $paginate = false,
        int $perPage = 10,
        array $relations = [],
        string $orderBy = 'ASC',
        string $orderColumn = 'id'
    ) {
        $query = $this->model::query()->select($columns)->with($relations);

        foreach ($scopes as $scope => $parameters) {
            // If scope is numeric, it means no parameters (e.g., ['active', 'verified'])
            if (is_numeric($scope)) {
                $scopeName = $parameters;
                $query = $query->{$scopeName}();
            } else {
                // Scope has parameters
                $scopeName = $scope;
                // Ensure parameters is an array
                $params = is_array($parameters) ? $parameters : [$parameters];
                $query = $query->{$scopeName}(...$params);
            }
        }

        // Apply ordering
        $query = $query->orderBy($orderColumn, $orderBy);

        // Return paginated or all results based on the $paginate parameter
        return $paginate ? $query->paginate($perPage) : $query->get();
    }
}
