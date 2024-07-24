<!-- resources/views/stock/index.blade.php -->

@extends('layouts.filament')

@section('content')
    <div class="container">
        <x-filament::section>
            <x-slot name="heading">
                Stock Management Report
            </x-slot>
            <x-slot name="description">
                Review the stock levels, new received quantities, quantities sold, and closing stock for each product.
            </x-slot>
            
            <form method="GET" action="{{ route('stock.index') }}">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="date"
                            id="start_date"
                            name="start_date"
                            value="{{ request('start_date') }}"
                            class="form-control"
                        />
                    </x-filament::input.wrapper>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="date"
                            id="end_date"
                            name="end_date"
                            value="{{ request('end_date') }}"
                            class="form-control"
                        />
                    </x-filament::input.wrapper>
                </div>

                <x-filament::button type="submit" class="btn btn-primary">
                    Filter
                </x-filament::button>
            </form>

            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Opening Stock</th>
                        <th>New Received</th>
                        <th>Quantity Sold</th>
                        <th>Closing Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report as $item)
                        <tr>
                            <td>{{ $item['product_name'] }}</td>
                            <td>{{ $item['opening_stock'] }}</td>
                            <td>{{ $item['new_received'] }}</td>
                            <td>{{ $item['quantity_sold'] }}</td>
                            <td>{{ $item['closing_stock'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-filament::section>
    </div>
@endsection
