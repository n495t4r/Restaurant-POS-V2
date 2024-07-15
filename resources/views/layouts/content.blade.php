<!-- \resources\views\layouts\content.blade.php -->

<x-filament::panel>
    <!-- Top navbar -->
    <x-filament::navigation.top />

    <!-- Side navigation -->
    <x-filament::navigation.sidebar />

    <!-- Content -->
    <x-filament::content :title="$title">
        <!-- Content Header (Page header) -->
        <x-slot name="header">
            <h1>@yield('content-header')</h1>
        </x-slot>

        <!-- Main content -->
        <x-slot name="content">
            @yield('content')
        </x-slot>

        <!-- Content actions -->
        <x-slot name="actions">
            <div class="col-sm-6 text-right">
                @yield('content-actions')
            </div>
        </x-slot>
    </x-filament::content>
</x-filament::panel>
