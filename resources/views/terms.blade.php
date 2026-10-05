<x-layouts.public :title="__('terms.title') . ' - AISC Madrid'">

    {{-- Page header --}}
    <div class="mx-auto w-full max-w-7xl px-6 pt-10 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <flux:heading size="xl" level="1">
                {!! __('terms.title') !!}
            </flux:heading>

            <div class="mx-auto my-4 h-1 w-15 rounded-full bg-primary"></div>
        </div>
    </div>

    {{-- Content --}}
    <div class="mx-auto w-full max-w-7xl px-6 py-10 lg:px-8">

        <section class="mb-8">
            <flux:heading size="lg" level="2">{!! __('terms.sec1_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('terms.sec1_p1') !!}</flux:text>
            <flux:text class="mt-2">{!! __('terms.sec1_p2') !!}</flux:text>
            <flux:text class="mt-2">{!! __('terms.sec1_p3') !!} <flux:link href="mailto:aisc.asoc@uc3m.es">aisc.asoc@uc3m.es</flux:link></flux:text>
            <flux:text class="mt-2">{!! __('terms.sec1_p4') !!}</flux:text>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="2">{!! __('terms.sec2_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('terms.sec2_p1') !!}</flux:text>
            <ul class="mt-2 list-disc space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('terms.sec2_li1') !!}</li>
                <li>{!! __('terms.sec2_li2') !!}</li>
            </ul>
            <flux:text class="mt-2">{!! __('terms.sec2_p2') !!}</flux:text>
            <flux:text class="mt-2">{!! __('terms.sec2_p3') !!}</flux:text>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="2">{!! __('terms.sec3_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('terms.sec3_p1') !!}</flux:text>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="2">{!! __('terms.sec4_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('terms.sec4_p1') !!}</flux:text>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="2">{!! __('terms.sec5_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('terms.sec5_p1') !!}</flux:text>
            <flux:text class="mt-2">{!! __('terms.sec5_p2') !!}</flux:text>
            <flux:text class="mt-2">{!! __('terms.sec5_p3') !!}</flux:text>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="2">{!! __('terms.sec6_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('terms.sec6_p1_before') !!}<flux:link href="mailto:aisc.asoc@uc3m.es">aisc.asoc@uc3m.es</flux:link>{!! __('terms.sec6_p1_after') !!}</flux:text>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="2">{!! __('terms.sec7_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('terms.sec7_p1') !!}</flux:text>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="2">{!! __('terms.sec8_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('terms.sec8_p1') !!}</flux:text>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('terms.sec8_li1') !!}</li>
                <li>{!! __('terms.sec8_li2') !!}</li>
                <li>{!! __('terms.sec8_li3') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="2">{!! __('terms.sec9_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('terms.sec9_p1') !!}</flux:text>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="2">{!! __('terms.sec10_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('terms.sec10_p1') !!}</flux:text>
        </section>
    </div>

</x-layouts.public>
