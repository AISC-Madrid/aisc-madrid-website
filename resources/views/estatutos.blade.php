<x-layouts.public :title="__('estatutos.title') . ' - AISC Madrid'">

    {{-- Page header --}}
    <div class="mx-auto w-full max-w-7xl px-6 pt-10 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <flux:heading size="xl" level="1">
                {!! __('estatutos.title') !!}
            </flux:heading>

            <div class="mx-auto my-4 h-1 w-15 rounded-full bg-primary"></div>
        </div>
    </div>

    {{-- Content --}}
    <div class="mx-auto w-full max-w-7xl px-6 py-10 lg:px-8">

        <flux:heading size="xl" level="2" class="mt-12">{!! __('estatutos.cap1_title') !!}</flux:heading>
        <flux:separator class="my-3" />

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art1_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art1_li1') !!}</li>
                <li>{!! __('estatutos.art1_li2') !!}</li>
                <li>{!! __('estatutos.art1_li3') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art2_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art2_li1') !!}</li>
                <li>
                    <span>{!! __('estatutos.art2_li2_intro') !!}</span>
                    <ol class="mt-2 list-[lower-alpha] space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                        <li>{!! __('estatutos.art2_li2_a') !!}</li>
                        <li>{!! __('estatutos.art2_li2_b') !!}</li>
                        <li>{!! __('estatutos.art2_li2_c') !!}</li>
                        <li>{!! __('estatutos.art2_li2_d') !!}</li>
                        <li>{!! __('estatutos.art2_li2_e') !!}</li>
                    </ol>
                </li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art3_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art3_li1') !!}</li>
                <li>{!! __('estatutos.art3_li2') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art4_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art4_li1') !!}</li>
                <li>{!! __('estatutos.art4_li2') !!}</li>
                <li>{!! __('estatutos.art4_li3') !!}</li>
            </ol>
        </section>

        <flux:heading size="xl" level="2" class="mt-12">{!! __('estatutos.cap2_title') !!}</flux:heading>
        <flux:separator class="my-3" />

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art5_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('estatutos.art5_p') !!}</flux:text>
            <ol class="mt-2 list-[lower-alpha] space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art5_li_a') !!}</li>
                <li>{!! __('estatutos.art5_li_b') !!}</li>
            </ol>
        </section>

        <flux:heading size="xl" level="2" class="mt-12">{!! __('estatutos.cap3_title') !!}</flux:heading>
        <flux:separator class="my-3" />

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art6_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('estatutos.art6_p') !!}</flux:text>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art7_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art7_li1') !!}</li>
                <li>{!! __('estatutos.art7_li2') !!}</li>
                <li>{!! __('estatutos.art7_li3') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art8_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art8_li1') !!}</li>
                <li>{!! __('estatutos.art8_li2') !!}</li>
                <li>{!! __('estatutos.art8_li3') !!}</li>
                <li>{!! __('estatutos.art8_li4') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art9_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('estatutos.art9_p') !!}</flux:text>
            <ol class="mt-2 list-[lower-alpha] space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art9_li_a') !!}</li>
                <li>{!! __('estatutos.art9_li_b') !!}</li>
                <li>{!! __('estatutos.art9_li_c') !!}</li>
                <li>{!! __('estatutos.art9_li_d') !!}</li>
                <li>{!! __('estatutos.art9_li_e') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art10_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art10_li1') !!}</li>
                <li>{!! __('estatutos.art10_li2') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art11_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>
                    <span>{!! __('estatutos.art11_li1_intro') !!}</span>
                    <ol class="mt-2 list-[lower-alpha] space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                        <li>{!! __('estatutos.art11_li1_a') !!}</li>
                        <li>{!! __('estatutos.art11_li1_b') !!}</li>
                        <li>{!! __('estatutos.art11_li1_c') !!}</li>
                        <li>{!! __('estatutos.art11_li1_d') !!}</li>
                        <li>{!! __('estatutos.art11_li1_e') !!}</li>
                    </ol>
                </li>
                <li>{!! __('estatutos.art11_li2') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art12_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art12_li1') !!}</li>
                <li>{!! __('estatutos.art12_li2') !!}</li>
                <li>{!! __('estatutos.art12_li3') !!}</li>
                <li>{!! __('estatutos.art12_li4') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art13_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art13_li1') !!}</li>
                <li>{!! __('estatutos.art13_li2') !!}</li>
            </ol>
        </section>

        <flux:heading size="xl" level="2" class="mt-12">{!! __('estatutos.cap4_title') !!}</flux:heading>
        <flux:separator class="my-3" />

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art14_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art14_li1') !!}</li>
                <li>{!! __('estatutos.art14_li2') !!}</li>
                <li>{!! __('estatutos.art14_li3') !!}</li>
                <li>{!! __('estatutos.art14_li4') !!}</li>
                <li>{!! __('estatutos.art14_li5') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art15_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>
                    <span>{!! __('estatutos.art15_li1_intro') !!}</span>
                    <ol class="mt-2 list-[lower-alpha] space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                        <li>{!! __('estatutos.art15_li1_a') !!}</li>
                        <li>{!! __('estatutos.art15_li1_b') !!}</li>
                        <li>{!! __('estatutos.art15_li1_c') !!}</li>
                        <li>{!! __('estatutos.art15_li1_d') !!}</li>
                        <li>{!! __('estatutos.art15_li1_e') !!}</li>
                    </ol>
                </li>
                <li>{!! __('estatutos.art15_li2') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art16_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art16_li1') !!}</li>
                <li>{!! __('estatutos.art16_li2') !!}</li>
                <li>{!! __('estatutos.art16_li3') !!}</li>
                <li>{!! __('estatutos.art16_li4') !!}</li>
                <li>{!! __('estatutos.art16_li5') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art17_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art17_li1') !!}</li>
                <li>{!! __('estatutos.art17_li2') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art18_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('estatutos.art18_p') !!}</flux:text>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art19_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art19_li1') !!}</li>
                <li>
                    <span>{!! __('estatutos.art19_li2_intro') !!}</span>
                    <ol class="mt-2 list-[lower-alpha] space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                        <li>{!! __('estatutos.art19_li2_a') !!}</li>
                        <li>{!! __('estatutos.art19_li2_b') !!}</li>
                    </ol>
                </li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art20_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art20_li1') !!}</li>
                <li>
                    <span>{!! __('estatutos.art20_li2_intro') !!}</span>
                    <ol class="mt-2 list-[lower-alpha] space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                        <li>{!! __('estatutos.art20_li2_a') !!}</li>
                        <li>{!! __('estatutos.art20_li2_b') !!}</li>
                        <li>{!! __('estatutos.art20_li2_c') !!}</li>
                    </ol>
                </li>
                <li>{!! __('estatutos.art20_li3') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art21_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art21_li1') !!}</li>
                <li>{!! __('estatutos.art21_li2') !!}</li>
                <li>{!! __('estatutos.art21_li3') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art22_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>
                    <span>{!! __('estatutos.art22_li1_intro') !!}</span>
                    <ol class="mt-2 list-[lower-alpha] space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                        <li>{!! __('estatutos.art22_li1_a') !!}</li>
                        <li>{!! __('estatutos.art22_li1_b') !!}</li>
                    </ol>
                </li>
                <li>{!! __('estatutos.art22_li2') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art23_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('estatutos.art23_p') !!}</flux:text>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art24_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art24_li1') !!}</li>
                <li>{!! __('estatutos.art24_li2') !!}</li>
                <li>{!! __('estatutos.art24_li3') !!}</li>
                <li>{!! __('estatutos.art24_li4') !!}</li>
            </ol>
        </section>

        <flux:heading size="xl" level="2" class="mt-12">{!! __('estatutos.cap5_title') !!}</flux:heading>
        <flux:separator class="my-3" />

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art25_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art25_li1') !!}</li>
                <li>
                    <span>{!! __('estatutos.art25_li2_intro') !!}</span>
                    <ol class="mt-2 list-[lower-alpha] space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                        <li>{!! __('estatutos.art25_li2_a') !!}</li>
                        <li>{!! __('estatutos.art25_li2_b') !!}</li>
                        <li>{!! __('estatutos.art25_li2_c') !!}</li>
                    </ol>
                </li>
                <li>{!! __('estatutos.art25_li3') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art26_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art26_li1') !!}</li>
                <li>{!! __('estatutos.art26_li2') !!}</li>
                <li>
                    <span>{!! __('estatutos.art26_li3_intro') !!}</span>
                    <ol class="mt-2 list-[lower-alpha] space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                        <li>{!! __('estatutos.art26_li3_a') !!}</li>
                        <li>{!! __('estatutos.art26_li3_b') !!}</li>
                        <li>{!! __('estatutos.art26_li3_c') !!}</li>
                        <li>{!! __('estatutos.art26_li3_d') !!}</li>
                    </ol>
                </li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art27_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('estatutos.art27_p') !!}</flux:text>
            <ol class="mt-2 list-[lower-alpha] space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art27_li_a') !!}</li>
                <li>{!! __('estatutos.art27_li_b') !!}</li>
                <li>{!! __('estatutos.art27_li_c') !!}</li>
                <li>{!! __('estatutos.art27_li_d') !!}</li>
                <li>{!! __('estatutos.art27_li_e') !!}</li>
                <li>{!! __('estatutos.art27_li_f') !!}</li>
                <li>{!! __('estatutos.art27_li_g') !!}</li>
                <li>{!! __('estatutos.art27_li_h') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art28_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('estatutos.art28_p') !!}</flux:text>
            <ol class="mt-2 list-[lower-alpha] space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art28_li_a') !!}</li>
                <li>{!! __('estatutos.art28_li_b') !!}</li>
                <li>{!! __('estatutos.art28_li_c') !!}</li>
                <li>{!! __('estatutos.art28_li_d') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art29_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('estatutos.art29_p') !!}</flux:text>
            <ol class="mt-2 list-[lower-alpha] space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art29_li_a') !!}</li>
                <li>{!! __('estatutos.art29_li_b') !!}</li>
                <li>{!! __('estatutos.art29_li_c') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art30_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art30_li1') !!}</li>
                <li>{!! __('estatutos.art30_li2') !!}</li>
                <li>{!! __('estatutos.art30_li3') !!}</li>
            </ol>
        </section>

        <flux:heading size="xl" level="2" class="mt-12">{!! __('estatutos.cap6_title') !!}</flux:heading>
        <flux:separator class="my-3" />

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art31_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art31_li1') !!}</li>
                <li>{!! __('estatutos.art31_li2') !!}</li>
                <li>{!! __('estatutos.art31_li3') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art32_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art32_li1') !!}</li>
                <li>{!! __('estatutos.art32_li2') !!}</li>
                <li>{!! __('estatutos.art32_li3') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art33_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('estatutos.art33_p') !!}</flux:text>
            <ol class="mt-2 list-[lower-alpha] space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art33_li_a') !!}</li>
                <li>{!! __('estatutos.art33_li_b') !!}</li>
                <li>{!! __('estatutos.art33_li_c') !!}</li>
                <li>{!! __('estatutos.art33_li_d') !!}</li>
                <li>{!! __('estatutos.art33_li_e') !!}</li>
            </ol>
        </section>

        <flux:heading size="xl" level="2" class="mt-12">{!! __('estatutos.cap7_title') !!}</flux:heading>
        <flux:separator class="my-3" />

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art34_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('estatutos.art34_p') !!}</flux:text>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art35_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('estatutos.art35_p') !!}</flux:text>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art36_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>
                    <span>{!! __('estatutos.art36_li1_intro') !!}</span>
                    <ol class="mt-2 list-[lower-alpha] space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                        <li>{!! __('estatutos.art36_li1_a') !!}</li>
                        <li>{!! __('estatutos.art36_li1_b') !!}</li>
                        <li>{!! __('estatutos.art36_li1_c') !!}</li>
                        <li>{!! __('estatutos.art36_li1_d') !!}</li>
                    </ol>
                </li>
                <li>{!! __('estatutos.art36_li2') !!}</li>
                <li>{!! __('estatutos.art36_li3') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art37_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('estatutos.art37_p') !!}</flux:text>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art38_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art38_li1') !!}</li>
                <li>{!! __('estatutos.art38_li2') !!}</li>
                <li>{!! __('estatutos.art38_li3') !!}</li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art39_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('estatutos.art39_p') !!}</flux:text>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art40_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>{!! __('estatutos.art40_li1') !!}</li>
                <li>{!! __('estatutos.art40_li2') !!}</li>
            </ol>
        </section>

        <flux:heading size="xl" level="2" class="mt-12">{!! __('estatutos.cap8_title') !!}</flux:heading>
        <flux:separator class="my-3" />

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art41_title') !!}</flux:heading>
            <ol class="mt-2 list-decimal space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                <li>
                    <span>{!! __('estatutos.art41_li1_intro') !!}</span>
                    <ol class="mt-2 list-[lower-alpha] space-y-2 pl-6 text-base text-zinc-500 dark:text-white/70">
                        <li>{!! __('estatutos.art41_li1_a') !!}</li>
                        <li>{!! __('estatutos.art41_li1_b') !!}</li>
                        <li>{!! __('estatutos.art41_li1_c') !!}</li>
                    </ol>
                </li>
            </ol>
        </section>

        <section class="mb-8">
            <flux:heading size="lg" level="3">{!! __('estatutos.art42_title') !!}</flux:heading>
            <flux:text class="mt-2">{!! __('estatutos.art42_p') !!}</flux:text>
        </section>
    </div>

</x-layouts.public>
