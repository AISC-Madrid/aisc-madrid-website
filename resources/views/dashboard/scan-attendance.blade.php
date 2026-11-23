<x-layouts::app :title="__('Escanear asistencia')">
    <div class="@container flex w-full flex-1 flex-col gap-4">
        {{-- Cabecera --}}
        <div>
            <flux:heading size="lg" level="1">{{ __('Escanear asistencia') }}</flux:heading>
            <flux:text class="text-sm">
                {{ __('Utiliza la cámara de tu dispositivo para escanear códigos QR y registrar la asistencia.') }}
            </flux:text>
        </div>

        {{-- Contenedor principal --}}
        <section class="mx-auto w-full max-w-xl rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            @if ($isGuest && empty($allowedEvents))
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-center text-sm text-amber-800 dark:border-amber-800/50 dark:bg-amber-950/50 dark:text-amber-200">
                    {{ __('No tienes eventos asignados para escanear. Contacta con el administrador.') }}
                </div>
            @else
                <div id="qr-reader" class="mx-auto w-full overflow-hidden rounded-lg"></div>
                <div id="qr-reader-results" class="mt-4 text-center"></div>
            @endif
        </section>
    </div>

    @if (! ($isGuest && empty($allowedEvents)))
        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const isGuest = @json($isGuest);
                const allowedEvents = @json($allowedEvents);
                const scanUrl = @json(route('attendance.update'));
                const csrfToken = @json(csrf_token());
                const resultsEl = document.getElementById('qr-reader-results');

                let html5QrcodeScanner = new Html5QrcodeScanner(
                    'qr-reader',
                    { fps: 10, qrbox: 250 }
                );

                async function onScanSuccess(decodedText, decodedResult) {
                    // Detener el escáner inmediatamente para evitar lecturas duplicadas
                    try {
                        await html5QrcodeScanner.clear();
                    } catch (e) {
                        console.warn(e);
                    }

                    // Separar el texto del QR (formato: email;event_id)
                    const parts = decodedText.split(';');

                    if (parts.length < 2) {
                        resultsEl.innerHTML = `<p class="text-base font-semibold text-red-600 dark:text-red-400">❌ Formato QR inválido</p>`;
                        restartScanner();
                        return;
                    }

                    const email = parts[0].trim();
                    const eventId = parseInt(parts[1].trim(), 10);

                    if (!email || isNaN(eventId)) {
                        resultsEl.innerHTML = `<p class="text-base font-semibold text-red-600 dark:text-red-400">❌ Datos del QR incompletos</p>`;
                        restartScanner();
                        return;
                    }

                    // Validación rápida en cliente si es guest
                    if (isGuest && !allowedEvents.includes(eventId)) {
                        resultsEl.innerHTML = `<p class="text-base font-semibold text-red-600 dark:text-red-400">❌ No tienes autorización para escanear este evento</p>`;
                        restartScanner();
                        return;
                    }

                    try {
                        const response = await fetch(scanUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                email: email,
                                event_id: eventId
                            })
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            resultsEl.innerHTML = `<p class="text-base font-semibold text-green-600 dark:text-green-400">✅ ${data.message || 'Escaneado correctamente'}</p>`;
                        } else {
                            resultsEl.innerHTML = `<p class="text-base font-semibold text-red-600 dark:text-red-400">❌ ${data.message || 'Error al registrar asistencia'}</p>`;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        resultsEl.innerHTML = `<p class="text-base font-semibold text-red-600 dark:text-red-400">❌ Error de conexión</p>`;
                    }

                    restartScanner();
                }

                function restartScanner() {
                    setTimeout(function () {
                        resultsEl.innerHTML = '';
                        html5QrcodeScanner.render(onScanSuccess, onScanError);
                    }, 3000);
                }

                function onScanError(errorMessage) {
                    // Ignorar errores continuos de lectura cuando no hay QR enfocado
                }

                html5QrcodeScanner.render(onScanSuccess, onScanError);
            });
        </script>
    @endif
</x-layouts::app>