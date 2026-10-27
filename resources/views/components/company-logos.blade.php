@php
    /** @var array<int, array{name: string, logo: string, icon: bool, large?: bool}> $companies */
    $companies = config('aisc.event_companies', []);
@endphp

{{--
    Logo banner of companies we have organised events with (config/aisc.php → event_companies).
    The row scrolls endlessly (pauses on hover); the list is rendered twice so the loop is seamless,
    and the copy is hidden from screen readers. With "reduce motion" the logos just wrap, without moving.
--}}
@if (count($companies) > 0)
    <section {{ $attributes->class('w-full border-y border-border bg-muted/40 py-10') }} aria-labelledby="companies-title" data-test="company-logos">
        <div class="mx-auto max-w-7xl px-6 md:px-12">
            <h2 id="companies-title" class="text-center text-sm font-semibold uppercase tracking-widest text-muted-foreground">
                {{ __('site.home.companies.title') }}
            </h2>
        </div>

        <div class="aisc-marquee group relative mt-8 overflow-hidden">
            <div class="aisc-marquee-track flex w-max items-center group-hover:[animation-play-state:paused]">
                @foreach ([false, true] as $isCopy)
                    <ul class="flex shrink-0 items-center gap-14 pe-14" @if ($isCopy) aria-hidden="true" @endif>
                        @foreach ($companies as $company)
                            <li class="flex shrink-0 items-center gap-2.5 opacity-60 transition-opacity duration-300 hover:opacity-100">
                                <img
                                    src="{{ asset('images/companies/'.$company['logo']) }}"
                                    alt="{{ $company['icon'] ? '' : $company['name'] }}"
                                    @class([
                                        'w-auto brightness-0 dark:invert',
                                        'h-8' => $company['icon'],
                                        'max-w-40 object-contain' => ! $company['icon'],
                                        'h-7' => ! $company['icon'] && empty($company['large']),
                                        'h-12' => ! $company['icon'] && ! empty($company['large']),
                                    ])
                                >
                                @if ($company['icon'])
                                    <span class="text-lg font-semibold whitespace-nowrap text-foreground">{{ $company['name'] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
        </div>
    </section>
@endif
