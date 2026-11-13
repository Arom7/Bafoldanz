# Despliegue — Docker (local + producción)

Documento de diseño de la dockerización. **Todavía no existe código**: describe qué se creará, cómo se comportará y con qué convenciones se trabajará, de modo que cualquier sesión futura implemente esto sin re-decidir nada.

## Contexto del entorno

- Máquina de desarrollo: Linux (Ubuntu), usuario `arom` con **uid=1000, gid=1000**, ya pertenece al grupo `docker`.
- Docker 29.x + Docker Compose v2 disponibles en el host.
- **No hay PHP ni Composer instalados en el host**: todos los comandos (`artisan`, `composer`, `phpunit`) se ejecutan dentro de contenedores. Node solo se necesita en la etapa de build de producción (Vite), también dentro de Docker.

## Principio central: contenedores como uid=1000 / gid=1000

Todo corre como uid 1000 / gid 1000 para que los archivos generados en el proyecto (logs, cache, vendor, build de Vite) queden con el dueño del host y nunca como root.

Reglas derivadas:

1. El `Dockerfile` recibe `ARG UID=1000` y `ARG GID=1000` con esos valores por defecto, y crea el usuario `app` con esos ids. **Ojo: la base es Alpine**, que no trae `groupadd`/`useradd`/`bash`; se usan `addgroup`/`adduser` (paquete `shadow` si hiciera falta) y `sh` como shell:
   - `addgroup -g $GID app && adduser -D -u $UID -G app -s /bin/sh app`
2. El `Dockerfile` termina con `USER app`. `composer install` se ejecuta con ese usuario.
3. El entrypoint **no** hace `chown -R root`; si toca `storage/` y `bootstrap/cache/`, lo hace solo hacia `1000:1000` y de forma idempotente (solo si el dueño actual no es 1000).
4. En desarrollo el código se monta por bind mount con el uid del host; como coincide con el uid del contenedor, no hay problemas de permisos.
5. Nunca se crea un usuario root de servicio "por comodidad": si algo falla por permisos, se corrige el uid/gid, no el dueño.

## Estructura de archivos a crear (Fase 0)

```
docker/
├── Dockerfile              # multi-stage: stage "dev" (xdebug opcional) y stage "prod" optimizado
├── entrypoint.sh           # espera MySQL → (prod) migrate --force + caches → exec php-fpm / artisan serve
├── php.ini                 # producción: opcache on, realpath_cache, memory_limit
├── local.ini               # desarrollo: display_errors, xdebug (activable por env)
├── nginx/default.conf      # producción: root /var/www/public, fastcgi_pass app:9000
└── backup/entry.sh         # contenedor de backup: mysqldump programado (ver sección Backups)

docker-compose.yml          # LOCAL
docker-compose.prod.yml     # PRODUCCIÓN
.dockerignore
Makefile
docs/05-despliegue.md       # este documento
```

## Imagen base (docker/Dockerfile)

- Base: `php:8.4-fpm-alpine` (Laravel 13 requiere PHP ≥ 8.4 vía Symfony 8; decisión del dueño en Fase 0).
- Extensiones PHP: `pdo_mysql`, `gd` (necesaria para dompdf en v1.1), `zip`, `intl`, `opcache`, `pcntl`.
- Composer copiado desde la imagen oficial `composer:2`.
- Stage **dev**: adicionales de desarrollo (xdebug opcional por variable de entorno), dependencias de composer completas, código por bind mount.
- Stage **prod**: `composer install --no-dev --optimize-autoloader` + `npm ci && npm run build` (stage node intermedio) + código copiado dentro de la imagen, sin bind mounts.
- Variables de build: `UID` (1000), `GID` (1000), `APP_ENV`.
- Misma imagen para local y prod: se elige stage con `--target` en el compose.

## Entorno local (docker-compose.yml)

| Servicio | Definición | Puertos host | Notas |
|---|---|---|---|
| `app` | build stage `dev`, `user: "1000:1000"` | 8000→8000 | `php artisan serve --host=0.0.0.0 --port=8000`; bind mount del código |
| `mysql` | `mysql:8.0` | 33061→3306 | puerto host elegido alto para no chocar con un MySQL local; volumen nombrado `dbdata`; healthcheck `mysqladmin ping` |

Detalles:

- `depends_on: condition: service_healthy` en `app` → Laravel no arranca antes que MySQL.
- Red interna `bafoldanz`; los servicios se resuelven por nombre (`mysql`).
- `.env` del proyecto con `DB_HOST=mysql`, `DB_PORT=3306`. **Mailpit queda fuera del MVP** (no hay correos en v1); si algún día hace falta, se agrega como servicio aparte.
- Vite en dev: se puede ejecutar `npm run dev` dentro del contenedor `app` cuando se necesite; no es un servicio fijo.

## Entorno de producción (docker-compose.prod.yml)

