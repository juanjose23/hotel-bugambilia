# Entorno portable de Hotel Bugambilia

## Requisitos

Docker Desktop con motor Linux en Windows/macOS, o Docker Engine con Compose v2 en Linux. El entorno de review necesita acceso a PostgreSQL. Los perfiles de pruebas incluyen su propio PostgreSQL y Dusk incluye Selenium.

Para trabajar sin Docker: PHP 8.4.1+, Composer 2, Node 22.12+ y npm 10+, PostgreSQL y Chrome/Chromium con ChromeDriver compatible. El PHP debe estar en PATH; `PHP_BINARY` permite elegir otro ejecutable sin editar los archivos compartidos. En Windows también se detectan lanzadores como `php.bat`.

## Aplicación

El entorno de review usa el PostgreSQL existente indicado en `.env`. Si PostgreSQL está en el equipo, usa `DB_HOST=host.docker.internal` para acceder desde los contenedores. Conserva las credenciales y la clave de la aplicación de ese entorno.

```sh
docker compose build app
docker compose up -d app worker scheduler
docker compose ps
```

Los tres servicios comparten la imagen `hotel-bugambilia:review` y el volumen `hotel-storage`. La web escucha en http://localhost por defecto. Para usar otro puerto, ajusta `HOTEL_PORT` y `APP_URL` en `.env`. El arranque omite las migraciones y las semillas automáticas. El worker y el scheduler operan sobre la base configurada. Para revisar solo la interfaz, levanta únicamente `app`.

```sh
docker build --target review -t hotel-bugambilia:review .
docker build --target production -t hotel-bugambilia:production .
docker build --target tooling -t hotel-bugambilia:tools .
```

Las imágenes de review y producción incluyen Nginx, PHP-FPM y los recursos compilados. La imagen de producción conserva las validaciones de arranque y su Compose dedicado. La imagen de herramientas añade el entorno de frontend y las dependencias de pruebas.

### Alias y subdominios locales

Para usar un nombre como `review.hotel.test`, agrega `127.0.0.1 review.hotel.test` al archivo de hosts del equipo y ajusta `APP_URL=http://review.hotel.test:8080` al puerto publicado. Nginx recibe el nombre en el mismo puerto. El bloque `server` de `docker/nginx.conf` permite declarar los nombres en `server_name` o definir bloques adicionales si cada subdominio necesita reglas propias.

## Pruebas y Dusk

```sh
docker compose -f compose.ci.yaml --profile quality run --rm --build tools
docker compose -f compose.ci.yaml --profile quality run --rm tools node scripts/project.mjs test --filter=Guias
docker compose -f compose.ci.yaml --profile dusk run --rm --build dusk
docker compose -f compose.ci.yaml --profile quality run --rm tools node scripts/project.mjs check
```

`hotel_testing` y `hotel_dusk` pertenecen a un PostgreSQL efímero. Dusk reinicia su base, carga `DuskSeeder` y limpia las cookies entre casos. Selenium accede a `http://dusk-app`. Las capturas y los registros se guardan en `tests/Browser/screenshots` y `tests/Browser/console`.

```sh
docker compose -f compose.ci.yaml --profile quality --profile dusk stop tools dusk dusk-app selenium test-db
```

Ejecución nativa:

```sh
php -r "file_exists('.env') || copy('.env.example', '.env');"
composer install
npm ci
npm run build
composer test
```

Configura las credenciales compartidas en `.env.testing`; para tu equipo puedes sobrescribirlas en `.env.testing.local` (ignorado por Git y Docker) o mediante variables de proceso (`DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE`). El nombre debe terminar en `_testing`. Para Dusk copia `.env.dusk.example` a `.env.dusk.local`, configura otra base terminada en `_dusk` y usa una URL HTTP local con puerto libre:

```sh
php artisan dusk:chrome-driver --detect
composer dusk
```

El comando de Dusk prepara su base, inicia su propio servidor y lo detiene al terminar. `DUSK_DRIVER_URL` permite usar Selenium externo; indicar `BROWSER=chrome` por sí solo no desactiva ChromeDriver local. Para un servidor de pruebas ya gestionado externamente usa `DUSK_EXTERNAL_SERVER=1`. No se reemplaza `.env` ni se borra la caché del entorno normal.

## Husky

`npm ci` instala los hooks cuando hay Git disponible. En CI, producción y Docker la instalación se omite de forma explícita. Los hooks funcionan con comandos de Node y PHP, sin rutas personales ni dependencias de Bash específicas.

- `pre-commit`: formato, ESLint, tipos y Pint, en modo comprobación.
- `pre-push`: PHPStan, suite PHP completa y compilación; las pruebas están habilitadas.
- Dusk se ejecuta completo en CI y mediante `composer dusk` o el perfil Docker; no exige abrir un navegador para cada commit.

Los únicos hooks del proyecto son `pre-commit` y `pre-push`; siempre se ejecutan localmente con PHP y Node del equipo. No invocan Docker ni construyen imágenes, incluso si `HOTEL_CHECK_MODE=docker` está definido o `.hotel-checks.json` selecciona Docker para otros comandos. Se necesitan las dependencias locales instaladas con Composer y npm.

No se omiten fallos automáticamente. Si un control falla, corrige lo indicado y repite. Los controles no reformatean ni guardan archivos durante un commit o push.

Las pruebas funcionales usan 144 reservas históricas sintéticas mediante `DEMO_RESERVAS_HISTORICAS`. La demostración normal conserva 10.000; esa carga masiva puede configurarse por separado para pruebas de rendimiento. Los escenarios de reservas, cobros y facturación permanecen activos.

La imagen fija 512 MB para PHP y el lanzador asigna 1 GB a Pest/Dusk, evitando diferencias con el límite de memoria del PHP instalado en cada equipo.

En este equipo quedó seleccionado `native` en `.hotel-checks.json`. Las pruebas de pre-push utilizan la base sintética local configurada en `.env.testing.local`. Para comandos manuales distintos de los hooks puede elegirse Docker mediante `HOTEL_CHECK_MODE=docker` o `.hotel-checks.json`; la construcción y publicación de imágenes de producción pertenece a la pipeline CI/CD. No ejecutes simultáneamente dos suites PHP sobre la misma base de pruebas.
