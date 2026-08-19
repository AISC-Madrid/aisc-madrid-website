@props(['member', 'honor' => false])

<div class="flex flex-col items-center text-center">
    
        href="{{ $member->safeSocialUrl() }}"
        target="_blank"
        rel="noopener noreferrer"
        class="block w-full max-w-[180px] overflow-hidden rounded-2xl border border-border shadow-sm transition hover:shadow-md">
        <img
            src="{{ asset($member->image_path) }}"
            alt="{{ $member->full_name }}"
            class="aspect-square w-full object-cover">
    </a>

    <h5 class="mt-4 text-base font-semibold text-foreground">
        {{ $member->full_name }}
    </h5>

    <p class="mt-1 text-sm text-muted-foreground">
        {{ app()->getLocale() === 'en' ? $member->position_en : $member->position_es }}
    </p>

    @if ($honor)
        @if ($member->graduation_year)
            <p class="mt-1 text-sm italic text-muted-foreground">
                {{ __('site.team.class_of', ['year' => $member->graduation_year]) }}
            </p>
        @endif
        @if ($member->honor_quote)
            <p class="mt-2 text-sm italic text-muted-foreground">
                "{{ $member->honor_quote }}"
            </p>
        @endif
    @endif
</div>