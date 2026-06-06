@props(['label', 'name'])
<label class="block">
    <span class="control-label">{{ $label }}</span>
    <select name="{{ $name }}" {{ $attributes->merge(['class' => 'control-field']) }}>
        {{ $slot }}
    </select>
</label>
