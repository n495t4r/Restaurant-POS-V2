<?php

namespace App\Models\Chowdeck;

use App\Services\ChowdeckService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChowDeckOrder extends Model
{
    protected $fillable = [
        'id',
        'total_price',
        'status',
        'customer_address',
        'reference',
        'created_at',
        'updated_at'
    ];

    // protected $casts = [

    // ];
    public function order()
{
    return $this->belongsTo(ChowDeckOrder::class, 'id');
}

    public function newEloquentBuilder($query)
    {
        return new class($query) extends Builder {
            protected $items;

            protected function getItems()
            {
                if (!$this->items) {
                    $service = app(ChowdeckService::class);
                    $this->items = collect($service->getOrders());
                }
                return $this->items;
            }

            public function get($columns = ['*'])
            {
                return $this->getItems();
            }

            public function find($id, $columns = ['*'])
        {
            return $this->getItems()->first(function ($item) use ($id) {
                return $item['id'] == $id;
            });
        }

        public function findOrFail($id, $columns = ['*'])
        {
            $result = $this->find($id, $columns);
            if (!$result) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException;
            }
            return $result;
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
