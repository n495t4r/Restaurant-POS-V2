<?php

namespace App\Services;

use App\Models\ChowDeck\MenuItem;
use App\Models\Chowdeck\ChowDeckOrder;
use App\Models\Chowdeck\OrderItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;

class ChowdeckService
{
    protected $baseUrl = 'https://api.chowdeck.com';
    protected $apiKey;
    protected $merchantReference;

    public function __construct()
    {
        $this->apiKey = config('services.chowdeck.api_key');
        $this->merchantReference = config('services.chowdeck.merchant_reference');
    }

    protected function client(): PendingRequest
    {
        return Http::withToken($this->apiKey)
            ->withOptions([
                'verify' => false,
            ]);
    }

    public function getMenuItems(): Collection
    {
        $response = $this->client()
            ->get("{$this->baseUrl}/merchant/{$this->merchantReference}/menu");

        if ($response->successful()) {
            $menuItems = $response->json()['data'];
            // dd($menuItems);
            return Collection::make($menuItems)->map(function ($item) {
                return new MenuItem([
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'in_stock' => $item['in_stock'],
                    'is_published' => $item['is_published'],
                    'reference' => $item['reference'],
                    'category' => $item['category']['name'],
                    'image' => $item['images'][0]['path'] ?? null,
                ]);
            });
        }

        throw new \Exception('Failed to fetch menu items from Chowdeck: ' . $response->body());
    }

    public function updateMenuItem(string $reference, array $data): MenuItem
    {
        $response = $this->client()
            ->put("{$this->baseUrl}/merchant/{$this->merchantReference}/menu/{$reference}", $data);

        if ($response->successful()) {
            $updatedItem = $response->json()['data'];
            return new MenuItem([
                'id' => $updatedItem['id'],
                'name' => $updatedItem['name'],
                'description' => $updatedItem['description'],
                'price' => $updatedItem['price'],
                'in_stock' => $updatedItem['in_stock'],
                'is_published' => $updatedItem['is_published'],
                'category' => $updatedItem['category']['name'],
                'image' => $updatedItem['images'][0]['path'] ?? null,
                'reference' => $updatedItem['reference'],
                'menu_category_id' => $updatedItem['category']['id'],
                'price_description' => $updatedItem['price_description'],
                'rank' => $updatedItem['rank'],
            ]);
        }

        throw new \Exception('Failed to update menu item: ' . $response->body());
    }

    public function getOrder(string $reference): OrderItem
    {
        $response = $this->client()
            ->get("{$this->baseUrl}/merchant/{$this->merchantReference}/order/{$reference}");

        if ($response->successful()) {
            $orderData = $response->json()['data'];
            return new OrderItem($orderData);
        }

        throw new \Exception('Failed to fetch order item: ' . $response->body());
    }

    public function getOrders(): Collection
    {
        $response = $this->client()
            ->get("{$this->baseUrl}/merchant/{$this->merchantReference}/order");

        if ($response->successful()) {
            $ordersData = $response->json()['data'];
            // dd($ordersData);
            return Collection::make($ordersData)->map(function ($orderData) {
                return new ChowDeckOrder([
                    'id' => $orderData['id'],
                    'reference' => $orderData['reference'],
                    'total_price' => $orderData['total_price'],
                    'status' => $orderData['status'],
                    'customer_address' => $orderData['customer_address'],
                    'created_at' => $orderData['created_at'],
                    'updated_at' => $orderData['updated_at']
                ]);
            });
        }

        throw new \Exception('Failed to fetch order: ' . $response->body());
    }
}
