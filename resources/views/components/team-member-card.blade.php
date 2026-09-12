@props(['member', 'honor' => false])

<flux:card class="flex flex-col items-center p-6 text-center">
    <a
        href="{{ $member->safeSocialUrl() }}"
        target="_blank"
        rel="noopener noreferrer"
        class="block h-28 w-28 overflow-hidden rounded-full ring-2 ring-border transition hover:ring-primary"
    >
        <img
            src="{{ $member->image_path ?: 'https://ui-avatars.com/api/?name='.urlencode($member->full_name ?? '?') }}"
            alt="{{ $member->full_name }}"
            class="h-full w-full object-cover"
        >
    </a>

    <flux:heading size="base" class="mt-4">
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
</flux:card>
