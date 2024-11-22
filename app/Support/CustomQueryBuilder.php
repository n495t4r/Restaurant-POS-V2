<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CustomQueryBuilder extends Builder
{
    protected Collection $items;

    public function __construct(Collection $items)
    {
        parent::__construct(new class extends Model {});
        $this->items = $items;
    }

    public function get($columns = ['*'])
    {
        return $this->items;
    }

    public function paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

        $results = $this->forPage($page, $perPage)->get($columns);

        return new LengthAwarePaginator($results, $this->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
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

    protected function compareValues($a, $b, $operator)
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

    public function count($columns = '*')
    {
        return $this->items->count();
    }

    public function forPage($page, $perPage)
    {
        $offset = max(0, ($page - 1) * $perPage);
        $this->items = $this->items->slice($offset, $perPage);
        return $this;
    }
}