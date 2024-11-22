<?php

namespace App\Repositories;

use App\Services\ChowdeckService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class ChowDeckMenuItemRepository
{
    protected $chowdeckService;

    public function __construct(ChowdeckService $chowdeckService)
    {
        $this->chowdeckService = $chowdeckService;
    }

    public function query()
    {
        return new class($this) {
            protected $repository;
            protected $filters = [];
            protected $sorts = [];

            public function __construct($repository)
            {
                $this->repository = $repository;
            }

            public function where($column, $operator, $value = null)
            {
                $this->filters[] = [$column, $operator, $value];
                return $this;
            }

            public function orderBy($column, $direction = 'asc')
            {
                $this->sorts[] = [$column, $direction];
                return $this;
            }

            public function paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
            {
                $items = $this->repository->getFilteredAndSortedItems($this->filters, $this->sorts);
                $page = $page ?: Paginator::resolveCurrentPage($pageName);
                
                $total = $items->count();
                $items = $items->slice(($page - 1) * $perPage, $perPage);
                
                return new Paginator($items, $total, $perPage, $page, [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]);
            }
        };
    }

    public function getFilteredAndSortedItems($filters, $sorts)
    {
        $items = $this->chowdeckService->getMenuItems();

        // Apply filters
        foreach ($filters as $filter) {
            [$column, $operator, $value] = $filter;
            $items = $items->filter(function ($item) use ($column, $operator, $value) {
                switch ($operator) {
                    case '=':
                        return $item->$column == $value;
                    case 'like':
                        return stripos($item->$column, str_replace('%', '', $value)) !== false;
                    // Add more operators as needed
                }
            });
        }

        // Apply sorts
        foreach ($sorts as $sort) {
            [$column, $direction] = $sort;
            $items = $items->sortBy($column, SORT_REGULAR, $direction === 'desc');
        }

        return $items;
    }
}
