<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        // Fetch products from the database
        // $products = Product::all();
        $products = Product::where('status',1)->paginate(10);
        $customers = Customer::all();

        if ($request->ajax()) {

            if($request->input('query')){
                $query = $request->input('query');
                $products = Product::where('name', 'like', "%$query%")->take(6)->get()
                ->where('status', 1);
                return view('products.partials.search_results', compact('products'));
            }
            return response()->json(view('products.partials.products', compact('products'))->render());
        }

        // Pass products and customers data to the view
        return view('filament.pages.cart', [
            'products' => $products,
            'customers' => $customers,
        ]);

    }

   
}
