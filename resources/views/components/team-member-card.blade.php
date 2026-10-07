@props(['member', 'honor' => false])

@php
    $socialUrl = $member->safeSocialUrl();
    $hasSocialUrl = $socialUrl !== '#';
@endphp

<{{ $hasSocialUrl ? 'a' : 'div' }}
    @if ($hasSocialUrl)
        href="{{ $socialUrl }}"
        target="_blank"
        rel="noopener noreferrer"
    @endif
    class="group block text-center transition duration-300 hover:scale-105"
>
    <div class="aspect-square overflow-hidden rounded-xl bg-muted shadow-sm transition duration-300 group-hover:shadow-lg">
        <img
            src="{{ $member->image_path ?: 'https://ui-avatars.com/api/?size=512&name='.urlencode($member->full_name ?? '?') }}"
            alt="{{ $member->full_name }}"
            loading="lazy"
            class="size-full object-cover"
        >
    </div>

    <flux:heading size="base" class="mt-3">
        {{ $member->full_name }}
    </flux:heading>

    <flux:text class="mt-1">
        {{ app()->getLocale() === 'en' ? $member->position_en : $member->position_es }}
    </flux:text>

    @if ($honor)
        @if ($member->alumniHonor?->graduation_year)
            <flux:text size="sm" class="mt-2 italic">
                {{ __('team.class_of', ['year' => $member->alumniHonor->graduation_year]) }}
            </flux:text>
        @endif

        @if ($member->alumniHonor?->honor_quote)
            <flux:text size="sm" class="mt-2 italic">
                &ldquo;{{ $member->alumniHonor->honor_quote }}&rdquo;
            </flux:text>
        @endif
    @endif
</{{ $hasSocialUrl ? 'a' : 'div' }}>
