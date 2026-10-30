# Producción en Coolify o Dokploy

## Flujo de entrega

`CI y producción` se ejecuta en pull requests y al subir cambios a `develop`, `main` o `master`:

1. Calidad, PHPStan 9, suite PHP y compilación; Dusk se ejecuta en otro trabajo del mismo flujo.
2. Si ambos trabajos pasan, se construye el destino Docker `production` para Linux AMD64.
3. Se arranca esa imagen con PostgreSQL efímero. Se comprueban migraciones, web, colas, scheduler, revisión y ausencia de archivos de desarrollo.
4. Solo desde `main` se publica `ghcr.io/juanjose23/hotel-bugambilia:sha-<commit>` con `GITHUB_TOKEN`.
5. Si está habilitado el despliegue, el entorno GitHub `production` controla el acceso a sus credenciales y sus reglas de revisión. El mismo digest probado se promociona a `:production`.
6. Se solicita el despliegue por API a Coolify o Dokploy. El trabajo solo confirma éxito cuando el dominio devuelve la revisión esperada y HTTP 200 en `/up` y `/admin/login`.

Un HTTP 200 de la API que encola el despliegue no se considera confirmación de que la aplicación se actualizó. La revisión incluye el commit, el número de ejecución y su intento para distinguir incluso dos despliegues del mismo commit. La comprobación espera hasta diez minutos; si falla, GitHub marca el trabajo como fallido y requiere revisar la plataforma. No se revierten migraciones ni se borran volúmenes automáticamente.

Los trabajos reutilizables `tests.yml` y `dusk-tests.yml` también pueden lanzarse manualmente. La pipeline CI/CD los llama para validar el mismo commit antes de publicar. Las pruebas omitidas existentes siguen documentadas en `verificacion-portabilidad-2026-10-30.md`; no se consideran aprobaciones.

## Imagen de producción

```sh
docker build --target production --build-arg APP_REVISION=local-production-validation -t hotel-bugambilia:production .
```

Este destino copia exclusivamente los directorios necesarios de la aplicación, las dependencias Composer de producción y los recursos compilados. No incluye `.env`, Node, pruebas ni dependencias Composer de desarrollo. La configuración se entrega al arrancar; las cachés de configuración, rutas, eventos y vistas se generan con esos valores. OPcache está activado sin comprobar fechas de archivos porque el código se reemplaza mediante una imagen nueva.

El arranque rechaza depuración activada, una APP_KEY vacía/incorrecta o la clave de pruebas, una URL pública sin HTTPS y una conexión distinta de PostgreSQL. La clave debe generarse una vez para producción y conservarse entre versiones. No la cambies en cada despliegue.

La imagen tiene comprobación de salud y registra su revisión en `/health-release.txt`; este archivo contiene el identificador público del commit y de la ejecución de CI, nunca credenciales. Está preparada para un VPS AMD64; para ARM64 hay que adaptar `platforms` en la pipeline y validar esa arquitectura.

## Configuración en la plataforma

Crea un recurso **Docker Compose** utilizando `compose.production.yaml`. Configura la plataforma para descargar la imagen publicada, sin volver a construir el código. El archivo no fija nombres de contenedores, puertos del anfitrión ni redes del proxy: Coolify/Dokploy los administran.

- Configura acceso de lectura a GHCR en el servidor si el paquete es privado. Usa credenciales de lectura del registro; el `GITHUB_TOKEN` temporal de CI no se guarda en el VPS.
- Copia las variables de `.env.production.example` al panel y sustituye sus valores. Guarda las credenciales del hotel en el panel, no en Git.
- `HOTEL_IMAGE=ghcr.io/juanjose23/hotel-bugambilia:production` selecciona la versión promocionada por CI. Antes de activar la primera promoción puedes usar una etiqueta `sha-<commit>` ya publicada.
- Usa una base PostgreSQL de producción administrada en Coolify/Dokploy o externamente. Su nombre/usuario/contraseña/host son obligatorios; la plantilla no crea ni reutiliza la base del portátil. Conecta el recurso y PostgreSQL mediante la red interna de la plataforma, o configura la conexión externa y su SSL según el proveedor.
- Configura `TRUSTED_PROXIES` con las IP o CIDR reales del proxy interno de la plataforma. Laravel usará sus cabeceras HTTPS para generar enlaces y mantener sesiones seguras. No uses `*` ni expongas Apache directamente a Internet.
- Asigna el dominio HTTPS al servicio `app`, puerto interno **80**. Asegura acceso público a `/health-release.txt`, `/up` y `/admin/login`, sin caché para el archivo de revisión.
- El servicio `release` ejecuta `migrate --force`. Web, worker y scheduler dependen de que termine correctamente. No ejecuta semillas ni crea usuarios de demostración.
- Los procesos PHP usan SIGTERM y un proceso init para detenerse correctamente; Apache conserva su señal de cierre propia. Mantén un único scheduler y respeta la exclusión de tareas programadas. `worker` procesa la cola de base de datos, con timeout de 600 segundos, reintentos después de 660 segundos y apagado de hasta 660 segundos, para respetar los reportes largos existentes.
- El volumen `hotel-uploads` conserva comprobantes, imágenes y otros archivos del hotel. Solo se comparte `storage/app`; las cachés compiladas pertenecen a cada contenedor y versión.
- Activa copias de seguridad de PostgreSQL y del volumen, y comprueba la restauración antes de habilitar producción. Las migraciones deben ser compatibles con la versión anterior durante el cambio de contenedores; usa cambios graduales de esquema.

