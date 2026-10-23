# Guía: login y dashboard en la rama `laravel`

Esta guía explica cómo pasar el login y el dashboard de la web antigua (PHP plano, rama `dev`) a la rama `laravel`, quién hace cada parte y cómo crear las tablas que faltan.

## Resumen y reparto

| # | Tarea | Quién | Depende de |
|---|---|---|---|
| 0 | Base común: login con la tabla `members`, roles y arreglo de `route('home')` | **Alejandro** (propuesta: hay que hacerlo antes que el resto) | — |
| 1 | Diseño del login con los colores de AISC | **Xinyi** | Se puede empezar ya (es solo vista) |
| 2 | Dashboard principal (`/dashboard`) | **Christian** | 0 y las tablas `form_submissions`, `event_registrations`, `recruiting` |
| 3 | Página de usuarios (`/dashboard/users`) | **Alejandro** | 0 y la tabla `form_submissions` |
| 4 | Templates de las tablas que faltan | **Los tres** (reparto en la [sección 4](#4-templates-de-las-tablas-que-faltan--los-tres)) | — |

**Orden recomendado:** primero cada uno crea sus tablas (sección 4), en paralelo Alejandro hace el paso 0 y Xinyi el diseño del login. Después Christian hace el dashboard y Alejandro la página de usuarios.

---

## Cómo trabajar

### Ramas

Cada persona crea su rama desde `laravel` y abre una PR contra `laravel` (no contra `main`):

```bash
git checkout laravel
git pull
git checkout -b xinyi/login-design      # o christian/dashboard, alejandro/users...
```

### Poner el proyecto en marcha (la primera vez)

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
composer run dev
```

La base de datos local es SQLite (`DB_CONNECTION=sqlite` en `.env.example`), así que no hace falta instalar MySQL. La web queda en `http://localhost:8000`. El dashboard está en `/dashboard` y el login en `/login`.

### Antes de abrir la PR

```bash
vendor/bin/pint --dirty          # formatea el código PHP
php artisan test --compact       # todos los tests en verde
```

Cada cambio lleva su test (Pest, en `tests/Feature`). En cada sección hay un ejemplo.

---

## Cómo funciona hoy (web antigua) y qué cambia

| | Web antigua (`dev`) | Laravel |
|---|---|---|
| Login | `login.php` busca en `members` por `mail` y comprueba `password_verify($password, password_hash)` | Fortify (`/login`), configurado para usar `members` |
| Sesión | `$_SESSION['activated']`, `$_SESSION['role']`, `$_SESSION['name']` | `auth()->user()` devuelve el `Member` que ha iniciado sesión |
| Permisos | Cada página repite `$allowed_roles = [...]` | Middleware `role:admin,web` en la ruta |
| Consultas | SQL a mano con `$conn->query(...)` | Modelos de Eloquent: `FormSubmission::count()` |
| Invitados | `login.php` también busca en `guests` | Más adelante, con un login propio (fuera de esta guía) |

Roles que se usan en la web antigua: `admin`, `events`, `web`, `finance`, `marketing` (y `guest` para invitados). `users.php` también permite `viewer`, pero ese rol **no existe** en la tabla `members` ni en el enum `MemberRole`. Hay que decidir si se añade o se quita.

---

## 0. Base común: login con `members` — Alejandro (propuesta)

Ahora mismo Fortify usa la tabla `users` de Laravel, que está vacía, así que ningún miembro podría entrar. Además la página de login da error 500. Este paso lo arregla.

### 0.1 Arreglar el error 500 de `route('home')`

La ruta `home` necesita el idioma (`/es` o `/en`), pero los layouts del login la llaman sin él. Hay que cambiarlo en:

- `resources/views/layouts/auth/simple.blade.php`
- `resources/views/layouts/auth/card.blade.php`
- `resources/views/layouts/auth/split.blade.php` (dos veces)

```blade
{{-- antes --}}
<a href="{{ route('home') }}" ...>
{{-- después --}}
<a href="{{ route('home', ['locale' => app()->getLocale()]) }}" ...>
```

### 0.2 Hacer que `Member` sirva para iniciar sesión

En `app/Models/Member.php`:

```php
use Illuminate\Foundation\Auth\User as Authenticatable;

class Member extends Authenticatable   // antes: extends Model
{
    // ... lo que ya hay ...

    /**
     * La contraseña está en la columna password_hash, no en "password".
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    /**
     * El sidebar usa $user->name y $user->email.
     */
    public function getNameAttribute(): string
    {
        return (string) $this->full_name;
    }

    public function getEmailAttribute(): ?string
    {
        return $this->mail;
    }

    /**
     * Iniciales para el avatar del sidebar (copiado de User).
     */
    public function initials(): string
    {
        return \Illuminate\Support\Str::of($this->name)->explode(' ')->take(2)
            ->map(fn (string $word) => \Illuminate\Support\Str::substr($word, 0, 1))->implode('');
    }
}
```

Las contraseñas actuales funcionan tal cual: `password_hash()` de PHP genera hashes bcrypt (`$2y$...`) y Laravel los comprueba sin cambios. Nadie tiene que resetear su contraseña.

### 0.3 Decirle a Laravel que los usuarios son `Member`

En `config/auth.php`:

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\Member::class,   // antes: App\Models\User::class
    ],
],
```

En `app/Providers/FortifyServiceProvider.php`, dentro de `boot()`, el login busca por `mail` y solo deja entrar a miembros activos. El formulario puede seguir usando el campo `email`:

```php
Fortify::authenticateUsing(function (Request $request): ?Member {
    $member = Member::query()
        ->where('mail', $request->input('email'))
        ->where('active', 'yes')
        ->first();

    return $member && Hash::check($request->input('password'), $member->password_hash)
        ? $member
        : null;
});
```

### 0.4 Quitar registro y verificación de email

Los miembros los crea un admin y la tabla `members` no tiene `email_verified_at`.

- `config/fortify.php` → en `features`, quitar `Features::registration()` y `Features::emailVerification()`.
- `routes/web.php` y `routes/settings.php` → quitar `'verified'` de los middleware.
- La página `settings/profile` edita `name`/`email` del modelo `User`. Hay que adaptarla a `full_name`/`mail` o quitarla de momento.

### 0.5 "Recordarme"

Laravel guarda la sesión larga en la columna `remember_token`, que `members` no tiene. Hay dos opciones:

- Quitar el checkbox "Remember me" de `login.blade.php`.
- O crear una migración que añada `$table->rememberToken();` a `members`. En producción también habría que añadir la columna.

### 0.6 Middleware de roles

```bash
php artisan make:middleware EnsureMemberHasRole --no-interaction
```

```php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    $role = $request->user()?->role?->value;

    if (! in_array($role, $roles, true)) {
        abort(403);
    }

    return $next($request);
}
```

Registrarlo en `bootstrap/app.php`, junto al `locale` que ya existe:

```php
$middleware->alias([
    'locale' => SetLocale::class,
    'role' => EnsureMemberHasRole::class,
]);
```

Uso en las rutas: `->middleware(['auth', 'role:admin,web'])` es lo mismo que `$allowed_roles = ['admin', 'web']`.

### 0.7 Tests

Los tests de `tests/Feature/Auth` y `DashboardTest.php` usan `User::factory()`. Hay que cambiarlos a `Member::factory()` y añadir uno que compruebe que un miembro con `active = 'no'` no puede entrar.

```php
test('members can log in with their mail', function () {
    $member = Member::factory()->create([
        'mail' => 'test@aisc.es',
        'password_hash' => Hash::make('secret'),
    ]);

    $this->post(route('login.store'), ['email' => 'test@aisc.es', 'password' => 'secret'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($member);
});
```

---

## 1. Diseño del login — Xinyi

**Objetivo:** que `/login` (y "olvidé mi contraseña") se vean con la identidad de AISC Madrid, igual que la web pública.

### Archivos

| Archivo | Qué es |
|---|---|
| `resources/views/layouts/auth/simple.blade.php` | El layout que usa el login ahora mismo (fondo, logo, tarjeta) |
| `resources/views/layouts/auth/card.blade.php` y `split.blade.php` | Otros dos layouts que trae el starter kit. Se elige uno en `resources/views/layouts/auth.blade.php` |
| `resources/views/pages/auth/login.blade.php` | El formulario (email, contraseña, botón) |
| `resources/views/pages/auth/forgot-password.blade.php` y `reset-password.blade.php` | Recuperar contraseña |
| `resources/views/components/auth-header.blade.php` | Título y subtítulo del formulario |
| `resources/views/components/app-logo.blade.php` y `app-logo-icon.blade.php` | Logo |
| `resources/css/app.css` | Colores (tokens) de toda la web |

### Colores de la asociación

Vienen de `AESTHETIC.md` (rama `dev`):

| Nombre | Hex | Uso |
|---|---|---|
| Rosa (principal) | `#EB178E` | Botones, enlaces, destacados |
| Cian (secundario) | `#20CCF1` | Botones secundarios, hover |
| Rosa oscuro | `#D43089` / `#C4106F` | Hover/pulsado del rosa |
| Tintes suaves | `#FCE4F3` (rosa), `#E8F7FF` (cian) | Fondos |
| Texto | `#333333` (títulos), `#555555` (cuerpo), `#757575` (secundario) | |

En `resources/css/app.css` ya existen como tokens de Tailwind. Usa las clases en vez de escribir hex sueltos:

- `bg-primary`, `text-primary`, `border-primary` → rosa
- `bg-secondary`, `text-secondary` → cian
- `bg-background`, `bg-surface`, `text-foreground`, `text-muted-foreground`, `border-border`

Los componentes Flux usan el color `accent`, que ya está mapeado al rosa. Por eso `<flux:button variant="primary">` sale rosa sin hacer nada.

### Qué hacer

1. Elegir el layout (`simple`, `card` o `split`) y cambiarlo en `resources/views/layouts/auth.blade.php`.
2. Poner el logo de AISC (`public/images/...` o el componente `app-logo`) y un fondo con los tintes o el degradado del footer (blanco → rosa claro → `#EB178E`).
3. Pasar los textos a español. Ya existe `lang/es.json`: añade las claves que uses con `__('...')`, por ejemplo `"Log in to your account": "Inicia sesión en tu cuenta"`.
4. Comprobar el modo oscuro: la clase `.dark` tiene sus propios colores en `app.css`.
5. Comprobar en móvil (ancho de unos 375px).

Si no ves los cambios de CSS, tienes que tener corriendo `npm run dev` (o `composer run dev`).

### Test

`tests/Feature/Auth/AuthenticationTest.php` ya tiene "login screen can be rendered". Basta con que siga en verde. Si añades textos, puedes comprobarlos:

```php
$this->get(route('login'))->assertOk()->assertSee('Inicia sesión');
```

---

## 2. Dashboard principal — Christian

**Objetivo:** la página que se ve nada más iniciar sesión, igual que `dashboard/dashboard.php` de la web antigua.

### Qué muestra la página antigua

- "Hola, {nombre}"
- Tarjeta **Usuarios registrados**: `SELECT COUNT(*) FROM form_submissions`
- Tarjeta **Aplicantes recruiting**: `COUNT(*) FROM recruiting WHERE cohort = '2026-27-1C'`
- Tarjeta **Eventos programados**: `COUNT(*) FROM events WHERE start_datetime > NOW()`
- Total de inscripciones a eventos: `COUNT(*) FROM event_registrations`
- Suscritos a la newsletter (`newsletter = 'yes'`) y dados de baja (`'no'`)
- Tabla **Últimos suscriptores**: los 5 últimos de `form_submissions` por `submitted_at`

**Roles:** `admin`, `events`, `web`, `finance`, `marketing` (todos los miembros).

### Archivos

| Archivo | Qué hacer |
|---|---|
| `app/Http/Controllers/DashboardController.php` | Nuevo: calcula los números y los pasa a la vista |
| `resources/views/dashboard.blade.php` | Ya existe (placeholder del starter kit): poner las tarjetas y la tabla |
| `resources/views/layouts/app/sidebar.blade.php` | Menú lateral: enlaces a Dashboard, Usuarios, etc. |
| `routes/web.php` | Cambiar `Route::view('/dashboard', ...)` por el controlador |

### Pasos

```bash
php artisan make:controller DashboardController --no-interaction
```

```php
class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'totalUsers' => FormSubmission::count(),
            'activeSubscribers' => FormSubmission::where('newsletter', 'yes')->count(),
            'unsubscribed' => FormSubmission::where('newsletter', 'no')->count(),
            'latestSubscribers' => FormSubmission::latest('submitted_at')->limit(5)->get(),
            'upcomingEvents' => Event::where('start_datetime', '>', now())->count(),
            'eventRegistrations' => EventRegistration::count(),
            'recruitingApplicants' => Recruiting::where('cohort', config('aisc.recruiting_cohort'))->count(),
        ]);
    }
}
```

- La cohorte (`'2026-27-1C'`) está escrita a mano en la web antigua. Mejor ponerla en un config (`config/aisc.php`) o en `.env`.
- Las tarjetas pueden usar los componentes de Flux que ya trae el proyecto (`flux:heading`, `flux:text`) y las clases de color de la sección 1.

```php
// routes/web.php
Route::middleware(['auth', 'role:admin,events,web,finance,marketing'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        // Alejandro añade aquí /users
    });
```

**Necesitas los modelos** `FormSubmission`, `EventRegistration` y `Recruiting` (sección 4). Mientras no existan, puedes dejar esas tarjetas a 0.

### Test

```php
test('dashboard shows the number of registered users', function () {
    FormSubmission::factory()->count(3)->create();
    $member = Member::factory()->create();

    $this->actingAs($member)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('totalUsers', 3);
});

test('guests are redirected to login', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});
```

---

## 3. Página de usuarios — Alejandro

**Objetivo:** el equivalente de `dashboard/users.php`.

> Ojo: en la web antigua "usuarios" **no son los miembros**. Es la tabla `form_submissions`: la gente que se ha apuntado en el formulario de la web o la newsletter.

### Qué hace la página antigua

- Roles: `admin`, `events`, `viewer`
- `SELECT * FROM form_submissions` y lo pinta todo en una tabla, con una columna por cada columna de la base de datos
- Sin paginación ni búsqueda

### Qué hacer en Laravel

| Archivo | Qué hacer |
|---|---|
| `app/Http/Controllers/Dashboard/UserController.php` | Nuevo, método `index` |
| `resources/views/dashboard/users/index.blade.php` | Nueva vista con la tabla |
| `routes/web.php` | Ruta dentro del grupo `dashboard` |
| `resources/views/layouts/app/sidebar.blade.php` | Enlace "Usuarios" en el menú |

```bash
php artisan make:controller Dashboard/UserController --no-interaction
```

```php
public function index(Request $request): View
{
    $users = FormSubmission::query()
        ->when($request->string('search')->toString(), fn ($query, $search) => $query
            ->where('full_name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%"))
        ->latest('submitted_at')
        ->paginate(25)
        ->withQueryString();

    return view('dashboard.users.index', ['users' => $users]);
}
```

```php
Route::get('/users', [UserController::class, 'index'])
    ->middleware('role:admin,events')
    ->name('dashboard.users.index');
```

- Columnas a mostrar: nombre, email, newsletter (sí/no), fecha de alta. **No mostrar `unsubscribe_token`**: con él cualquiera puede dar de baja a esa persona.
- `{{ $users->links() }}` pinta la paginación.
- Mejoras opcionales: filtro "solo newsletter" o exportar a CSV.

### Test

```php
test('users page lists form submissions', function () {
    FormSubmission::factory()->create(['full_name' => 'Ada Lovelace']);
    $admin = Member::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('dashboard.users.index'))
        ->assertOk()
        ->assertSee('Ada Lovelace');
});

test('members without permission get 403', function () {
    $member = Member::factory()->create(['role' => 'marketing']);

    $this->actingAs($member)->get(route('dashboard.users.index'))->assertForbidden();
});
```

---

## 4. Templates de las tablas que faltan — los tres

### Qué es el "template de una tabla"

En Laravel cada tabla tiene cuatro piezas, y se crean de una vez:

```bash
php artisan make:model Contact -mfs --no-interaction
#   -m  migración   database/migrations/xxxx_create_contacts_table.php
#   -f  factory     database/factories/ContactFactory.php
#   -s  seeder      database/seeders/ContactSeeder.php
```

| Pieza | Para qué sirve |
|---|---|
| **Migración** | Define la estructura de la tabla. `up()` la crea (lo que "sube") y `down()` la borra (lo que "baja", para deshacer). `php artisan migrate` ejecuta los `up()` pendientes y `php artisan migrate:rollback` deshace el último lote con los `down()`. |
| **Modelo** | La clase para leer y guardar datos sin escribir SQL: `Contact::create([...])`, `Contact::where(...)->get()`, `$contact->update([...])`, `$contact->delete()` |
| **Factory** | Crea filas con datos falsos para los tests: `Contact::factory()->count(5)->create()` |
| **Seeder** | Rellena la base de datos local con datos de ejemplo (`php artisan db:seed`). Hay que añadirlo a `DatabaseSeeder` |

Ya existen para `members`, `events`, `event_types`, `guests`, `speakers`, `event_speakers`, `projects` y `alumni_honor`.

**Importante:** las migraciones tienen que reproducir **exactamente** las tablas de producción, para que el día que Laravel use la base de datos real todo encaje. Los esquemas de abajo salen del código de la web antigua. Solo `contacts` y `contact_email_logs` vienen de un `CREATE TABLE` real (`migrations/03-contacts.sql`). Para el resto, **antes de dar la migración por buena**, comprobad la tabla real en AdminNeo/phpMyAdmin con:

```sql
SHOW CREATE TABLE form_submissions;
```

Ajustad tipos, longitudes, valores por defecto e índices a lo que salga.

### Reparto de tablas

| Tabla | Modelo | Quién | La necesita |
|---|---|---|---|
| `form_submissions` | `FormSubmission` | Alejandro | Página de usuarios, dashboard |
| `newsletter_logs` | `NewsletterLog` | Alejandro | Envío de newsletter |
| `event_registrations` | `EventRegistration` | Christian | Dashboard, inscripciones, asistencia |
| `recruiting` | `Recruiting` | Christian | Dashboard |
| `project_registrations` | `ProjectRegistration` | Christian | Inscripción a proyectos |
| `contacts` | `Contact` | Xinyi | Contactos (difusión) |
| `contact_email_logs` | `ContactEmailLog` | Xinyi | Envío a contactos |
| `event_guest_access` | `EventGuestAccess` | Xinyi | Invitados por evento |
| `collaborations` | `Collaboration` | Xinyi | Formulario de colaboraciones |
| `regression_points`, `regression_guesses` | — | Opcional | Juego de regresión; preguntad si se sigue usando |

Christian y Alejandro deberían hacer primero las suyas, porque sus páginas dependen de ellas.

### Esquemas

#### `contacts` (exacto, de `migrations/03-contacts.sql`)

| Columna | Tipo | Notas |
|---|---|---|
| id | INT AUTO_INCREMENT | PK |
| full_name | VARCHAR(150) | NOT NULL |
| greeting_name | VARCHAR(100) | NULL. Nombre para el saludo ("Buenos días Silvia") |
| email | VARCHAR(255) | NOT NULL, UNIQUE |
| category | VARCHAR(50) | NOT NULL, índice. Ej: `degree_director` |
| organization | VARCHAR(300) | NULL |
| notes | TEXT | NULL |
| active | TINYINT(1) | NOT NULL, default 1 |
| created_at | TIMESTAMP | default CURRENT_TIMESTAMP |

#### `contact_email_logs` (exacto)

| Columna | Tipo | Notas |
|---|---|---|
| id | INT AUTO_INCREMENT | PK |
| contact_id | INT | NOT NULL, FK → `contacts.id` ON DELETE CASCADE |
| template_name | VARCHAR(255) | NOT NULL. Ruta de la plantilla enviada |
| sent_at | TIMESTAMP | default CURRENT_TIMESTAMP |
| | | índice (`contact_id`, `template_name`) |

```php
Schema::create('contact_email_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
    $table->string('template_name');
    $table->timestamp('sent_at')->useCurrent();
    $table->index(['contact_id', 'template_name']);
});
```

#### `form_submissions` (deducido, comprobar)

| Columna | Tipo propuesto | Sale de |
|---|---|---|
| id | id | |
| full_name | string | `INSERT INTO form_submissions (full_name, email, ...)` |
| email | string, único | `SELECT 1 FROM form_submissions WHERE email = ?` |
| newsletter | enum `yes`/`no` | `WHERE newsletter = 'yes'` / `'no'` |
| unsubscribe_token | string, nullable | baja de la newsletter |
| submitted_at | timestamp, default ahora | `ORDER BY submitted_at DESC` |

```php
Schema::create('form_submissions', function (Blueprint $table) {
    $table->id();
    $table->string('full_name');
    $table->string('email')->unique();
    $table->enum('newsletter', ['yes', 'no'])->default('yes');
    $table->string('unsubscribe_token')->nullable()->unique();
    $table->timestamp('submitted_at')->useCurrent();
});
```

#### `newsletter_logs` (deducido)

```php
Schema::create('newsletter_logs', function (Blueprint $table) {
    $table->id();
    $table->string('email');
    $table->string('template_name');
    $table->timestamp('sent_at')->useCurrent();
    $table->index(['email', 'template_name']);
});
```

#### `event_registrations` (deducido)

| Columna | Tipo propuesto | Sale de |
|---|---|---|
| id | id | |
| event_id | FK → `events.id` | `WHERE event_id = ?` |
| name | string | `INSERT (event_id, name, email, ...)` |
| email | string | `WHERE email = ? AND event_id = ?` |
| attendance_status | enum `attended`/`not_attended` | `SET attendance_status = 'attended'` |
| qr_email_sent | boolean, default false | `SET qr_email_sent = 1` |
| registration_date | timestamp | `$row['registration_date']` |

```php
Schema::create('event_registrations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('event_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('email');
    $table->enum('attendance_status', ['attended', 'not_attended'])->default('not_attended');
    $table->boolean('qr_email_sent')->default(false);
    $table->timestamp('registration_date')->useCurrent();
    $table->unique(['event_id', 'email']);
});
```

#### `recruiting` (deducido)

```php
Schema::create('recruiting', function (Blueprint $table) {
    $table->id();
    $table->string('full_name');
    $table->string('email');
    $table->string('campus')->nullable();
    $table->string('position')->nullable();
    $table->text('interest')->nullable();
    $table->string('cohort');              // ej. '2026-27-1C'
    $table->timestamp('submitted_at')->useCurrent();   // comprobar si existe
    $table->index(['email', 'cohort']);
});
```

En el modelo: `protected $table = 'recruiting';`, porque Laravel buscaría `recruitings`.

#### `project_registrations` (deducido)

```php
Schema::create('project_registrations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('email');
    $table->timestamp('registration_date')->useCurrent();   // comprobar nombre real
    $table->unique(['project_id', 'email']);
});
```

#### `event_guest_access` (deducido)

```php
Schema::create('event_guest_access', function (Blueprint $table) {
    $table->id();
    $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
    $table->foreignId('event_id')->constrained()->cascadeOnDelete();
    $table->unique(['guest_id', 'event_id']);
});
```

Es una tabla intermedia (invitado ↔ evento). En vez de un modelo propio, se puede usar `belongsToMany` en `Guest` y `Event`:

```php
// Guest.php
public function events(): BelongsToMany
{
    return $this->belongsToMany(Event::class, 'event_guest_access');
}
```

#### `collaborations` (deducido)

```php
Schema::create('collaborations', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->string('event_name')->nullable();
    $table->text('description')->nullable();
    $table->string('language', 2)->nullable();   // 'es' / 'en'
    $table->timestamp('created_at')->useCurrent(); // comprobar si existe
});
```

#### `regression_points` y `regression_guesses` (opcional)

```php
Schema::create('regression_points', function (Blueprint $table) {
    $table->id();
    $table->float('x');
    $table->float('y');
});

Schema::create('regression_guesses', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->float('x1');
    $table->float('y1');
    $table->float('x2');
    $table->float('y2');
    $table->string('color', 20);
    $table->float('error');
});
```

---

### Template de referencia completo: `contacts`

Copiad esta estructura para cada tabla, cambiando nombres y columnas.

#### 1. Crear los archivos

```bash
php artisan make:model Contact -mfs --no-interaction
```

#### 2. Migración — `database/migrations/xxxx_xx_xx_xxxxxx_create_contacts_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 150);
            $table->string('greeting_name', 100)->nullable();
            $table->string('email')->unique();
            $table->string('category', 50)->index();
            $table->string('organization', 300)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
```

Equivalencias SQL → Laravel más usadas:

| SQL | Laravel |
|---|---|
| `INT AUTO_INCREMENT PRIMARY KEY` | `$table->id()` |
| `VARCHAR(150) NOT NULL` | `$table->string('x', 150)` |
| `... NULL` | `->nullable()` |
| `TEXT` | `$table->text('x')` |
| `TINYINT(1)` | `$table->boolean('x')` |
| `ENUM('yes','no')` | `$table->enum('x', ['yes', 'no'])` |
| `DEFAULT 1` | `->default(true)` |
| `TIMESTAMP DEFAULT CURRENT_TIMESTAMP` | `$table->timestamp('x')->useCurrent()` |
| `UNIQUE` | `->unique()` |
| `INDEX (...)` | `->index()` o `$table->index(['a', 'b'])` |
| `FOREIGN KEY (x_id) REFERENCES xs(id) ON DELETE CASCADE` | `$table->foreignId('x_id')->constrained()->cascadeOnDelete()` |
| `created_at` y `updated_at` | `$table->timestamps()` (solo si la tabla tiene **las dos**) |

#### 3. Modelo — `app/Models/Contact.php`

```php
<?php

namespace App\Models;

use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    protected $table = 'contacts';

    /**
     * La tabla tiene created_at pero no updated_at.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'full_name',
        'greeting_name',
        'email',
        'category',
        'organization',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Emails enviados a este contacto.
     *
     * @return HasMany<ContactEmailLog, $this>
     */
    public function emailLogs(): HasMany
    {
        return $this->hasMany(ContactEmailLog::class);
    }

    /**
     * Nombre para el saludo; si no hay, el primer nombre.
     */
    public function greetingName(): string
    {
        return $this->greeting_name ?: strtok($this->full_name, ' ');
    }
}
```

Reglas del modelo:

- Si la tabla **no tiene** `created_at` ni `updated_at`: `public $timestamps = false;` (como en `Member`).
- Si solo tiene `created_at`: `const UPDATED_AT = null;` (como en `Guest`).
- Si el nombre de la tabla no es el plural en inglés del modelo, poned `protected $table = '...';`.
- Columnas `yes`/`no`: usad el cast que ya existe, `'newsletter' => YesNoBoolean::class`.
- Columnas secretas (contraseñas, tokens): ponedlas en `protected $hidden`.

#### 4. Factory — `database/factories/ContactFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'greeting_name' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'category' => fake()->randomElement(['degree_director', 'professor', 'staff']),
            'organization' => fake()->company(),
            'notes' => null,
            'active' => true,
        ];
    }

    /**
     * Contacto dado de baja.
     */
    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }
}
```

#### 5. Seeder — `database/seeders/ContactSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Contact::factory()->count(10)->create();
    }
}
```

Y añadirlo en `database/seeders/DatabaseSeeder.php`:

```php
$this->call([
    // ... los que ya hay ...
    ContactSeeder::class,
]);
```

#### 6. Test — `tests/Feature/Models/ContactTest.php`

```bash
php artisan make:test --pest Models/ContactTest --no-interaction
```

```php
<?php

