
@extends('layouts.app')

@section('content')
<section class="section" id="team">
    <div class="container scroll-margin">
        <div class="text-center mb-5 px-3 px-md-5">
            <h2 class="fw-bold mb-4" style="color: var(--muted);">{{ __('team.heading') }}</h2>
            <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
            <h6 class="lh-lg text-muted mx-auto" style="max-width: 700px">{{ __('team.subheading') }}</h6>
        </div>

        <div class="mt-5 row">
            @foreach ($activeMembers as $member)
                <x-team-member-card :member="$member" />
            @endforeach
        </div>
    </div>
</section>

@if ($honorMembers->isNotEmpty())
<section class="section" id="honor-members">
    <div class="container scroll-margin">
        <div class="text-center mb-5 px-3 px-md-5">
            <h2 class="fw-bold mb-4" style="color: var(--muted);">{{ __('team.honor_heading') }}</h2>
            <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
            <h6 class="lh-lg text-muted mx-auto" style="max-width: 700px">{{ __('team.honor_subheading') }}</h6>
        </div>
        <div class="mt-5 row justify-content-center">
            @foreach ($honorMembers as $member)
                <x-team-member-card :member="$member" :honor="true" />
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection