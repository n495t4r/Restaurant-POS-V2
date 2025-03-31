<?php

namespace App\Filament\Pages\Traits;

use App\Filament\Pages\OrderManagement;
use App\Filament\Pages\StockHistories;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderChannel;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions\Action as ActionsAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use TomatoPHP\FilamentTypes\Models\Type;

trait HasCheckout
{
    public function checkoutAction(): Action
    {

        return Action::make('checkoutAction')
            ->label('Checkout')
            ->disabled(fn() => StockHistories::isCashierUnitClosed())
            ->tooltip(fn() => StockHistories::isCashierUnitClosed() ? 'Cashier unit is closed' : null)
            ->slideOver()
            ->requiresConfirmation()
            ->form(function (array $arguments) {
                $channels = OrderChannel::where('is_active', true)->pluck('channel', 'id')->toArray(); //change to customer.name instead
                $customers = Customer::where('is_active', true)->whereNotNull('name')->pluck('name', 'id')->toArray(); //change to customer.name instead
                $payment_methods = PaymentMethod::where('is_active', true)->pluck('name', 'id')->toArray(); //change to customer.name instead
                return [
                    Select::make('channel_id')
                        ->label('Order Channel')
                        ->options($channels)
                        ->live()
                        ->preload(),
                    Select::make('customer_id')
                        ->label('Customer')
                        ->options(function (Get $get, Set $set) use ($customers) {
                            if ($get('channel_id') == 6) {
                                $set('customer_id', '');
                                // Filter customers when channel_id is 6
                                return array_filter($customers, function ($value, $key) {
                                    $is_staff = Customer::getStaffIds();
                                    return in_array($key, $is_staff);
                                }, ARRAY_FILTER_USE_BOTH);
                            }
                            return $customers;
                        })
                        ->preload()
                        ->required(function (Get $get): bool {
                            return $get('channel_id') == 6;
                        })
                        ->searchable(),
                    TextInput::make('paid_amount')
                        ->label(trans('Paid amount'))
                        ->step(50)
                        ->placeholder($arguments['total'])
                        ->maxValue($arguments['total'])
                        ->minValue(0)
                        ->visible(
                            function (Get $get, Set $set): bool {
                                if ($get('channel_id') == 6) {
                                    $set('paid_amount', 0);
                                    return false;
                                }
                                return true;
                            }
                        )
                        ->numeric()
                        // ->required()
                        ->live()
                        ->suffixAction(
                            ActionsAction::make('clear')
                                ->icon('heroicon-m-x-mark')
                                ->action(function (TextInput $component) {
                                    $component->state(0);
                                    $this->paid_amount = 0;
                                })
                        )
                        ->afterStateUpdated(function ($state) {
                            $this->paid_amount = $state;
                        }),

                    Select::make('payment_method_id')
                        ->label('Payment method')
                        ->searchable()
                        ->options($payment_methods)
                        ->required(fn(Get $get): bool => $get('paid_amount') > 0)
                        ->visible(fn(Get $get): bool => $get('paid_amount') > 0),
                    TextInput::make('commentForCook')
                        ->label(false)
                        ->placeholder('Enter note for cook'),
                ];
            })
            ->action(function (array $data, array $arguments) {
                if (!StockHistories::isCashierUnitClosed()) {

                    if($data['channel_id'] == 6){
                        
                        $staffOrderAmount = Customer::getTotalOrderAmount($data['customer_id']);
                        if($staffOrderAmount + $arguments['total'] > 2000){
                            $this->notify('The staff Orders should not exceed N2000', 'warning');
                            return;
                        }
                    }
                    // Begin a transaction
                    DB::beginTransaction();
                    try {

                        $cart = Cart::query()->where('session_id', $this->sessionID)->get();

                        //Order model
                        // 'user_id',
                        // 'customer_id',
                        // 'channel_id',
                        // 'commentForCook',
                        // 'status',

                        //Cart model
                        // 'session_id',
                        // 'product_id',
                        // 'qty',
                        // 'price',
                        // 'discount',
                        // 'vat',
                        // 'total',

                        //OrderItems model
                        // 'price',
                        // 'quantity',
                        // 'product_id',
                        // 'order_id',
                        // 'pack_id'=>1,
                        // 'package_number',

                        $order = Order::query()->create([
                            'user_id' => auth()->user()->id,
                            'channel_id' => $data['channel_id'],
                            'customer_id' => $data['customer_id'],
                            'commentForCook' => $data['commentForCook']
                        ]);

                        $order->items()->createMany($cart->map(function ($item) {
                            $item['quantity'] = $item['qty'];
                            $item['price'] = $item['price'] * $item['qty'];
                            Product::decreaseQuantity($item['product_id'], $item['quantity']);

                            return $item;
                        })->toArray());

                        // Create the payment
                        if (isset($data['paid_amount']) && $data['paid_amount'] != null && $data['paid_amount'] > 0) {
                            $payment_method_id = $data['payment_method_id'];
                            $payment = new Payment;
                            $payment->order_id = $order->id; // Set order_id from the created Order
                            $payment->user_id = auth()->user()->id;

                            if ($data['paid_amount']) {
                                $payment->payment_method_id = $payment_method_id;
                            }
                            $payment->paid = $data['paid_amount'];

                            $payment->save();
                        }

                        Cart::query()->where('session_id', $this->sessionID)->delete();
                        // Dispatch a global event

                        $this->notify('Order created successfully');

                        // Detailed logging
                        // \Log::info('Order Created - Event Dispatch', [
                        //     'orderId' => $order->id,
                        //     'method' => 'create',
                        //     'timestamp' => now()
                        // ]);

                        // Method 2: Broadcast event
                        event(new \App\Events\OrderCreated($order));

                        // Dispatch the event
                        $this->dispatch('order-created', orderId: $order->id)->to(OrderManagement::class);


                        // Commit the transaction
                        DB::commit();
                    } catch (\Exception $e) {
                        // Rollback the transaction in case of an error
                        DB::rollBack();

                        // Optionally, rethrow the exception or handle it
                        throw $e;
                    }
                } else {
                    $this->notify('The cashier unit is currently closed. Checkouts are not allowed.', 'error');
                }
            });
    }
}
