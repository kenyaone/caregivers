@props(['name', 'label', 'value' => null, 'rows' => 4, 'help' => null])
<div>
    <label for="{{ $name }}" class="label">{{ $label }}</label>
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->merge(['class' => 'input']) }}>{{ old($name, $value) }}</textarea>
    @if($help)<p class="help">{{ $help }}</p>@endif
    @error($name)<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
</div>
