<?php

namespace App\Repository\Eloquent;

use App\Enums\QueryReturnType;
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

    /**
     * Generic query builder with optional scopes and flexible return types
     *
     * @param array $scopes Array of scopes (optional). Can be:
     *                      - Simple array: ['scopeName1', 'scopeName2']
     *                      - Associative array with parameters: ['scopeName' => $param] or ['scopeName' => [$param1, $param2]]
     * @param array $columns Columns to select
     * @param array $relations Relations to eager load
     * @param QueryReturnType $returnType Return type (GET, FIRST, PAGINATE, or QUERY)
     * @param int $perPage Items per page (only used if $returnType is PAGINATE)
     * @param string $orderBy Order direction (ASC or DESC)
     * @param string $orderColumn Column to order by
     * @return Collection|Model|Builder|\Illuminate\Contracts\Pagination\LengthAwarePaginator|null
     *
     * @example
     * // Get all with scopes
     * $repository->query(['active', 'verified']);
     *
     * // Get with parameters
     * $repository->query(['status' => 'active', 'role' => 'admin']);
     *
     * // Get first result
     * $repository->query(['active'], returnType: QueryReturnType::FIRST);
     *
     * // Get paginated results
     * $repository->query(
     *     scopes: ['active', 'status' => 'pending'],
     *     columns: ['*'],
     *     relations: ['profile'],
     *     returnType: QueryReturnType::PAGINATE,
     *     perPage: 15,
     *     orderBy: 'DESC',
     *     orderColumn: 'created_at'
     * );
     *
     * // Get query builder for further customization
     * $query = $repository->query(['active'], returnType: QueryReturnType::QUERY);
     * $results = $query->where('created_at', '>', now()->subDays(7))->get();
     *
     * // No scopes, just get all
     * $repository->query();
     */
    public function query(
        array $scopes = [],
        array $columns = ['*'],
        array $relations = [],
        QueryReturnType $returnType = QueryReturnType::GET,
        int $perPage = 10,
        string $orderBy = 'ASC',
        string $orderColumn = 'id'
    ) {
        $query = $this->model::query()->select($columns)->with($relations);

        // Apply scopes if provided
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

        // Return based on the return type using match
        return match ($returnType) {
            QueryReturnType::GET => $query->get(),
            QueryReturnType::FIRST => $query->first(),
            QueryReturnType::PAGINATE => $query->paginate($perPage),
            QueryReturnType::QUERY => $query,
        };
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
}
