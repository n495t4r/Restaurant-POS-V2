<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class PrintController extends Controller
{

    public function printInvoice($id)
    {
        // return 'Hi there';

        return $this->orderInvoice($id);


        if ($request->input('page') == 'cart') {
            // return response()->json(['html' => 'Found '.$request->input('page')]);
            return $this->cartInvoice($request);
        }else{
            return $this->orderInvoice($request);
            // return response()->json(['html' => 'Not Found '.$request->input('page')]);
        }
    }

    private function cartInvoice(Request $request)
    {
        // Retrieve necessary data from the request
        $customerId = $request->input('customerId');
        $paymentMethods = (array) $request->input('paymentMethods'); // Convert to array
        $commentForCook = $request->input('commentForCook');
        $amount = $request->input('amount');

        // Retrieve cart data as needed
        // Initialize an empty array to store item details
        $cartData = [];

        // Loop through the cart items and associate them with the order
        foreach ($request->input('cart') as $item) {
            // Calculate the total price for the item
            $totalPrice = $item['price'] * $item['quantity'];
            $product_name = Product::find($item['id'])->name;

            // Push an associative array containing item details to the $itemDetails array
            $cartData[] = [
                'price' => $totalPrice,
                'quantity' => $item['quantity'],
                'name' => $product_name
            ];
        }

        $invoiceNo = rand(20, 160);

        // Generate the HTML content for the invoice
        $html = view('invoice_template', [
            'customerId' => $customerId,
            'paymentMethods' => $paymentMethods,
            'commentForCook' => $commentForCook,
            'amount' => $amount,
            'cartData' => $cartData,
            'invoiceNo' => 'Cart: '.$invoiceNo,
            'status' => 'invoice',
            // Pass cart data as needed
        ])->render();

        return response()->json(['html' => $html]);
        // return response()->json(['html' => 'Found '.$request->input('page')]);
    }

    private function orderInvoice($id)
    {

                // return 'Hi there '. $id;

        $orderId = $id;
        $order = Order::findorFail($orderId);

        // Retrieve necessary data from the request
        $customerId = $order->customer_id;
        // $paymentMethods = $order->payments->pluck('payment_methods')->toArray(); // Extract payment methods from related payments
        $paid = $order->payments->sum('paid');
        $price = $order->items->sum('price');
        $commentForCook = $order->commentForCook;

        // Initialize an empty array to store item details
        $orderData = [];

        // Loop through the cart items and associate them with the order
        foreach ($order->items as $item) {
            // Calculate the total price for the item
            $totalPrice = $item->price * 1;
            $product_name = Product::findorFail($item->product_id)->name;

            // Push an associative array containing item details to the $itemDetails array
            $orderData[] = [
                'price' => $totalPrice,
                'quantity' => $item->quantity,
                'name' => $product_name
            ];
        }

        $invoiceNo = rand(20, 160);

        // Generate the HTML content for the invoice
        $html = view('invoice_template', [
            'customerId' => $customerId,
            // 'paymentMethods' => $paymentMethods,
            'commentForCook' => $commentForCook,
            'paid' => $paid,
            'cartData' => $orderData,
            'price' => $price,
            'invoiceNo' => 'Order ID: '.$order->id,
            'status' => $order->status,
            'created_at' => $order->created_at
            // Pass cart data as needed
        ])->render();

        return $html;
        // return response()->json(['html' => $html]);
        // return response()->json(['html' => 'Found '.$request->input('page')]);
    }

}
