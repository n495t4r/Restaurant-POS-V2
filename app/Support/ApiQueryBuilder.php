<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class ApiQueryBuilder
{
    protected Collection $items;
    protected array $columns = ['*'];
    protected ?string $orderBy = null;
    protected string $orderDirection = 'asc';

    public function __construct(Collection $items)
    {
        $this->items = ($items);
    }

    public function get()
    {
        return $this->items;
    }

    public function paginate($perPage, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $results = $this->forPage($page, $perPage)->get($columns);

        $total = $this->count();

        return new Paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    public function forPage($page, $perPage)
    {
        $offset = max(0, ($page - 1) * $perPage);
        
        $this->items = $this->items->slice($offset, $perPage);

        return $this;
    }

    public function count()
    {
        return $this->items->count();
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->orderBy = $column;
        $this->orderDirection = $direction;

        $this->items = $this->items->sortBy($column, SORT_REGULAR, $direction === 'desc');

        return $this;
    }

    public function where($column, $operator, $value = null)
    {
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->items = $this->items->filter(function ($item) use ($column, $operator, $value) {
            $columnValue = data_get($item, $column);

            switch ($operator) {
                case '=':
                    return $columnValue == $value;
                case '!=':
                    return $columnValue != $value;
                case '>':
                    return $columnValue > $value;
                case '<':
                    return $columnValue < $value;
                case '>=':
                    return $columnValue >= $value;
                case '<=':
                    return $columnValue <= $value;
                case 'like':
                    return stripos($columnValue, str_replace('%', '', $value)) !== false;
            }

            return false;
        });

        return $this;
    }
}