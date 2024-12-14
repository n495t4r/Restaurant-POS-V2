<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;
use Livewire\Component;

class OrderManagement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Order Management';
    protected static string $view = 'filament.pages.order-management';

    protected static bool $shouldRegisterNavigation = false;

    public $selectedOrder = null;
    public $showOrderModal = false;
    public $pendingOrders = [];
    public $acceptedOrders = [];
    public $readyOrders = [];

    protected $listeners = ['order-created' => 'handleNewOrder'];

    // protected $listeners = ['echo:orders,OrderCreated' => 'handleNewOrder'];


    // protected function getHeaderActions(): array
    // {
    //     return [];
    // }

    public function mount()
    {
        \Log::error('OrderManagement Mount Called', [
            'timestamp' => now(),
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
        ]);

        $this->loadOrders();
        // $this->setupAudioNotification();
    }

    public function loadOrders()
    {
        $this->pendingOrders = $this->getOrdersForToday(2);
        $this->acceptedOrders = $this->getOrdersForToday(3);
        $this->readyOrders = $this->getOrdersForToday(1);

        // Refresh the pending orders
        $this->refreshPendingOrders();
    }

    public function getOrdersForToday($status)
    {
        return Order::where('status', $status)
            ->whereDate('created_at', Carbon::today())
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'created_at' => $order->created_at->format('H:i'),
                    'items_count' => $order->items->count(),
                    // Add other necessary fields, but keep it minimal
                ];
            });
    }

    public function getOrderCountForToday($status)
    {
        return Order::where('status', $status)
            ->whereDate('created_at', Carbon::today())
            ->count();
    }

    #[On('order-created')]
    public function handleNewOrder($orderId)
    {
        \Log::error('Order Creation Event RECEIVED', [
            'orderId' => $orderId,
            'method' => 'handleNewOrder',
            'timestamp' => now(),
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
        ]);

        // dd('Order creation event is received');
        // Play notification sound
        $this->dispatch('play-notification');

        $order = Order::find($orderId);
        if ($order) {
            Notification::make()
                ->title('New Order Received')
                ->body("Order #{$order->id} has been created.")
                ->success()
                ->send();

            // Dispatch a browser event
            $this->dispatch('newOrderCreated', orderId: $order->id);
        }

        $this->loadOrders();

        // Dispatch browser events
        $this->dispatch('play-notification');
        $this->dispatch('newOrderCreated', ['orderId' => $order->id]);
    }

    public function refreshPendingOrders()
    {
        // This method will trigger a re-render of the pending orders section
        $this->dispatch('refresh-pending-orders');
    }

    public function getOrderInfolist(Order $order): Infolist
    {
        return Infolist::make()
            ->record($order)
            ->schema([
                Section::make('Order Details')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Order ID'),
                        TextEntry::make('created_at')
                            ->label('Order Date')
                            ->dateTime(),
                        TextEntry::make('channel.channel')
                            ->label('Channel'),
                        TextEntry::make('customer.name')
                            ->label('Customer'),
                        TextEntry::make('user.name')
                            ->label('Created By'),
                    ])->columns(2),

                Section::make('Order Items')
                    ->schema([
                        TextEntry::make('items')
                            ->label('Items')
                            ->html()
                            ->formatStateUsing(function ($state) {
                                return new HtmlString(
                                    collect($state)->map(function ($item) {
                                        return "
                                            <div class='flex justify-between py-1'>
                                                <span>{$item->product->name} x {$item->quantity}</span>
                                                <span>₦" . number_format($item->price, 2) . "</span>
                                            </div>
                                        ";
                                    })->join('')
                                );
                            }),
                    ]),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('commentForCook')
                            ->label('Notes for Cook'),
                    ]),
            ]);
    }

    public function updateOrderStatus($orderId, $status)
    {
        $order = Order::find($orderId);
        $order->status = $status;
        $order->save();

        $statusMessages = [
            0 => 'Order declined',
            1 => 'Order marked as ready',
            3 => 'Order accepted',
        ];

        Notification::make()
            ->title($statusMessages[$status])
            ->success()
            ->send();

        $this->loadOrders();
    }

    protected function setupAudioNotification(): void
    {
        $this->dispatch('register-notification-sound', [
            'url' => asset('notification.mp3')
        ]);
    }

    // public function render()
    // {
    //     return view('filament.pages.order-management');
    // }
}
