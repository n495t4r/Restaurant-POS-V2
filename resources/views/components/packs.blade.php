<!-- resources/views/components/packs.blade.php -->

@foreach ($packs as $pack)
    <div class="pack">
        <h4>Pack {{ $loop->iteration }}</h4>
        <ul>
            @foreach ($pack['items'] as $item)
                <li>
                    <strong>Product Name:</strong> {{ $item['product_name'] }}<br>
                    <strong>Price:</strong> {{ $item['price'] }}<br>
                    <strong>Quantity:</strong> {{ $item['quantity'] }}<br>
                    <strong>Package Number:</strong> {{ $item['package_number'] }}
                </li>
            @endforeach
        </ul>
    </div>
@endforeach
