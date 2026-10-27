@props([
    'sidebar' => false,
])

{{-- AISC Madrid logo + name, used in the dashboard sidebar and header. --}}
@if($sidebar)
    <flux:sidebar.brand name="AISC Madrid" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center">
            <img src="{{ asset('images/logos/aisc-logo-color.svg') }}" alt="" class="size-8" data-test="sidebar-logo">
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="AISC Madrid" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center">
            <img src="{{ asset('images/logos/aisc-logo-color.svg') }}" alt="" class="size-8">
        </x-slot>
    </flux:brand>
@endif
