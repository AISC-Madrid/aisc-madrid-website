<?php

test('the home page shows the company logo banner between the header and the events', function (string $locale, string $title) {
    $this->get(route('home', ['locale' => $locale]))
        ->assertOk()
        ->assertSeeInOrder(['</header>', 'data-test="company-logos"', $title, 'id="events"'], false);
})->with([
    ['es', 'Empresas con las que hemos organizado eventos'],
    ['en', 'Companies we&#039;ve organised events with'],
]);

test('every configured company logo is shown and exists on disk', function () {
    $companies = config('aisc.event_companies');

    expect($companies)->not->toBeEmpty();

    $response = $this->get(route('home', ['locale' => 'es']))->assertOk();

    foreach ($companies as $company) {
        expect(public_path('images/companies/'.$company['logo']))->toBeFile();

        $response->assertSee(asset('images/companies/'.$company['logo']), false);
        $response->assertSee($company['name']);
    }
});

test('the logo list is duplicated for the loop but the copy is hidden from screen readers', function () {
    $content = $this->get(route('home', ['locale' => 'es']))->getContent();

    expect(substr_count($content, 'images/companies/microsoft.svg'))->toBe(2);
    expect(preg_match_all('/<ul class="flex shrink-0 items-center gap-14 pe-14"\s*(aria-hidden="true")?\s*>/', $content, $matches))->toBe(2);
    expect(array_filter($matches[1]))->toHaveCount(1);
});

test('the banner is hidden when no companies are configured', function () {
    config(['aisc.event_companies' => []]);

    $this->get(route('home', ['locale' => 'es']))
        ->assertOk()
        ->assertDontSee('data-test="company-logos"', false);
});
