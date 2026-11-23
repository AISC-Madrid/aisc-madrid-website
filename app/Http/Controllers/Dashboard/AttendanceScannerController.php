<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\EventRegistration;
use App\Models\Guest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceScannerController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isGuest = $user instanceof Guest;
        $allowedEvents = [];

        if ($isGuest) {
            $allowedEvents = $user->events()
                ->pluck('events.id')
                ->map(fn ($id): int => (int) $id)
                ->all();
        }

        return view('dashboard.scan-attendance', [
            'isGuest' => $isGuest,
            'allowedEvents' => $allowedEvents,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'event_id' => ['required', 'integer'],
        ]);

        $email = $validated['email'];
        $eventId = (int) $validated['event_id'];
        $user = $request->user();

        // Si el usuario es un guest, verificar que tenga permiso sobre este evento
        if ($user instanceof Guest) {
            $hasAccess = $user->events()
                ->where('events.id', $eventId)
                ->exists();

            if (! $hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => __('No tienes autorización para escanear este evento.'),
                ], 403);
            }
        }

        // Buscar la inscripción por email + event_id
        $registration = EventRegistration::query()
            ->where('email', $email)
            ->where('event_id', $eventId)
            ->first();

        if (! $registration) {
            return response()->json([
                'success' => false,
                'message' => __('El usuario no está registrado en este evento.'),
            ], 404);
        }

        // Actualizar asistencia
        $registration->attendance_status = EventRegistration::STATUS_ATTENDED;

        if ($registration->save()) {
            return response()->json([
                'success' => true,
                'message' => __('Asistencia registrada correctamente (:name).', ['name' => $registration->name]),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('Error al actualizar la asistencia.'),
        ], 500);
    }
}