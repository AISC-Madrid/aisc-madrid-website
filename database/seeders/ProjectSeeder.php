<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::factory()->create([
            'title_es' => 'Sistema de Recomendación OpenUC3M',
            'title_en' => 'OpenUC3M Recommendation System',
            'category' => 'ai',
            'description_es' => 'Desarrollo del sistema de recomendación de usuarios, posts y eventos de la red social de la universidad, Open UC3M. La implementación sigue cinco etapas: filtrado inicial para reducir el volumen de candidatos, extracción de atributos mediante consultas a la base de datos, cálculo de puntuación con coeficientes ponderados, conversión a probabilidades mediante la función softmax y entrega al frontend.',
            'description_en' => 'Development of the recommendation system for users, posts and events on the university\'s social network, Open UC3M. The implementation follows five stages: initial filtering to reduce the candidate pool, attribute extraction via database queries, score calculation using weighted coefficients, probability conversion using the softmax function, and delivery to the frontend.',
            'tags' => ['SQL', 'Softmax', 'Frontend'],
            'team_credit' => null,
            'github_url' => null,
            'external_url' => 'https://openuc3m.com/',
            'start_date' => '2025-11-11',
            'end_date' => '2026-01-31',
        ]);

        Project::factory()->create([
            'title_es' => 'Administrador de Eventos',
            'title_en' => 'Event Manager',
            'category' => 'desarrollo-web',
            'description_es' => 'Sistema interno creado desde cero para la organización de registros en eventos, pensado tanto para el equipo de AISC Madrid como para los propios asistentes. Incluye creación y gestión de eventos, control de acceso por roles, envío de recordatorios y newsletters a más de 500 personas de la comunidad, check-in mediante códigos QR y estadísticas de asistencia exportables en PDF.',
            'description_en' => 'Internal system built from scratch to manage event registrations, for both the AISC Madrid team and event guests. It includes event creation and management, role-based access control, reminder emails and newsletters sent to over 500 community members, QR-code check-in, and exportable PDF attendance analytics.',
            'tags' => ['Google Calendar', 'QR', 'Newsletter'],
            'team_credit' => 'Equipo web de AISC Madrid',
            'github_url' => null,
            'external_url' => null,
            'start_date' => '2025-09-01',
            'end_date' => null,
        ]);

        Project::factory()->create([
            'title_es' => 'Detector de Postura — 3er puesto en BearHack 2025 (UC Riverside)',
            'title_en' => 'Posture Detector — 3rd place at BearHack 2025 (UC Riverside)',
            'category' => 'vision',
            'description_es' => 'Dispositivo para detectar malas posturas durante el estudio y avisar mediante un LED y un pitido. Utiliza una webcam y la librería MediaPipe para detectar puntos clave del cuerpo (cuello, torso) y analizar el ángulo de la postura en tiempo real; un Arduino UNO enciende un LED rojo y activa un zumbador si la postura es incorrecta, o un LED verde si es correcta. Tercer puesto en el BearHack Make-a-thon de UC Riverside, un maratón de desarrollo de 36 horas.',
            'description_en' => 'A device that detects bad posture during study sessions and alerts with an LED and a buzzer. It uses a webcam and the MediaPipe library to detect body keypoints (neck, torso) and analyze posture angles in real time; an Arduino UNO lights a red LED and triggers a buzzer for incorrect posture, or a green LED for correct posture. Third place at the BearHack Make-a-thon at UC Riverside, a 36-hour hackathon.',
            'tags' => ['MediaPipe', 'Arduino', 'Computer Vision'],
            'team_credit' => 'Hugo Centeno, Alfonso Mayoral, Lauren Gallego, Yago Cabanes',
            'github_url' => 'https://github.com/centenohugo/Posture-Detection-BearHack-UC-Riverside',
            'external_url' => null,
            'start_date' => '2025-04-05',
            'end_date' => null,
        ]);
    }
}