use App\Models\Contact;
use App\Models\ContactEmailLog;

test('a contact can be created with the factory', function () {
    $contact = Contact::factory()->create();

    expect($contact->exists)->toBeTrue()
        ->and($contact->active)->toBeTrue();
});

test('greeting name falls back to the first name', function () {
    $contact = Contact::factory()->create(['full_name' => 'Silvia Santalla', 'greeting_name' => null]);

    expect($contact->greetingName())->toBe('Silvia');
});

test('deleting a contact deletes its email logs', function () {
    $contact = Contact::factory()->create();
    ContactEmailLog::factory()->for($contact)->create();

    $contact->delete();

    expect(ContactEmailLog::count())->toBe(0);
});
```

#### 7. Comprobar

```bash
php artisan migrate                 # crea la tabla
php artisan migrate:rollback        # la borra (prueba que down() funciona)
php artisan migrate --seed          # la vuelve a crear con datos de ejemplo
php artisan test --compact --filter=ContactTest
vendor/bin/pint --dirty
```

### Checklist por tabla

- [ ] `php artisan make:model Nombre -mfs`
- [ ] Migración con las columnas exactas (comprobado con `SHOW CREATE TABLE`)
- [ ] `down()` borra la tabla
- [ ] Modelo: `$table` si hace falta, `$fillable`, `$casts`, `$hidden`, timestamps y relaciones
- [ ] Factory con datos realistas
- [ ] Seeder añadido a `DatabaseSeeder`
- [ ] Test que crea un registro con la factory
- [ ] `migrate`, `migrate:rollback` y `migrate` otra vez funcionan
- [ ] `pint --dirty` y tests en verde
