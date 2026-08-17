@props(['member', 'honor' => false])

<div class="col-sm-6 col-lg-3">
    <div class="team-box text-center">
        <div class="team-wrapper">
            <div class="team-member">
                <a href="{{ $member->safeSocialUrl() }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ $member->image_path }}" alt="{{ $member->full_name }}" class="img-fluid rounded">
                </a>
            </div>
        </div>
        <h5 class="mt-3" style="color: var(--background)">{{ $member->full_name }}</h5>
        <p class="text-muted">
            {{ app()->getLocale() === 'en' ? $member->position_en : $member->position_es }}
        </p>
        @if ($honor)
            @if ($member->graduation_year)
                <p class="text-muted"><em>{{ __('team.class_of', ['year' => $member->graduation_year]) }}</em></p>
            @endif
            @if ($member->honor_quote)
                <p class="fst-italic text-muted">"{{ $member->honor_quote }}"</p>
            @endif
        @endif
    </div>
</div>