Desactiva los despliegues directos por push de Git para este recurso: la solicitud de producción debe venir después de CI. Para Dokploy usa el recurso de tipo Docker Compose, no Docker Stack/Swarm, ya que la dependencia de migración requiere Compose.

### Coolify

Habilita la API y genera un token con permiso de despliegue. Copia el webhook autenticado del recurso a `DEPLOY_URL`; `DEPLOY_PLATFORM=coolify` usa POST y autorización Bearer. Configura la extracción de la etiqueta `production` en cada despliegue.

### Dokploy

Genera una clave API. Configura `DEPLOY_URL=https://<panel>/api/compose.deploy`, `DEPLOY_PLATFORM=dokploy` y el identificador del recurso en `DOKPLOY_COMPOSE_ID`. Se envía `x-api-key` y un cuerpo con el identificador y `freshVolumes=false`, conservando los volúmenes. La opción de extraer imágenes debe estar habilitada.

## GitHub Actions

Crea el entorno **production** en Settings → Environments y configura sus reglas de revisión si corresponden. Protege `main` y exige los trabajos de calidad, navegador e imagen de la pipeline.

Variables del repositorio:

| Variable | Valor |
| --- | --- |
| `PRODUCTION_DEPLOY_ENABLED` | `true` cuando el servidor y sus respaldos estén preparados; ausente/false permite construir y publicar sin desplegar |
| `DEPLOY_PLATFORM` | `coolify` o `dokploy` |
| `PRODUCTION_URL` | Dominio HTTPS público, por ejemplo `https://hotel.example.com` |

Secretos del entorno `production`:

| Secreto | Uso |
| --- | --- |
| `DEPLOY_URL` | Webhook autenticado de Coolify o endpoint `compose.deploy` de Dokploy |
| `DEPLOY_TOKEN` | Token de la plataforma |
| `DOKPLOY_COMPOSE_ID` | Solo Dokploy: identificador del recurso Compose |

La publicación GHCR usa el `GITHUB_TOKEN` del trabajo y permiso `packages: write`; no requiere guardar un token de escritura permanente. Permite a Actions publicar paquetes en la configuración del repositorio y concede al VPS lectura del paquete privado. Los pull requests no reciben credenciales de producción ni publican imágenes.

## Validación local aislada

```sh
# Construir antes con APP_REVISION=local-production-validation.
# PowerShell:
$env:EXPECTED_REVISION = 'local-production-validation'
node scripts/production-smoke.mjs
# POSIX: EXPECTED_REVISION=local-production-validation node scripts/production-smoke.mjs
node --test scripts/deploy-production.test.mjs
```

La prueba genera una APP_KEY sintética y credenciales propias en `.local`, utiliza un proyecto Compose exclusivo y una base efímera, y elimina únicamente sus contenedores/volúmenes al finalizar. Por defecto usa el puerto 8097; `SMOKE_PORT` permite cambiarlo. No conecta con la base real. `PRODUCTION_IMAGE` permite probar otra etiqueta local de la imagen.

## Reversión

Conserva las etiquetas por commit y los respaldos. Para revertir la aplicación, selecciona en el panel la etiqueta `sha-<commit>` anterior y despliega de nuevo. Pausa la promoción automática mientras se investiga el incidente. La reversión del código requiere compatibilidad con el esquema vigente; una restauración de base se trata como operación separada y no se dispara automáticamente.

## Verificación local realizada el 30 de octubre de 2026

- Imagen `hotel-bugambilia:production` construida y arrancada con configuración de producción y una APP_KEY sintética propia.
- Migraciones completas aprobadas sobre PostgreSQL efímero, con la aplicación, worker y scheduler funcionando.
- HTTP 200 en `/up` y `/admin/login`, revisión de imagen correcta y enlaces HTTPS al simular el proxy interno.
- Rechazo comprobado de depuración activada y de la clave de pruebas. Comprobada la ausencia de `.env`, pruebas, Node y Pest en el contenedor.
- Siete pruebas PHP aprobadas, con diecinueve comprobaciones, para confianza del proxy y manejo de errores; seis pruebas Node aprobadas para el control de despliegue.
- Pipeline y workflows reutilizables aprobados por actionlint. Los servicios y el volumen de la prueba sintética se eliminaron al terminar; la base real permaneció intacta.

La publicación en GHCR y la llamada a una plataforma real no se ejecutaron desde esta sesión. Para activarlas hay que subir los cambios a GitHub, registrar las variables/secretos indicados y preparar el recurso de producción en el panel. La imagen local usa la revisión `local-production-validation`; CI sustituye ese valor por el commit y los identificadores de su ejecución.

## Fuentes de la integración

- [Coolify: GitHub Actions e imágenes publicadas](https://coolify.io/docs/applications/sources/github/actions)
- [Dokploy: API Docker Compose](https://docs.dokploy.com/docs/api/compose)
- [GitHub: publicación de imágenes Docker](https://docs.github.com/en/actions/tutorials/publish-packages/publish-docker-images)
- [Laravel: despliegue y optimización](https://laravel.com/framework/docs/deployment)
