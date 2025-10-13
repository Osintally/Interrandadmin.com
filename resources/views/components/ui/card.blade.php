@props([
    'title' => null,
    'subtitle' => null,
    'headerActions' => null,
    'footer' => null
])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title || $subtitle || $headerActions)
    <div class="card-header">
        <div>
            @if($title)
            <h3 class="card-title">{{ $title }}</h3>
            @endif
            @if($subtitle)
            <p class="card-subtitle">{{ $subtitle }}</p>
            @endif
        </div>
        @if($headerActions)
        <div class="card-actions">
            {{ $headerActions }}
        </div>
        @endif
    </div>
    @endif
    
    <div class="card-body">
        {{ $slot }}
    </div>
    
    @if($footer)
    <div class="card-footer">
        {{ $footer }}
    </div>
    @endif
</div>
