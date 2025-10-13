@props([
    'type' => 'text',
    'name' => '',
    'label' => '',
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'help' => null,
    'options' => []
])

<div class="form-group">
    @if($label)
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required)
        <span class="text-danger">*</span>
        @endif
    </label>
    @endif
    
    @switch($type)
        @case('select')
            <select 
                id="{{ $name }}" 
                name="{{ $name }}" 
                class="form-control @error($name) error @enderror"
                @if($required) required @endif
                @if($disabled) disabled @endif
                {{ $attributes }}
            >
                @if($placeholder)
                <option value="">{{ $placeholder }}</option>
                @endif
                @foreach($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @if($value == $optionValue) selected @endif>
                    {{ $optionLabel }}
                </option>
                @endforeach
            </select>
            @break
            
        @case('textarea')
            <textarea 
                id="{{ $name }}" 
                name="{{ $name }}" 
                class="form-control @error($name) error @enderror"
                placeholder="{{ $placeholder }}"
                @if($required) required @endif
                @if($disabled) disabled @endif
                {{ $attributes }}
            >{{ $value }}</textarea>
            @break
            
        @case('checkbox')
            <div class="form-check">
                <input 
                    type="checkbox" 
                    id="{{ $name }}" 
                    name="{{ $name }}" 
                    class="form-check-input @error($name) error @enderror"
                    value="1"
                    @if($value) checked @endif
                    @if($required) required @endif
                    @if($disabled) disabled @endif
                    {{ $attributes }}
                >
                @if($label)
                <label for="{{ $name }}" class="form-check-label">
                    {{ $label }}
                    @if($required)
                    <span class="text-danger">*</span>
                    @endif
                </label>
                @endif
            </div>
            @break
            
        @case('radio')
            @foreach($options as $optionValue => $optionLabel)
            <div class="form-check">
                <input 
                    type="radio" 
                    id="{{ $name }}_{{ $optionValue }}" 
                    name="{{ $name }}" 
                    class="form-check-input @error($name) error @enderror"
                    value="{{ $optionValue }}"
                    @if($value == $optionValue) checked @endif
                    @if($required) required @endif
                    @if($disabled) disabled @endif
                    {{ $attributes }}
                >
                <label for="{{ $name }}_{{ $optionValue }}" class="form-check-label">
                    {{ $optionLabel }}
                </label>
            </div>
            @endforeach
            @break
            
        @default
            <input 
                type="{{ $type }}" 
                id="{{ $name }}" 
                name="{{ $name }}" 
                class="form-control @error($name) error @enderror"
                placeholder="{{ $placeholder }}"
                value="{{ $value }}"
                @if($required) required @endif
                @if($disabled) disabled @endif
                {{ $attributes }}
            >
    @endswitch
    
    @if($error || $errors->has($name))
    <div class="form-error">
        {{ $error ?? $errors->first($name) }}
    </div>
    @endif
    
    @if($help)
    <div class="form-help">
        {{ $help }}
    </div>
    @endif
</div>
