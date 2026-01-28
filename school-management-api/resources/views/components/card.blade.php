<!-- Card Component -->
@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'p-6',
    'shadow' => 'shadow-sm',
    'border' => 'border border-gray-200 dark:border-gray-700',
    'rounded' => 'rounded-lg',
    'bgColor' => 'bg-white dark:bg-gray-800'
])

<div class="{{ $bgColor }} {{ $shadow }} {{ $border }} {{ $rounded }} {{ $padding }}">
    @if($title)
        <div class="mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $title }}</h3>
            @if($subtitle)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    
    {{ $slot }}
</div>
