{{--
    Select Component
    Usage:
        <x-select name="courier" label="Kurir" :options="['jne' => 'JNE', 'pos' => 'POS']" />
        <x-select name="city" label="Kota" :options="$cities" :selected="$selectedCity" />
--}}
@props([
    'name'    => null,
    'label'   => null,
    'options' => [],
    'selected' => null,
    'placeholder' => '— Pilih —',
    'required' => false,
    'disabled' => false,
    'error'   => null,
])

@php
$id = $name ?? 'select_' . uniqid();
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

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => "w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 transition bg-white $inputClass"]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $value => $label)
            <option value="{{ $value }}" @if(old($name, $selected) == $value) selected @endif>
                {{ $label }}
            </option>
        @endforeach
    </select>

    @if($error)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
