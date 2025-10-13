@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'default',
    'icon' => null,
    'iconPosition' => 'left',
    'loading' => false,
    'disabled' => false
])

@php
    $classes = [
        'btn',
        'btn-' . $variant,
        $size !== 'default' ? 'btn-' . $size : '',
        $loading ? 'loading' : '',
        $disabled ? 'disabled' : ''
    ];
@endphp

<button 
    type="{{ $type }}" 
    {{ $attributes->merge(['class' => implode(' ', array_filter($classes))]) }}
    @if($disabled || $loading) disabled @endif
>
    @if($loading)
        <i class="fas fa-spinner fa-spin"></i>
        Loading...
    @else
        @if($icon && $iconPosition === 'left')
            <i class="{{ $icon }}"></i>
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right')
            <i class="{{ $icon }}"></i>
        @endif
    @endif
</button>
