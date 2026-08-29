@props([
    'permission',
    'type' => 'button',
    'class' => 'btn-primary',
    'onclick' => null,
    'href' => null,
    'label' => '',
    'icon' => null,
])

@php
    $hasPermission = auth()->user()->hasPermission($permission);
    $finalClass = $class . (!$hasPermission ? ' opacity-50 cursor-not-allowed filter grayscale' : '');
    $alertMessage = "You do not have permission to " . str_replace(['.', '_'], ' ', $permission) . ".";
@endphp

@if($href && $hasPermission)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $finalClass]) }}>
        @if($icon) {!! $icon !!} @endif
        <span>{{ $label }}</span>
    </a>
@elseif($href && !$hasPermission)
    <a href="javascript:void(0)" 
       onclick="alert('{{ $alertMessage }}')" 
       {{ $attributes->merge(['class' => $finalClass]) }}>
        @if($icon) {!! $icon !!} @endif
        <span>{{ $label }}</span>
    </a>
@else
    <button type="{{ $type }}" 
            @if($hasPermission) @if($onclick) onclick="{!! $onclick !!}" @endif @else onclick="alert('{{ $alertMessage }}'); event.preventDefault(); return false;" @endif
            {{ $attributes->merge(['class' => $finalClass]) }}>
        @if($icon) {!! $icon !!} @endif
        <span>{{ $label }}</span>
    </button>
@endif
