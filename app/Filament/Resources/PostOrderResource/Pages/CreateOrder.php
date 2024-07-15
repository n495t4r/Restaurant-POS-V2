<?php

namespace App\Filament\Resources\PostOrderResource\Pages;

use App\Filament\Resources\PostOrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrder extends CreateRecord
{
    protected static string $resource = PostOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id(); // Add user ID

        // $packNumber = 1; // Initialize pack number
        // // dd($data);
        // foreach ($data['packs'] as $packData) {
        //     foreach ($packData['items'] as $item) {
        //         $product = Product::find($item['product_id']);

        //         // Modify data for Order and OrderItems
        //         $data['items'][] = [
        //             'price' => $product->price,
        //             'quantity' => $item['quantity'],
        //             'product_id' => $product->id,
        //             'package_number' => $packNumber++, // Assign pack_id based on incrementing counter
        //         ];
        //     }
        // }
        // dd($data);
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // dd($data);
        $order = Order::create($data);
        
        $orderPrice = 0; // Initialize a variable to store total price

        // $data['order_id'] = $order->id; // Add user ID

        $packNumber = 1; // Initialize pack number
        // dd($data);

        foreach ($data['packs'] as $packData) {
            foreach ($packData['items'] as $itemData) {
                $orderItem = new OrderItem;
                $orderItem->order_id = $order->id; // Set order_id from the created Order
                $orderItem->product_id = $itemData['product_id'];
                $orderItem->price = $itemData['price'];
                $orderItem->quantity = $itemData['quantity'];
                $orderItem->package_number = $packNumber;
                // ... other OrderItem fields
                // dd($orderItem);

                // Calculate total price for the order
                $orderItemPrice = $itemData['price'];
                $orderPrice += $orderItemPrice;

                $orderItem->save();
            }

            $packNumber++;
        }
        // dd($order);

        $payment = new Payment;
        $payment->order_id = $order->id; // Set order_id from the created Order
        $payment->user_id = $data['user_id'];
        $payment->customer_id = $data['customer_id'];
        $payment->payment_method_id = $data['payment_method_id'];
        $payment->amount = $orderPrice;
        $payment->paid = $data['paid'];
        // Calculate the difference between total price and paid amount
        $paymentDifference = $orderPrice - $data['paid'];

        if ($paymentDifference <= 0) {
            $payment->status = 'paid';
        } else if ($paymentDifference < $orderPrice) {
            $payment->status = 'partial';
        } else {
            $payment->status = 'unpaid';
        }
        // dd($orderItem);
        $payment->save();

        return $order;
        // return static::getModel()::create($data);
    }
}
