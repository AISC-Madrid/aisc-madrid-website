<?php

use App\Models\Event;
use App\Models\Member;
use App\Models\Project;

beforeEach(function () {
    config([
        'services.media.base_url' => 'https://s3.aiscmadrid.com/aisc-public',
        'services.cloudinary.cloud_name' => 'dpchpoort',
        'services.cloudinary.folder' => 'aisc_madrid',
    ]);
});

test('empty values have no url', function (?string $path) {
    expect(Event::mediaUrl($path))->toBeNull();
})->with([null, '', '   ']);

test('bucket keys point to the S3 media bucket', function (string $key) {
    expect(Event::mediaUrl($key))->toBe("https://s3.aiscmadrid.com/aisc-public/{$key}");
})->with([
    'events-workshops/10/cover.webp',
    'projects/3/cover.webp',
    'members/ada.webp',
]);

test('bucket keys are url encoded segment by segment', function () {
    expect(Event::mediaUrl('members/Ada Lovelace.webp'))
        ->toBe('https://s3.aiscmadrid.com/aisc-public/members/Ada%20Lovelace.webp');
});

test('the bucket base url comes from config', function () {
    config(['services.media.base_url' => 'http://localhost:9000/aisc-public/']);

    expect(Event::mediaUrl('events-workshops/10/cover.webp'))
        ->toBe('http://localhost:9000/aisc-public/events-workshops/10/cover.webp');
});

test('absolute urls are returned unchanged', function () {
    $url = 'https://res.cloudinary.com/dpchpoort/image/upload/aisc_madrid/events/event1/WelcomeDay.png';

    expect(Event::mediaUrl($url))->toBe($url);
});

test('legacy local paths are served from cloudinary without the images/ prefix', function () {
    expect(Event::mediaUrl('images/logos/PNG/AISC Logo Color.png'))
        ->toBe('https://res.cloudinary.com/dpchpoort/image/upload/aisc_madrid/logos/PNG/AISC%20Logo%20Color.png');

    expect(Event::mediaUrl('\images\events\old.png'))
        ->toBe('https://res.cloudinary.com/dpchpoort/image/upload/aisc_madrid/events/old.png');
});

test('legacy local paths fall back to the public folder without cloudinary', function () {
    config(['services.cloudinary.cloud_name' => null]);

    expect(Event::mediaUrl('images/events/old.png'))->toBe(asset('images/events/old.png'));
});

test('models expose image urls', function () {
    expect(Member::factory()->make(['image_path' => 'members/ada.webp'])->image_url)
        ->toBe('https://s3.aiscmadrid.com/aisc-public/members/ada.webp');

    expect(Project::factory()->make(['image_path' => 'projects/3/cover.webp'])->image_url)
        ->toBe('https://s3.aiscmadrid.com/aisc-public/projects/3/cover.webp');

    expect(Event::factory()->make([
        'gallery_paths' => ['events-workshops/2/gallery/01.webp', '', 'https://example.com/02.webp'],
    ])->gallery_urls)->toBe([
        'https://s3.aiscmadrid.com/aisc-public/events-workshops/2/gallery/01.webp',
        'https://example.com/02.webp',
    ]);
});

test('team page shows member photos stored as bucket keys', function () {
    Member::factory()->create(['full_name' => 'Ada Lovelace', 'image_path' => 'members/ada.webp']);

    $this->get(route('team.index', ['locale' => 'es']))
        ->assertOk()
        ->assertSee('src="https://s3.aiscmadrid.com/aisc-public/members/ada.webp"', false);
});
