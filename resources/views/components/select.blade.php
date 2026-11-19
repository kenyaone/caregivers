@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => 'Select…', 'help' => null])
@php($current = old($name, $value))
<div>
    <label for="{{ $name }}" class="label">{{ $label }}</label>
    <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'input']) }}>
        @if($placeholder !== false)<option value="">{{ $placeholder }}</option>@endif
        @foreach($options as $key => $opt)
            @php($val = array_is_list($options) ? $opt : $key)
            <option value="{{ $val }}" @selected((string) $current === (string) $val)>{{ $opt }}</option>
        @endforeach
    </select>
    @if($help)<p class="help">{{ $help }}</p>@endif
    @error($name)<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
</div>
