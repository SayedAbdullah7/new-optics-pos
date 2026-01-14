@props([
    'label' => '',
    'name' => '',
    'value' => '',
    'options' => [],
    'required' => false,
    'placeholder' => 'Select an option',
])

<div class="mb-3">
    <label for="{{ $name }}" class="{{ $required ? 'required' : '' }} form-label fw-semibold">{{ $label }}</label>
    <select
        class="form-select form-control-solid @error($name) is-invalid @enderror"
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $required ? 'required' : '' }}
        data-kt-select2="true"
        {{ $attributes }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" {{ old($name, $value) == $optionValue ? 'selected' : '' }}>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>





