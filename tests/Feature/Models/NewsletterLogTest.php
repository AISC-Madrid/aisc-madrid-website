<?php

use App\Models\NewsletterLog;

test('a newsletter log can be created with factory', function () {
    $log = NewsletterLog::factory()->create(['email' => 'ada@example.com']);

    expect($log->exists)->toBeTrue()
        ->and($log->email)->toBe('ada@example.com')
        ->and($log->sent_at)->not->toBeNull();
});
