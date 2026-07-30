<x-layouts.public title="AISC Madrid">
    <div class="flex min-h-screen flex-col items-center">
        <!-- Landing -->
        <header
            class="mx-auto grid min-h-[85vh] w-[90vw] max-w-7xl grid-cols-1 items-center gap-10 py-12 md:grid-cols-12"
        >
            <!-- Text -->
            <div class="order-2 flex flex-col items-start justify-center md:order-1 md:col-span-7">
                <h1 class="text-5xl font-bold leading-tight md:text-7xl">
                    <span class="text-primary">AI</span>
                    <br>
                    Student Collective
                    <br>
                    <span class="text-secondary">Madrid</span>
                </h1>

                <p class="mt-8 max-w-2xl text-base leading-8 text-muted-foreground md:text-lg">
                    Asociación de Estudiantes de la UC3M interesados en la IA.
                    Organizamos talleres prácticos orientados a adquirir habilidades
                    demandadas, eventos con la industria y conectamos estudiantes
                    a través de una comunidad internacional.
                </p>
            </div>

            <!-- Logo -->
            <div
                class="order-1 flex items-center justify-center md:order-2 md:col-span-5 md:justify-end"
            >
                <img
                    src="{{ asset('images/logos/aisc-logo-color.png') }}"
                    alt="Logotipo de la asociación AISC"
                    class="w-2/3 max-w-sm md:w-3/4"
                >
            </div>
        </header>

        <!-- Events -->
        <section
            id="events"
            class="w-full scroll-mt-24 px-6 py-20 md:px-12"
        >
            <div class="mx-auto max-w-7xl">
                <h2 class="text-center text-3xl font-bold md:text-4xl">
                    Eventos
                </h2>

                <div
                    class="mx-auto mt-4 h-[3px] w-16 rounded-full bg-primary"
                ></div>

                <div class="mt-12">
                    <p class="text-center text-muted-foreground">
                        Próximamente aparecerán aquí los eventos.
                    </p>
                </div>
            </div>
        </section>
    </div>
</x-layouts.public>