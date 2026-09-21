@props([
    'size' => 'default',
    'src' => asset('logo (1).png'), // Path default logo
    'alt' => 'PLN Suku Cadang'
])

@php
    // Pengaturan ukuran logo berdasarkan prop size
    $sizeClasses = [
        'sm' => 'h-8',
        'default' => 'h-10',
        'lg' => 'h-12',
    ][$size] ?? 'h-10';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <img 
        src="{{ $src }}" 
        alt="{{ $alt }}" 
        class="{{ $sizeClasses }} w-auto max-w-full object-contain"
    />
</div>