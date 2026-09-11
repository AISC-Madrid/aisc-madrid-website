# Correr la web en local (Windows + Herd)

Esta guía documenta cómo poner en marcha `aisc-madrid-website` en local en Windows usando [Laravel Herd](https://herd.laravel.com/windows). Usa **SQLite** como base de datos, que es el driver por defecto del proyecto (ver `.env.example`) y no requiere instalar ni configurar ningún servidor de base de datos aparte.

## Requisitos previos

- [Laravel Herd para Windows](https://herd.laravel.com/windows) instalado y con el sitio `aisc-madrid-website` añadido (Herd sirve el proyecto en `https://aisc-madrid-website.test`).
- Node.js y npm (Herd no gestiona Node; instálalo aparte si no lo tienes: https://nodejs.org).
- El repo clonado, por ejemplo en `C:\AISC\aisc-madrid-website`.

## Pasos

### 1. Instalar dependencias de PHP

Herd ya trae PHP y Composer. Desde la carpeta del proyecto:

```bash
composer install
```

### 2. Configurar el `.env`

Copia el archivo de ejemplo si todavía no tienes uno:

```bash
cp .env.example .env
```

Comprueba que la sección de base de datos use **SQLite** (es el valor por defecto en `.env.example`, no lo cambies a `mysql` a menos que sepas lo que haces):

```
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=
```

> Si tu `.env` apunta a `mysql`/`mariadb`, la forma más simple de arrancar en local es volver a `sqlite` como arriba, salvo que tengas un motivo concreto para usar MariaDB (y sus credenciales correctas).

### 3. Generar la `APP_KEY`

```bash
php artisan key:generate
```

### 4. Crear el fichero de base de datos SQLite

Laravel necesita que el archivo exista antes de migrar:

```bash
touch database/database.sqlite
```

En PowerShell, si `touch` no existe:

```powershell
New-Item -ItemType File -Path database/database.sqlite -Force
```

### 5. Ejecutar las migraciones

```bash
php artisan migrate
```

### 6. Instalar dependencias de frontend y compilar assets

```bash
npm install
npm run build
```

Para desarrollo activo con recarga en caliente, deja esto corriendo en vez de `npm run build`:

```bash
npm run dev
```

### 7. Abrir la web

Con Herd corriendo, visita:

```
https://aisc-madrid-website.test
```

## Problemas comunes

### "The bootstrap/cache directory must be present and writable" (aunque la carpeta exista y parezca escribible)

Es un problema conocido de PHP en Windows: si la carpeta del proyecto tiene marcado el atributo de solo lectura de Windows (visible con `attrib`), la función `is_writable()` de PHP devuelve `false` aunque el Explorador de Windows te deje escribir sin problema en esa carpeta.

Comprueba el atributo:

```powershell
attrib C:\ruta\al\proyecto
```

Si aparece una `R` en la salida, quítala recursivamente:

```powershell
attrib -R "C:\ruta\al\proyecto" /D
attrib -R "C:\ruta\al\proyecto\*.*" /S /D
```

### "Vite manifest not found at: .../public/build/manifest.json"

Significa que los assets de frontend no se han compilado todavía. Ejecuta:

```bash
npm install
npm run build
```

o, si estás desarrollando activamente, `npm run dev` (déjalo corriendo mientras trabajas).

### Error de conexión a la base de datos (SQLSTATE / Access denied) si usas MariaDB/MySQL en vez de SQLite

Si decides usar MariaDB en lugar de SQLite, asegúrate de que:

- El servicio de MariaDB/MySQL está arrancado y escuchando en el puerto configurado en `DB_PORT`.
- El usuario y contraseña en `.env` son correctos para ese servidor (no asumas que la contraseña de un `.env` compartido/antiguo sigue siendo válida en tu instalación local).
- La base de datos indicada en `DB_DATABASE` existe realmente en ese servidor (créala si no: `CREATE DATABASE nombre;`).

Si no necesitas MariaDB específicamente, es más simple volver a `DB_CONNECTION=sqlite` (ver paso 2).
