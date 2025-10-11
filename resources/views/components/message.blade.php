@props(['type' => 'info'])
@php
    $colors = [
        'success' => '!bg-green-50 !text-green-800 !border-green-200',
        'error' => '!bg-red-50 !text-red-800 !border-red-200',
        'info' => '!bg-blue-50 !text-blue-800 !border-blue-200',
        'warning' => '!bg-yellow-50 !text-yellow-800 !border-yellow-200'
    ];

    $icons = [
        'success' => '✓',
        'error' => '✕',
        'info' => 'ℹ',
        'warning' => '⚠'
    ];
@endphp

<div {{ $attributes->merge(['class' => "flash !flex !items-center !justify-center !gap-3 !p-4 !rounded-lg !border !border-solid !font-medium !text-sm !shadow-sm " . ($colors[$type] ?? $colors['info'])]) }}>
    <span class="!text-base">{{ $icons[$type] ?? $icons['info'] }}</span>
    <span>{{ session('message') }}</span>
</div>