| Servicio | Definición | Puertos host | Notas |
|---|---|---|---|
| `nginx` | `nginx:alpine` + `docker/nginx/default.conf` | 80 (y 443 si hay reverse proxy externo) | único servicio expuesto |
| `app` | build stage `prod`, entrypoint de producción | ninguno | `php-fpm` en 9000 interno; `APP_ENV=production`, `APP_DEBUG=false` |
| `mysql` | `mysql:8.0` + volumen nombrado `dbdata_prod` | **ninguno** | no publica puerto al host; credenciales solo por `.env` |
| `backup` | `mysql:8.0` reutilizando la misma imagen (solo cliente mysql) + `docker/backup/entry.sh` | ninguno | mysqldump programado; escribe a un volumen `backups` (ver sección Backups) |

Detalles:

- El código va dentro de la imagen (sin bind mounts). Solo son volúmenes `storage/` y `bootstrap/cache/` (opcional), el volumen de datos de MySQL y el de backups.
- `composer install --no-dev --optimize-autoloader`; `npm run build` en el build-stage.
- Healthchecks en `app` y `mysql`; `restart: unless-stopped` en todos.
- TLS/HTTPS: lo maneja un **reverse proxy externo** al compose (recomendado si el servidor ya tiene uno; si no, se puede agregar Caddy/Traefik como servicio adicional más adelante). El hosting de producción está **pendiente de definir** (ver Decisiones).
- Scheduler (`schedule:run`): **fuera del MVP** — en v1 no hay tareas programadas y supervisor quedó descartado para evitar contradicciones en el entrypoint. Cuando se necesite (v1.1), se agregará como un contenedor/servicio aparte que corra solo el scheduler, no dentro de `app`.
- Queues: no hay en v1.

## Entrypoint (docker/entrypoint.sh)

Flujo según entorno:

