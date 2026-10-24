<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('legacy table migrations skip tables that already exist', function (string $migrationFile, string $table) {
    expect(Schema::hasTable($table))->toBeTrue();

    DB::table('migrations')->where('migration', $migrationFile)->delete();

    $this->artisan('migrate', ['--path' => "database/migrations/{$migrationFile}.php"])
        ->assertSuccessful();

    expect(Schema::hasTable($table))->toBeTrue();
})->with([
    ['2026_09_11_164619_create_members_table', 'members'],
    ['2026_09_11_170103_create_event_types_table', 'event_types'],
    ['2026_09_11_170104_create_events_table', 'events'],
    ['2026_09_11_170105_create_guests_table', 'guests'],
    ['2026_09_11_170106_create_speakers_table', 'speakers'],
    ['2026_09_11_170107_create_event_speakers_table', 'event_speakers'],
    ['2026_09_11_171104_create_projects_table', 'projects'],
    ['2026_09_12_000000_create_alumni_honor_table', 'alumni_honor'],
]);
