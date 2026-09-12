<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $boardMembers = [
            ['full_name' => 'Hugo Centeno Sanz', 'position_es' => 'Presidente', 'position_en' => 'President'],
            ['full_name' => 'Alejandro Barroso Bueso', 'position_es' => 'Vicepresidente', 'position_en' => 'Vice President'],
            ['full_name' => 'Juanjo Rosales Hernando', 'position_es' => 'Responsable de Tecnología', 'position_en' => 'Head of Technology'],
            ['full_name' => 'Marta Vallejo Leonor', 'position_es' => 'Responsable de Eventos', 'position_en' => 'Head of Events'],
        ];

        foreach ($boardMembers as $boardMember) {
            Member::factory()->create([
                ...$boardMember,
                'board' => true,
                'active' => true,
                'image_path' => 'https://ui-avatars.com/api/?name='.urlencode($boardMember['full_name']),
            ]);
        }

        Member::factory()->count(2)->honorMember()->create();
    }
}
