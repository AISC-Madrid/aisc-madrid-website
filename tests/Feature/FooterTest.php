<?php

test('footer shows social icons and legal links without the old headings', function (string $locale) {
    app()->setLocale($locale);

    $this->get(route('home', ['locale' => $locale]))
        ->assertOk()
        ->assertSee('https://www.instagram.com/aisc_madrid/', false)
        ->assertSee('https://www.linkedin.com/company/ai-student-collective-madrid', false)
        ->assertSee('https://github.com/AISC-Madrid', false)
        ->assertSee('mailto:info@aiscmadrid.com', false)
        ->assertSeeInOrder([
            __('site.footer.newsletter'),
            __('site.footer.terms'),
            __('site.footer.bylaws'),
        ])
        ->assertSee(route('terms', ['locale' => $locale]), false)
        ->assertSee(route('estatutos', ['locale' => $locale]), false)
        ->assertDontSee($locale === 'es' ? 'Todos los derechos reservados' : 'All rights reserved')
        ->assertDontSee($locale === 'es' ? 'Síguenos' : 'Follow us');
})->with(['en', 'es']);
