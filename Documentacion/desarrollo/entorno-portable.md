# Entorno portable de Hotel Bugambilia

## Requisitos

Docker Desktop con motor Linux en Windows/macOS, o Docker Engine con Compose v2 en Linux. Se usa la misma receta en el equipo del desarrollador y en CI. No se necesitan Herd, un dominio `.test`, PHP, Composer, PostgreSQL ni Chrome instalados en el anfitrión para ejecutar el proyecto con Docker.

Para trabajar sin Docker: PHP 8.4.1+, Composer 2, Node 22.12+ y npm 10+, PostgreSQL y Chrome/Chromium con ChromeDriver compatible. El PHP debe estar en PATH; `PHP_BINARY` permite elegir otro ejecutable sin editar los archivos compartidos. En Windows también se detectan lanzadores como `php.bat`.

## Aplicación

```sh
docker compose build app
docker compose up -d app
docker compose exec app php artisan migrate --force
```

Abrir http://localhost:8080. `HOTEL_PORT` permite elegir otro puerto. `hotel-db` y `hotel-storage` conservan la base y los archivos. El servicio no importa la base del equipo ni ejecuta semillas de demostración automáticamente. `docker compose down` detiene la aplicación conservando esos volúmenes.

Las credenciales y la clave predeterminadas de Compose son exclusivamente para desarrollo local. Para otro despliegue define `DOCKER_APP_KEY` y `DOCKER_DB_PASSWORD` propias mediante variables del entorno; no publiques las credenciales locales. Las imágenes excluyen `.env`, bases locales, credenciales de Composer, comprobantes y archivos subidos de `storage/app`.

```sh
docker build --target runtime -t hotel-bugambilia:local .
docker build --target tooling -t hotel-bugambilia:tools .
```

`runtime` contiene PHP/Apache, dependencias de producción y recursos compilados. `tooling` añade Node y las dependencias de pruebas. Composer y npm utilizan sus archivos lock. PostgreSQL se comunica por la red interna de Compose, sin ocupar el puerto de la instalación local.

## Pruebas y Dusk

```sh
docker compose --profile quality run --rm --build tools
docker compose --profile quality run --rm tools node scripts/project.mjs test --filter=Guias
docker compose --profile dusk run --rm --build dusk
docker compose --profile quality run --rm tools node scripts/project.mjs check
```

`hotel_testing` y `hotel_dusk` pertenecen a un PostgreSQL efímero separado del servicio `db`. Dusk reinicia exclusivamente su base, carga `DuskSeeder` y limpia las cookies entre casos para evitar sesiones heredadas. Selenium accede a `http://dusk-app`; no depende de nombres de dominio del equipo, certificados locales ni `host.docker.internal`. Las capturas y los registros se guardan en `tests/Browser/screenshots` y `tests/Browser/console`.

Detener los servicios auxiliares al terminar:

```sh
docker compose --profile quality --profile dusk stop tools dusk dusk-app selenium test-db
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
