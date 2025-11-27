<?php

namespace App\Repository\Contracts;

use App\Enums\QueryReturnType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    public function query(
        array $scopes = [],
        array $columns = ['*'],
        array $relations = [],
        QueryReturnType $returnType = QueryReturnType::GET,
        int $perPage = 10,
        string $orderBy = 'ASC',
        string $orderColumn = 'id'
    );

    public function getAll(array $columns = ['*'], array $relations = []): Collection;

    public function getActive(array $columns = ['*'], array $relations = []): Collection;

    public function getById(
        $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): ?Model;

    public function get(
        $byColumn,
        $value,
        array $columns = ['*'],
        array $relations = [],
    ): array|Collection;

    public function first(
        $byColumn,
        $value,
        array $columns = ['*'],
        array $relations = [],
    ): Builder|Model|null;

    public function create(array $payload): ?Model;

    public function insert(array $payload): bool;

    public function getFirst(): ?Model;

    public function update($modelId, array $payload): ?Model;

    public function delete($modelId): bool;

    public function forceDelete($modelId);

    public function paginate(int $perPage = 10, array $relations = [], $orderBy = 'ASC', $columns = ['*']);

    public function paginateWithQuery(
        $query,
        int $perPage = 10,
        array $relations = [],
        $orderBy = 'ASC',
        $columns = ['*'],
    );

    public function whereHasMorph($relation, $class);
}
