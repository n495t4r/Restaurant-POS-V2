<?php

// app/Http/Controllers/Api/ProductController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;


class ProductApiController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $category = $request->input('product_category');
        $status = $request->input('status');
        $search = $request->input('search');

        $cacheKey = "products:{$perPage}:{$category}:{$status}:{$search}:" . $request->page;

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($perPage, $category, $status, $search) {
            $query = Product::with('product_category')
                ->when($category, fn($q) => $q->where('product_category_id', $category))
                ->when($status, fn($q) => $q->where('status', $status))
                ->when($search, function($q) use ($search) {
                    $q->where(function($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                });

            return ProductResource::collection($query->paginate($perPage));
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'product_category_id' => 'required|exists:product_categories,id',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|max:2048'
        ]);

        try {
            DB::beginTransaction();

            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($validated);
            
            DB::commit();
            Cache::tags(['products'])->flush();

            return new ProductResource($product);
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($validated['image'])) {
                Storage::disk('public')->delete($validated['image']);
            }
            
            return response()->json([
                'message' => 'Error creating product',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
