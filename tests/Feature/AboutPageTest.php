<?php

test('about page shows the team photo next to the AI Student Collective story', function () {
    $this->get(route('about', ['locale' => 'es']))
        ->assertOk()
        ->assertSeeInOrder(['AI Student Collective', 'primera asociación de IA', 'equipoAISC.jpg'], false)
        ->assertSee('src="https://res.cloudinary.com/dpchpoort/image/upload/aisc_madrid/resources/equipoAISC.jpg"', false)
        ->assertSee('alt="Equipo de AISC Madrid"', false);
});

test('about page links to the other AISC chapters', function () {
    $this->get(route('about', ['locale' => 'es']))
        ->assertOk()
        ->assertSee('href="https://www.instagram.com/aiscdavis/"', false)
        ->assertSee('href="https://www.instagram.com/aiscuw/"', false)
        ->assertSee('href="https://www.instagram.com/aisc_riv/"', false)
        ->assertSee('<strong>primera asociación de IA</strong>', false);
});

test('about page shows the mission with its image', function () {
    $this->get(route('about', ['locale' => 'es']))
        ->assertOk()
        ->assertSee('Misión')
        ->assertSee('La asociación tiene una visión clara:')
        ->assertSee('Queremos acercar el mundo laboral de la mano de miembros destacados del sector.')
        ->assertSee('Understand%20AI%20Unlock%20Potential.png', false);
});

test('about page shows every value as a tab with its description', function () {
    $response = $this->get(route('about', ['locale' => 'es']))->assertOk();

    foreach (__('site.about.values_items', locale: 'es') as $value) {
        $response->assertSee($value['title'])->assertSee($value['body']);
    }

    $response->assertSee('role="tablist"', false)->assertSee('role="tabpanel"', false);
});

test('about page is translated to english', function () {
    $this->get(route('about', ['locale' => 'en']))
        ->assertOk()
        ->assertSee('first AI association')
        ->assertSee('Mission')
        ->assertSee('Our values')
        ->assertSee('alt="The AISC Madrid team"', false);
});
