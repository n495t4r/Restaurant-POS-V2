@props([
    'heading' => null,
    'description' => null,
    'icon' => null,
    'iconColor' => 'gray',
])


    <div class="flex items-center space-x-4">
        
        <div>
            <h3 class="text-lg font-medium text-gray-900"> {{$heading}} </h3>
            <p class="mt-1 text-sm text-gray-500"> {{$description}} </p>
        </div>
    </div>