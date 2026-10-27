<?php

use App\Models\Event;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

test('the s3 disk uses the S3 adapter pointed at the media bucket', function () {
    $disk = Storage::disk('s3');

    expect($disk->getAdapter())->toBeInstanceOf(AwsS3V3Adapter::class);
    expect(config('filesystems.disks.s3'))
        ->bucket->toBe('aisc-public')
        ->endpoint->toBe('https://s3.aiscmadrid.com');
});

test('the s3 disk url for a key matches the url shown on the site', function () {
    $key = 'events-workshops/10/cover.webp';

    expect(Storage::disk('s3')->url($key))->toBe(Event::mediaUrl($key));
});

test('uploaded files are stored in the bucket under a media key', function () {
    Storage::fake('s3');

    $key = UploadedFile::fake()->image('cover.webp')->storeAs('events-workshops/10', 'cover.webp', 's3');

    expect($key)->toBe('events-workshops/10/cover.webp');
    Storage::disk('s3')->assertExists($key);
    expect(Event::mediaUrl($key))->toBe('https://s3.aiscmadrid.com/aisc-public/events-workshops/10/cover.webp');
});
