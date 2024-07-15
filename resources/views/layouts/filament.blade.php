<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Cart' }}</title>
    <!-- Add your CSS and JS links here -->
</head>
<body>
    <header>
        <!-- Your header content goes here -->
    </header>

    <main>
        <div class="container">
            <h1>{{ $title ?? 'Cart' }}</h1>

            <!-- Include the Livewire component -->
            @yield('content')
        </div>
    </main>

    <footer>
        <!-- Your footer content goes here -->
    </footer>

    <!-- Include Livewire scripts -->
    @livewireScripts
</body>
</html>
