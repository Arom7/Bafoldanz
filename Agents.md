# Reglas del proyecto

Pagina web destinado al control de asistencia y pagos.

## Documentación
Lee primero `docs/README.md`. Contexto del negocio en `docs/01-contexto-negocio.md`; arquitectura MVC en `docs/02-arquitectura.md`; modelo de datos en `docs/03-modelo-datos.md`; plan de migración y avance por fases en `docs/04-plan-migracion.md`; despliegue Docker en `docs/05-despliegue.md`; diseño de la interfaz en `docs/06-diseno-ui.md`.

## Revisión del código
Todo el código que generes se entrega para revisión del usuario. **No ejecutes `git commit` ni `git push` en ningún caso.**

## Stack
- El stack actual simplemente trabaja con HTML pero se migrará a PHP 8.4, Laravel 13, MySQL con Eloquent (nota: Laravel 13 requiere PHP ≥ 8.4 vía Symfony 8)
- Autenticación: sesión web de Laravel (Blade). Sanctum queda diferenciado para una futura app móvil (no hay rutas API en v1)
- Roles y permisos: spatie/laravel-permission (v1: solo rol `admin` para las 2 directoras)
- PDF: barryvdh/laravel-dompdf
- Tests: PHPUnit (versión a verificar contra el Laravel instalado; el docs menciona PHPUnit 10). Formato: Laravel Pint
- Vite solo para assets; no hay frontend SPA
- Despliegue: Docker (ver `docs/05-despliegue.md`). **No hay PHP ni Composer en el host**: todo artisan/composer/test se ejecuta dentro de contenedores, vía `make ...` o `docker compose exec app ...`

## Comandos habituales
- `make up` / `make down`: levantar/bajar el stack local en Docker
- `make test`: ejecuta los tests (contra la BD de tests separada)
- `make pint` / `make pint-test`: corrige / comprueba el estilo (siempre dentro del contenedor)
- `php artisan route:list`: revisa las rutas (no hay rutas api en v1)
- `php artisan make:service Nombre`: crea un servicio en `app/Services` (no es nativo de Laravel: se agrega un comando/stub propio en la Fase 0; verificar al implementar)

## Convenciones
- PSR-12 vía Pint; tipado estricto en parámetros y retornos
- Nombres de clases y URLs en inglés (`/students`, `/payments`, ...); interfaz en español
- Zona horaria `America/La_Paz`; locale `es`; moneda Bs. (es-BO); diseño mobile-first (tokens en `docs/06-diseno-ui.md`)
- Tests en `tests/Feature` (endpoints) y `tests/Unit` (servicios), con el nombre `NombreTest.php`
- Todo endpoint nuevo o modificado debe tener un test
- Actualiza el README si cambias rutas

## Lo que NO debes hacer
- No instales dependencias nuevas sin avisar
- No edites migraciones ya existentes; crea una nueva
- No leas, muestres ni modifiques `.env` usa `.env.example`
- No pongas lógica de negocio ni consultas Eloquent en los controladores
- No ejecutes comandos destructivos sobre la base de datos (`migrate:fresh`, `db:wipe`, etc.); los tests usan una BD separada `bafoldanz_test`
- No ejecutes `git commit` ni `git push` (ver Revisión del código)
