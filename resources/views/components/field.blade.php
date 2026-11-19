@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null])
<div>
    <label for="{{ $name }}" class="label">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? '' : old($name, $value) }}" {{ $attributes->merge(['class' => 'input'.($errors->has($name) ? ' border-red-400' : '')]) }}>
    @if($help)<p class="help">{{ $help }}</p>@endif
    @error($name)<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
</div>
