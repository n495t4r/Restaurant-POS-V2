<?php

namespace App\Models\ChowDeck;

use App\Services\ChowdeckService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MenuItem extends Model
{
    protected $fillable = [
        'id',
        'name',
        'description',
        'price',
        'in_stock',
        'is_published',
        'category',
        'image',
        'reference',
        'menu_category_id',
        'price_description',
        'rank',
    ];

    public function update(array $attributes = [], array $options = [])
    {
        $service = app(ChowdeckService::class);
        return $service->updateMenuItem($this->reference, $attributes);
    }

    public function newEloquentBuilder($query)
    {
        return new class($query) extends Builder {
            protected $items;

            public function get($columns = ['*'])
            {
                if (!$this->items) {
                    $service = app(ChowdeckService::class);
                    $this->items = $service->getMenuItems();
                }
                return $this->items;
            }

            public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
            {
                $items = $this->get($columns);
                $page = $page ?: \Illuminate\Pagination\Paginator::resolveCurrentPage($pageName);

                $total = $items->count();

                $results = $items->forPage($page, $perPage);

                return new \Illuminate\Pagination\LengthAwarePaginator($results, $total, $perPage, $page, [
                    'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]);
            }
        };
    }
}