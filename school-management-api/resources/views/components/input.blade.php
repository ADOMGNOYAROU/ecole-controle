<!-- Form Input Component -->
@props([
    'name',
    'label' => null,
    'type' => 'text',
    'placeholder' => null,
    'value' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'help' => null,
    'icon' => null,
    'size' => 'md', // sm, md, lg
    'variant' => 'default' // default, success, error, warning
])

@php
$baseClasses = 'block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors duration-200';

$sizeClasses = [
    'sm' => 'px-3 py-2 text-sm',
    'md' => 'px-4 py-2.5 text-sm',
    'lg' => 'px-4 py-3 text-base'
];

$variantClasses = [
    'default' => '',
    'success' => 'border-green-300 focus:ring-green-500 focus:border-green-500 dark:border-green-600 dark:focus:ring-green-400 dark:focus:border-green-400',
    'error' => 'border-red-300 focus:ring-red-500 focus:border-red-500 dark:border-red-600 dark:focus:ring-red-400 dark:focus:border-red-400',
    'warning' => 'border-yellow-300 focus:ring-yellow-500 focus:border-yellow-500 dark:border-yellow-600 dark:focus:ring-yellow-400 dark:focus:border-yellow-400'
];

$classes = $baseClasses . ' ' . $sizeClasses[$size] . ' ' . $variantClasses[$variant];
if ($disabled) $classes .= ' opacity-50 cursor-not-allowed bg-gray-50 dark:bg-gray-800';
if ($readonly) $classes .= ' bg-gray-50 dark:bg-gray-800';
@endphp

<div class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative">
        @if($icon)
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        </div>
        @endif
        
        <input
            type="{{ $type }}"
            id="{{ $name }}"
            name="{{ $name }}"
            {{ $value ? 'value="' . $value . '"' : '' }}
            {{ $placeholder ? 'placeholder="' . $placeholder . '"' : '' }}
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            class="{{ $classes }} {{ $icon ? 'pl-10' : '' }}"
            {{ $attributes }}
        >
    </div>
    
    @if($error)
        <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @elseif($help)
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $help }}</p>
    @endif
</div>
