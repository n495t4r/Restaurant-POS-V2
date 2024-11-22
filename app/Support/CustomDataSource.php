<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CustomDataSource
{
    protected Collection $items;

    public function __construct(Collection $items)
    {
        $this->items = $items;
    }

    public function get(): Collection
    {
        return $this->items;
    }

    public function paginate(int $perPage): Paginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();

        $results = $this->items->forPage($page, $perPage);

        return new LengthAwarePaginator($results, $this->items->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);
    }

    public function where($column, $operator = null, $value = null): self
    {
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->items = $this->items->filter(function ($item) use ($column, $operator, $value) {
            return $this->compareValues($item[$column] ?? null, $value, $operator);
        });

        return $this;
    }

    protected function compareValues($a, $b, $operator): bool
    {
        switch ($operator) {
            case '=':
                return $a == $b;
            case '!=':
                return $a != $b;
            case '>':
                return $a > $b;
            case '>=':
                return $a >= $b;
            case '<':
                return $a < $b;
            case '<=':
                return $a <= $b;
            case 'like':
                return false !== stripos($a, str_replace('%', '', $b));
            default:
                return false;
        }
    }
}