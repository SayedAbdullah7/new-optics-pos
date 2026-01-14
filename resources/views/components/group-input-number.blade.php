@props([
    'label' => '',
    'name' => '',
    'value' => '',
    'required' => false,
    'min' => null,
    'max' => null,
    'step' => '1',
    'placeholder' => '',
])

<div class="mb-3">
    <label for="{{ $name }}" class="{{ $required ? 'required' : '' }} form-label fw-semibold">{{ $label }}</label>
    <input
        type="number"
        class="form-control form-control-solid @error($name) is-invalid @enderror"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder ?: 'Enter ' . strtolower($label) }}"
        step="{{ $step }}"
        {{ $required ? 'required' : '' }}
        {{ $min !== null ? 'min=' . $min : '' }}
        {{ $max !== null ? 'max=' . $max : '' }}
        {{ $attributes }}
    >
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>