**Producción**
1. Esperar a que MySQL acepte conexiones (loop con `mysqladmin ping` o similar).
2. `php artisan migrate --force` (idempotente; **nunca** `migrate:fresh`, regla del proyecto).
3. `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
4. Ajuste idempotente de dueño de `storage/` y `bootstrap/cache/` a `1000:1000` (solo si hace falta).
5. `exec php-fpm` (queda como PID 1). Sin supervisor ni schedulers en el MVP.

**Desarrollo**
1. Si no existe `.env`: avisar y abortar con mensaje claro (el `.env` lo crea el usuario con `make setup`).
2. `exec php artisan serve --host=0.0.0.0 --port=8000`.

## Assets de Vite en producción (nginx)

Problema: nginx y `app` son imágenes distintas y el código va dentro de la imagen de `app`; nginx no vería `public/build`.

Solución decidida: la **imagen de nginx es multi-stage** y copia `public/` (incluido `public/build`) desde el mismo stage `assets` (node) que usa la imagen de `app`. Ambas imágenes se construyen de la misma versión del código, así los assets siempre coinciden con el PHP. Alternativa descartada: volumen compartido (acoplamiento en runtime, más frágil al escalar).

## Base de datos de tests (separada)

Obligatoria: `RefreshDatabase` sobre la BD de desarrollo **borraría las tablas**, lo que viola la regla del proyecto de no ejecutar comandos destructivos contra la BD.

- BD de tests: `bafoldanz_test` (mismo servidor MySQL del compose local, otra base).
- `phpunit.xml` con `DB_DATABASE=bafoldanz_test`, `DB_USERNAME`/`DB_PASSWORD` de solo esa BD.
- `.env.testing` no se versiona (mismo trato que `.env`); los valores van en `phpunit.xml` o `.env.example` documentados.
- El usuario MySQL del proyecto debe tener permisos sobre ambas BD (`bafoldanz` y `bafoldanz_test`). Se puede preparar con un script init del contenedor MySQL, pero **ese script solo corre la primera vez que se crea el volumen**; por eso existe el atajo `make test-db`, que crea la BD si no existe (y se puede re-ejecutar sin problemas).
- `make test` ejecuta `php artisan test` con `--env=testing` implícito por phpunit.xml.

## Backups (producción)

Los datos incluyen pagos: sin backup no hay MVP.

- Contenedor `backup` en `docker-compose.prod.yml`: mysqldump programado una vez al día.
- **Detalle a verificar al implementar**: la imagen `mysql:8.0` no trae cron, así que el contenedor de backup usa (a) su propia imagen con cron instalado, o (b) un bucle `while true; do dump; sleep 86400; done` en `entry.sh`. Se decide en implementación; el requisito es el dump diario.
- Escribe `bafoldanz_YYYYMMDD.sql.gz` en el volumen `backups`; **retención 30 días** (borra dumps más viejos).
- Restauración: `gunzip < dump.sql.gz | docker compose exec -T mysql mysql -u... bafoldanz` (procedimiento documentado y probado una vez antes del go-live).
- `make backup` / `make backup-list` como atajos (también útiles en local).
- Responsabilidad: el operador del servidor verifica semanalmente que el dump más reciente sea válido (comando `make backup-verify` opcional en v1.1).

## Primer admin y datos reales en producción

- `DemoStudentsSeeder` **no corre en producción** (son datos de ejemplo).
- Comando dedicado (ej. `php artisan app:create-director` o seeder parametrizado) que crea las **2 directoras** con credenciales tomadas de variables de `.env` (`DIRECTORA_1_EMAIL`, `DIRECTORA_1_PASSWORD`, ...).
- `make admin` como atajo en producción.
- Catálogos reales (sucursales, grupos, cuentas) se cargan con `CatalogSeeder`; los alumnos se cargan desde la UI.

## Makefile (atajos oficiales del proyecto)

Comandos que todo el equipo usará (equivale a no tener PHP en el host):

| Comando | Qué hace |
|---|---|
| `make setup` | primer arranque: `cp .env.example .env`, build de imágenes, `composer install`, `key:generate`, `migrate --seed` |
| `make up` / `make down` | levantar/bajar el stack local |
| `make test` | `php artisan test` dentro del contenedor |
| `make pint` / `make pint-test` | corregir / verificar estilo |
| `make migrate` | `php artisan migrate` (nunca fresh) |
| `make seed` | seeders |
| `make routes` | `php artisan route:list` |
| `make admin` | crear/actualizar las directoras con credenciales del `.env` |
| `make test-db` | crear la BD `bafoldanz_test` si no existe |
| `make backup` / `make backup-list` | respaldo manual de la BD / listar respaldos |
| `make logs` | logs del contenedor `app` |
| `make shell` | shell dentro de `app` (en Alpine es `sh`) |

Regla: **siempre** se ejecuta artisan/composer vía `docker compose exec app ...` (o `make ...`), nunca en el host. `./vendor/bin/pint` también corre dentro del contenedor (`make pint`).

## Convenciones transversales

- Puertos y credenciales parametrizados por variables de entorno (sin hardcodear); `docker-compose.yml` usa `${VAR:-default}`.
- Los `.env` reales no se versionan ni se leen/modifican en sesiones (regla vigente); solo `.env.example`.
- `.dockerignore` excluye: `.git`, `node_modules`, `vendor`, `storage/logs`, `.env*`, `docs/` (opcional).
- Imágenes con tags fijos (no `latest`) para reproducibilidad.
- Prohibido ejecutar comandos destructivos contra la BD (`migrate:fresh`, `db:wipe`) salvo decisión explícita del usuario.
- **Todo el código se entrega para revisión del usuario: nunca ejecutar `git commit` ni `git push`.**

## Procedimiento de despliegue en producción

1. En el servidor: clonar el repo y crear `.env` de producción a mano (permisos 600, fuera de git).
2. `docker compose -f docker-compose.prod.yml build --build-arg UID=1000 --build-arg GID=1000`
3. `docker compose -f docker-compose.prod.yml up -d`
4. El entrypoint corre `migrate --force` y caches automáticamente.
5. `make admin` para crear las 2 directoras reales; cargar catálogos y alumnos.
6. Probar la restauración de un backup **antes** del go-live.
7. Actualizaciones: `git pull` → `build` → `up -d`. Rollback: volver al tag/commit anterior y reconstruir.

## Integración con el plan de migración

- La parte local de este documento se implementa en la **Fase 0** (Docker mínimo, antes del scaffold) y se completa durante las fases siguientes (BD de tests en la Fase 1, Makefile completo a medida que crece el proyecto).
- La parte de producción se implementa en la **Fase 5 — Producción y puesta en marcha**.
- A partir de la Fase 0, todo se desarrolla y prueba **contra MySQL en Docker** (`make up` + `make test`), no contra SQLite ni PHP local.
- El checklist de cada fase vive en `04-plan-migracion.md`.

## Decisiones cerradas

- Motor de BD: **MySQL 8.0** (coincide con el stack definido en `Agents.md`).
- TLS/HTTPS: reverse proxy externo al compose.
- Dev sin nginx: `artisan serve` es suficiente; nginx solo en producción.
- uid/gid: 1000/1000 fijos como valores por defecto del Dockerfile (coinciden con el usuario del host).
- Assets de prod: copiados en la imagen de nginx (multi-stage), no por volumen compartido.
- Tests contra BD separada (`bafoldanz_test`), nunca sobre la BD de desarrollo.
- Sin supervisor en el MVP (no hay tareas programadas; el scheduler, si llega, será un servicio aparte).
- Mailpit fuera del MVP (no hay correos en v1).

## Decisiones pendientes

- **Hosting de producción** (VPS propio, PaaS u otro): se asume VPS con Docker para el diseño; si cambia el destino, se revisará el compose de producción.
- Dominio y certificados (dependen del hosting).

