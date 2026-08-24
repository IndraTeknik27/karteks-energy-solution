{{--
    Form Input Component
    Usage:
        <x-input name="email" label="Email" type="email" placeholder="user@example.com" />
        <x-input name="password" label="Password" type="password" />
        <x-input name="notes" label="Catatan" type="textarea" rows="3" />
        <x-input name="qty" label="Jumlah" type="number" :min="1" :max="100" />
        <x-input name="phone" label="WhatsApp" helper="Contoh: 6281234567890" />
        <x-input name="email" label="Email" :error="$errors->first('email')" />
--}}
@props([
    'name'    => null,
    'label'   => null,
    'type'    => 'text',
    'placeholder' => '',
    'value'   => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error'   => null,
    'helper'  => null,
    'rows'    => 3,
    'min'     => null,
    'max'     => null,
    'step'    => null,
])

@php
$id = $name ?? 'field_' . uniqid();
$inputClass = $error
    ? 'border-red-400 focus:border-red-500 focus:ring-red-200'
    : 'border-gray-200 focus:border-brand-500 focus:ring-brand-100';
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-gray-700">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    @if($type === 'textarea')
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            {{ $attributes->merge(['class' => "w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 transition $inputClass"]) }}
        >{{ old($name, $value) }}</textarea>
    @else
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @if($min !== null) min="{{ $min }}" @endif
            @if($max !== null) max="{{ $max }}" @endif
            @if($step !== null) step="{{ $step }}" @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            {{ $attributes->merge(['class' => "w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 transition $inputClass"]) }}
        >
    @endif

    @if($error)
        <p class="text-xs text-red-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $error }}
        </p>
    @elseif($helper)
        <p class="text-xs text-gray-500">{{ $helper }}</p>
    @endif
</div>
