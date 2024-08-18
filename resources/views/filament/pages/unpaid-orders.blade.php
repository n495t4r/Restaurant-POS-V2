@extends('filament::layouts.app')

@section('content')
    <div class="filament-resource-table">
        <x-filament::card>
            <x-filament::table>
                <x-slot name="header">
                    <x-filament::table-header-cell>SN</x-filament::table-header-cell>
                    <x-filament::table-header-cell>Customer</x-filament::table-header-cell>
                    <x-filament::table-header-cell>Channel</x-filament::table-header-cell>
                    <x-filament::table-header-cell>Amount</x-filament::table-header-cell>
                    <x-filament::table-header-cell>Paid</x-filament::table-header-cell>
                    <x-filament::table-header-cell>Order ID</x-filament::table-header-cell>
                </x-slot>

                <x-slot name="body">
                    @foreach ($unpaidOrders as $index => $order)
                        <x-filament::table-row>
                            <x-filament::table-cell>{{ $index + 1 }}</x-filament::table-cell>
                            <x-filament::table-cell>{{ $order->customer->name ?? 'unselected' }}</x-filament::table-cell>
                            <x-filament::table-cell>{{ $order->channel->channel ?? 'unselected' }}</x-filament::table-cell>
                            <x-filament::table-cell>{{ $order->items->sum('price') }}</x-filament::table-cell>
                            <x-filament::table-cell>{{ $order->payments->sum('paid') }}</x-filament::table-cell>
                            <x-filament::table-cell>{{ $order->id }}</x-filament::table-cell>
                        </x-filament::table-row>
                    @endforeach
                </x-slot>
            </x-filament::table>
        </x-filament::card>
    </div>
@endsection
