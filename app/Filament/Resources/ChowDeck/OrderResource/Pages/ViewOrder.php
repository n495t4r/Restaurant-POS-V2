<?php


namespace App\Filament\Resources\ChowDeck\OrderResource\Pages;

use App\Filament\Resources\ChowDeck\OrderResource;
use App\Models\Chowdeck\ChowDeckOrder;
use App\Models\Chowdeck\OrderItem;
use App\Services\ChowdeckService;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;
    protected string $orderId;

    // public function mount($record): void
    // {
    //     parent::mount($record);
    //     $this->orderId = $record;
    //     dd($this->orderId);

    // }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // $orderReference = ChowDeckOrder::where('id', $this->orderId)->pluck('reference');
        $recordId = request()->route('record');

        if ($recordId) {
            $order = ChowDeckOrder::findOrFail($recordId);
            $orderReference = $order->reference;
            // dd($recordId. '-' .$orderReference);

            if ($recordId) {
                return OrderItem::findByReference($orderReference)->toArray();
            }
        }
        // return OrderItem::findByReference($data['reference'])->toArray();

        return $data;
    }

   
}
