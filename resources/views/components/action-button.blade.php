@props([
    'action',           // Required: URL for the action
    'type' => null,     // For has_action: 'show', 'edit', 'create'
    'method' => null,   // For admin-action-btn: 'POST', 'PUT', 'PATCH', 'DELETE'
    'confirm' => false, // Show confirmation dialog
    'confirmText' => 'Are you sure?',
    'variant' => 'primary',  // Button color variant
    'size' => null,     // Button size: 'sm', 'lg', or null for default
    'icon' => null,     // Icon class (ki-duotone icon name)
    'iconOnly' => false, // Show only icon (for table actions)
    'class' => '',      // Additional CSS classes
    'label' => '',      // Button text
])

@php
    // Determine the action handler class based on method/type
    if ($method === 'DELETE') {
        $handlerClass = 'delete_btn';
    } elseif ($type) {
        $handlerClass = 'has_action';
    } elseif ($method) {
        $handlerClass = 'admin-action-btn';
    } else {
        $handlerClass = 'has_action';
    }

    // Build button classes
    $buttonClasses = 'btn';
    $buttonClasses .= $iconOnly ? ' btn-icon' : '';
    $buttonClasses .= ' btn-' . ($iconOnly ? 'light-' : '') . $variant;
    $buttonClasses .= $size ? ' btn-' . $size : '';
    $buttonClasses .= ' ' . $class;
    $buttonClasses .= ' ' . $handlerClass;
@endphp

<button type="button"
        class="{{ trim($buttonClasses) }}"
        data-action="{{ $action }}"
        @if($type)
            data-type="{{ $type }}"
        @endif
        @if($method && $method !== 'DELETE')
            data-method="{{ $method }}"
        @endif
        @if($confirm)
            data-confirm="true"
            data-confirm-text="{{ $confirmText }}"
        @endif
        {{ $attributes }}>
    @if($icon)
        <i class="ki-duotone ki-{{ $icon }} {{ $iconOnly ? 'fs-5' : 'fs-4 me-1' }}">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
    @endif
    @if(!$iconOnly)
        {{ $label ?: $slot }}
    @endif
</button>




