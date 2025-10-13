@props([
    'id' => 'modal',
    'title' => 'Modal',
    'size' => 'medium',
    'closable' => true,
    'showFooter' => false,
    'footerActions' => null
])

@php
    $sizeClasses = [
        'small' => 'max-w-md',
        'medium' => 'max-w-lg',
        'large' => 'max-w-2xl',
        'xl' => 'max-w-4xl'
    ];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['medium'];
@endphp

<div class="modal-overlay" id="{{ $id }}" style="display: none;">
    <div class="modal {{ $sizeClass }}">
        <div class="modal-header">
            <h3 class="modal-title">{{ $title }}</h3>
            @if($closable)
            <button class="modal-close" type="button" onclick="PMS.modal.close()">
                &times;
            </button>
            @endif
        </div>
        
        <div class="modal-body">
            {{ $slot }}
        </div>
        
        @if($showFooter || $footerActions)
        <div class="modal-footer">
            @if($footerActions)
                {{ $footerActions }}
            @else
                <button type="button" class="btn btn-outline" onclick="PMS.modal.close()">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary">
                    Save
                </button>
            @endif
        </div>
        @endif
    </div>
</div>
