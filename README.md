# BAFOLDANZ Digital

Aplicación web para el control de asistencia y pagos del **Ballet Folclórico de Danza BAFOLDANZ**.
Stack: PHP 8.4 + Laravel 13 + Blade + MySQL (Eloquent), diseño mobile-first con tema oscuro «Ritmo Andino Glow».

## Documentación

- Contexto del negocio: [`docs/01-contexto-negocio.md`](docs/01-contexto-negocio.md)
- Arquitectura: [`docs/02-arquitectura.md`](docs/02-arquitectura.md)
- Modelo de datos: [`docs/03-modelo-datos.md`](docs/03-modelo-datos.md)
- Plan de migración y avance: [`docs/04-plan-migracion.md`](docs/04-plan-migracion.md)
- Despliegue: [`docs/05-despliegue.md`](docs/05-despliegue.md)
- Diseño UI: [`docs/06-diseno-ui.md`](docs/06-diseno-ui.md)
- Revisión del mockup: [`docs/07-revision-mockup.md`](docs/07-revision-mockup.md)

## Entorno local

Todo PHP/Composer corre dentro de Docker (nunca en el host):

```bash
make setup   # primer arranque: .env, build, composer install, key, migrate --seed
make up      # levanta el stack
make test    # PHPUnit contra la BD de tests
make routes  # lista de rutas
make shell   # shell dentro del contenedor app
```

La aplicación queda en `http://localhost:8000`.

## Rutas implementadas (v1)

| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/` | Redirige a `/students` (inicio operativo durante F2a) |
| `GET` | `/students` | Listado de alumnos con filtros y paginación server-side |
| `GET` | `/students/create` | Formulario de alta de alumno |
| `POST` | `/students` | Crea el alumno y su inscripción en una transacción |

### Filtros del listado (`GET /students`)

- `search` — búsqueda por nombre
- `status` — `active` \| `retired`
- `branch_id` — sucursal
- `group_type_id` — tipo de grupo
- `page` — página del paginador

Reglas: `per_page` fijo en **10**, orden por defecto **`name ASC`** y los filtros se conservan entre páginas.

## Convenciones

- URLs, clases y métodos en inglés; interfaz en español.
- Filtros, búsqueda, orden y paginación **siempre en backend**.
- Lógica de negocio en `app/Services`; controladores delgados.
- El código se entrega para revisión: **no se ejecutan `git commit` ni `git push`**.
