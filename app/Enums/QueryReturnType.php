<?php

namespace App\Enums;

use App\Traits\Enumable;

enum QueryReturnType: string
{
    use Enumable;

    case GET = 'get';
    case FIRST = 'first';
    case PAGINATE = 'paginate';
    case QUERY = 'query';

    /**
     * Check if the return type requires pagination
     */
    public function requiresPagination(): bool
    {
        return $this === self::PAGINATE;
    }

    /**
     * Check if the return type returns a single record
     */
    public function returnsSingle(): bool
    {
        return $this === self::FIRST;
    }

    /**
     * Check if the return type returns a collection
     */
    public function returnsCollection(): bool
    {
        return $this === self::GET;
    }

    /**
     * Check if the return type returns a query builder
     */
    public function returnsQuery(): bool
    {
        return $this === self::QUERY;
    }
}