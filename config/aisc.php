<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Recruiting cohort
    |--------------------------------------------------------------------------
    |
    | The recruiting process currently open (course-semester). The dashboard
    | counts the applicants in the `recruiting` table for this cohort.
    |
    */

    'recruiting_cohort' => env('RECRUITING_COHORT', '2026-27-1C'),

    /*
    | Invite link to the AISC Madrid WhatsApp community (newsletter success page and welcome email).
    */

    'whatsapp_community_url' => env('WHATSAPP_COMMUNITY_URL', 'https://chat.whatsapp.com/BpdXitZhwGCCpErwBoj3hv?mode=r_c'),

    'social' => [
        'instagram' => 'https://www.instagram.com/aisc_madrid/',
        'linkedin' => 'https://www.linkedin.com/company/ai-student-collective-madrid/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Companies we have organised events with
    |--------------------------------------------------------------------------
    |
    | Shown as a logo banner on the home page. Logos live in
    | public/images/companies. "icon" logos are just a symbol, so the
    | company name is shown next to them; the rest are full wordmarks.
    | "large" draws a logo bigger when its file has a lot of empty space.
    |
    */

    'event_companies' => [
        ['name' => 'Microsoft', 'logo' => 'microsoft.svg', 'icon' => false],
        ['name' => 'Google', 'logo' => 'google.svg', 'icon' => true],
        ['name' => 'Hugging Face', 'logo' => 'hugging-face.svg', 'icon' => true],
        ['name' => 'GitHub', 'logo' => 'github.svg', 'icon' => true],
        ['name' => 'Spotify', 'logo' => 'spotify.svg', 'icon' => true],
        ['name' => 'BCG X', 'logo' => 'bcg.svg', 'icon' => false],
        ['name' => 'BBVA', 'logo' => 'bbva.svg', 'icon' => false],
        ['name' => 'JetBrains', 'logo' => 'jetbrains.svg', 'icon' => true],
        ['name' => 'Openbank', 'logo' => 'openbank.svg', 'icon' => false],
        // Its SVG has a lot of empty space around the mark, so it is drawn larger.
        ['name' => 'Multiverse Computing', 'logo' => 'multiverse-computing.svg', 'icon' => false, 'large' => true],
        ['name' => 'Bdeo', 'logo' => 'bdeo.svg', 'icon' => false],
        ['name' => 'Lovable', 'logo' => 'lovable.svg', 'icon' => false],
        ['name' => 'Supabase', 'logo' => 'supabase.svg', 'icon' => true],
        ['name' => 'Milfshakes', 'logo' => 'milfshakes.png', 'icon' => false],
    ],

];
