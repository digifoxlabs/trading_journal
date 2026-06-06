@props(['label', 'name', 'type' => 'text', 'value' => null])
<label class="block">
    <span class="control-label">{{ $label }}</span>
    <input name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" {{ $attributes->merge(['class' => 'control-field']) }}>
</label>
