# Modelo de datos — BAFOLDANZ Digital

Esquema de la base de datos (MySQL 8.0 + Eloquent). Complementa a `01-contexto-negocio.md` (reglas del negocio) y `02-arquitectura.md` (arquitectura).

> **Versión:** 2026-11-20 · Esquema reestructurado antes del MVP: alumno con un solo grupo mediante `registrations`, finanzas como **salidas** (sin cuentas ni transferencias) y anulación con motivo.
>
> **Alcance:** este esquema corresponde al MVP interno. El mockup «Ritmo Andino Glow» explora entidades que **no** están modeladas aquí (solicitudes/leads, danzas/elencos, salones, aforo, becas, currículo); se reservan para v1.1 y no deben agregarse sin actualizar antes `01-contexto-negocio.md`, `04-plan-migracion.md` y este documento.

## Convenciones

- Todas las PK/FK son `bigint unsigned`.
- Fechas y horas en zona horaria `America/La_Paz`; moneda **Bs.** (formato `es-BO`).
- `timestamps` = `created_at` + `updated_at`; `softDeletes` = `deleted_at` cuando se indica.
- La lógica de negocio y los cálculos viven en Services; la base de datos garantiza integridad estructural.
- Los FK del dominio usan `restrict` delete / `cascade` update. Las tablas pivote de Spatie usan `cascade` delete.

## Tablas

### Infraestructura de Laravel

#### users
Usuarios del sistema (las dos directoras con rol `admin` en v1).

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint unsigned PK | |
| name | string | |
| email | string unique | |
| email_verified_at | timestamp nullable | scaffold de Laravel |
| password | string | hash bcrypt |
| is_director | boolean default false | distingue a las 2 directoras (salidas y reparto 50/50) |
| remember_token | string(100) nullable | scaffold de Laravel |
| timestamps | | |

- Relaciones: `payments.received_by` → users (quién cobró), `movements.director_id` → users (quién realizó una salida), `payments/movements.voided_by` → users (quién anuló).
- Roles vía spatie (`model_has_roles`): rol `admin` en v1.

#### password_reset_tokens
Tabla estándar de Laravel para restablecimiento de contraseña. Se conserva aunque en v1 el restablecimiento se hace con `make admin`.

| Columna | Tipo | Notas |
|---|---|---|
| email | string PK | |
| token | string | |
| created_at | timestamp nullable | |

#### sessions
Sesiones de Laravel (driver `database`).

| Columna | Tipo | Notas |
|---|---|---|
| id | string PK | |
| user_id | bigint unsigned nullable | **solo índice**, sin FK |
| ip_address | string(45) nullable | |
| user_agent | text nullable | |
| payload | longtext | |
| last_activity | int | índice |

#### cache
Caché de Laravel (driver `database`).

| Columna | Tipo | Notas |
|---|---|---|
| key | string PK | |
| value | mediumText | |
| expiration | bigint | índice |

#### cache_locks
Bloqueos de caché (driver `database`).

| Columna | Tipo | Notas |
|---|---|---|
| key | string PK | |
| owner | string | |
| expiration | bigint | índice |

#### jobs
Cola de trabajos (driver `database`).

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint unsigned PK | |
| queue | string | índice |
| payload | longtext | |
| attempts | unsignedSmallInteger | |
| reserved_at | unsignedInteger nullable | |
| available_at | unsignedInteger | |
| created_at | unsignedInteger | |

#### job_batches
Lotes de trabajos en cola.

| Columna | Tipo | Notas |
|---|---|---|
| id | string PK | |
| name | string | |
| total_jobs | int | |
| pending_jobs | int | |
| failed_jobs | int | |
| failed_job_ids | longtext | |
| options | mediumText nullable | |
| cancelled_at | int nullable | |
| created_at | int | |
| finished_at | int nullable | |

#### failed_jobs
Trabajos fallidos.

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint unsigned PK | |
| uuid | string unique | |
| connection | string | |
| queue | string | |
| payload | longtext | |
| exception | longtext | |
| failed_at | timestamp | default `CURRENT_TIMESTAMP` |
| index | (connection, queue, failed_at) | |

### Roles y permisos (spatie)

Tablas estándar de `spatie/laravel-permission` (teams desactivado). En v1 solo el rol `admin`.

#### permissions
| Columna | Tipo | Notas |
|---|---|---|
| id | bigint unsigned PK | |
| name | string | |
| guard_name | string | |
| timestamps | | |
| unique | (name, guard_name) | |

#### roles
| Columna | Tipo | Notas |
|---|---|---|
| id | bigint unsigned PK | |
| name | string | |
| guard_name | string | |
| timestamps | | |
| unique | (name, guard_name) | |

#### model_has_permissions
| Columna | Tipo | Notas |
|---|---|---|
| permission_id | bigint unsigned FK → permissions | `cascade` delete |
| model_type | string | polimórfico (`App\Models\User`) |
| model_id | bigint unsigned | |
| primary | (permission_id, model_id, model_type) | |
| index | (model_id, model_type) | |

#### model_has_roles
| Columna | Tipo | Notas |
|---|---|---|
| role_id | bigint unsigned FK → roles | `cascade` delete |
| model_type | string | polimórfico (`App\Models\User`) |
| model_id | bigint unsigned | |
| primary | (role_id, model_id, model_type) | |
| index | (model_id, model_type) | |

#### role_has_permissions
| Columna | Tipo | Notas |
|---|---|---|
| permission_id | bigint unsigned FK → permissions | `cascade` delete |
| role_id | bigint unsigned FK → roles | `cascade` delete |
| primary | (permission_id, role_id) | |

### Dominio

#### branches
Sucursales de la academia. Datos por **seeders en v1**; CRUD administrable en v1.1.

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint unsigned PK | |
| name | string unique | Centro, Norte (seeders) |
| default_fee | decimal(10,2) | tarifa regular de la sucursal (Centro 120, Norte 60) |
| timestamps | | |

#### group_types
Tipos de grupo (categorías). Son **globales**, no pertenecen a una sucursal; el grupo concreto es el que combina tipo, sucursal y horario. Datos por **seeders en v1**; CRUD en v1.1.

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint unsigned PK | |
| name | string unique | Infantil, Juvenil Intermedio, Juvenil Avanzado (seeders) |
| timestamps | | |

#### groups
Grupo concreto: un tipo de grupo en una sucursal, con un horario. Datos por **seeders en v1**; CRUD en v1.1.

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint unsigned PK | |
| branch_id | bigint unsigned FK → branches | `restrict` delete / `cascade` update |
| group_type_id | bigint unsigned FK → group_types | `restrict` delete / `cascade` update |
| hour_start | time | hora de inicio |
| hour_end | time | hora de fin |
| is_active | boolean default true | grupo activo/inactivo |
| unique | (branch_id, group_type_id, hour_start, hour_end) | evita duplicar el mismo grupo |
| timestamps | | |

- No se modelan días de la semana: el grupo se identifica por sucursal, tipo y horario.
- Horarios de v1 (idénticos en Centro y Norte):

| Tipo de grupo | Horario |
|---|---|
| Infantil | 16:00 – 17:00 |
| Juvenil Intermedio | 17:00 – 18:00 |
| Juvenil Avanzado | 18:00 – 19:00 |

#### students
Alumnos ("bailarines" en la UI). No guarda sucursal ni grupo: ambos se obtienen por su inscripción.

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint unsigned PK | |
| name | string | nombre del alumno ("bailarín") |
| fee | decimal(10,2) | mensualidad vigente |
| payment_day | unsignedTinyInt | día del mes acordado para cobrar (1–28) |
| is_promo | boolean default false | promoción permanente (mensualidad personalizada) |
| active | boolean default true | retirado/reactivado |
| enrolled_at | date | fecha de alta o de la última reactivación; abre la ventana de cobro |
| retired_at | date nullable | fecha del último retiro; se limpia al reactivar |
| timestamps | | softDeletes |

- **Alta** = creación del alumno + su inscripción (`registrations`) en la misma transacción.
- En código, `Student::registration()` es la relación; `$student->group` y `$student->branch` son accesores que la atraviesan.
- **Retiro** = `active = false` + `retired_at` + `is_promo = false`; la inscripción se conserva (último grupo).
- **Reactivación** = `active = true` + `enrolled_at = hoy` + `retired_at = null`; se actualiza el grupo de la inscripción y se renegocian fee y promo.
- **Borrado administrativo** = softDeletes (`deleted_at`). Solo para eliminar un registro creado por error; no es lo mismo que retirar.
- La **ventana de cobro** va de `enrolled_at` a (si corresponde) `retired_at`: un mes solo es "pendiente" dentro de esa ventana.
- Fechas en zona horaria `America/La_Paz`.

#### registrations
Inscripción del alumno a un grupo. Un alumno tiene **una sola inscripción**; cambiar de grupo actualiza esta fila (sin historial).

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint unsigned PK | |
| student_id | bigint unsigned FK → students unique | una inscripción por alumno; `restrict` delete / `cascade` update |
| group_id | bigint unsigned FK → groups | grupo actual del alumno; `restrict` delete / `cascade` update |
| timestamps | | |

- La **sucursal del alumno** se deriva de `registration → group → branch`; no se guarda en `students`.
- Al cambiar de grupo se actualiza `group_id`; no se crea una fila nueva.
- Al retirar o dar de baja administrativa no se elimina la inscripción.

#### payments
Mensualidades cobradas.

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint unsigned PK | |
| student_id | bigint unsigned FK → students | `restrict` delete / `cascade` update |
| month | date | **decisión**: `DATE` con el primer día del mes; en la app se lee/escribe como `YYYY-MM` |
| amount | decimal(10,2) | al registrar = mensualidad vigente del alumno |
| method | enum(Efectivo, Pago por QR) | únicos en v1; tabla `payment_methods` en v1.1 |
| paid_at | date | fecha del cobro (hoy, en La Paz) |
| received_by | bigint unsigned FK → users | directora que cobró (**obligatorio**) |
| voided_at | datetime nullable | anulación del pago (auditoría); NULL = vigente |
| voided_by | bigint unsigned FK → users nullable | quién anuló |
| void_reason | string(255) nullable | motivo opcional de la anulación |
| timestamps | | |

- Índice `(student_id, month)` **sin unique**: la unicidad de "un pago vigente por alumno y mes" se valida en `StorePaymentRequest`/`PaymentService` contra pagos no anulados. Así se permite re-cobrar tras anular.
- **Anulación**: se registran `voided_at`, `voided_by` y `void_reason`; el mes vuelve a "Pendiente" y se puede volver a cobrar.

#### attendances
Asistencias. Un registro por alumno y día (update-or-create).

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint unsigned PK | |
| student_id | bigint unsigned FK → students | `restrict` delete / `cascade` update |
| date | date | índice |
| code | enum(P,F,J,R,H,S) | P presente, F falta, J justificada, R reposición, H feriado, S suspendida por academia |
| unique | (student_id, date) | permite update-or-create por día |
| timestamps | | |

#### movements
Movimientos de caja: **salidas** de dinero (por ejemplo, gastos o retiros de directoras). Todas restan del saldo.

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint unsigned PK | |
| concept | string | descripción/justificación de la salida |
| amount | decimal(10,2) | monto de la salida |
| director_id | bigint unsigned FK → users nullable | informativo: quién realizó la salida, si aplica (normalmente una directora) |
| date | date | fecha de la salida; índice |
| created_by | bigint unsigned FK → users | quién registró el movimiento |
| voided_at | datetime nullable | anulación (auditoría); NULL = vigente |
| voided_by | bigint unsigned FK → users nullable | quién anuló |
| void_reason | string(255) nullable | motivo opcional de la anulación |
| timestamps | | |

- **No hay tipos** (`gasto`, `retiro`, `transferencia`): el detalle se explica en `concept`.
- **No hay cuentas**: una sola caja general; no se modelan transferencias entre cuentas.
- `director_id` es **solo informativo** y no altera ningún cálculo.
- Un movimiento anulado no suma en ningún total.

## Relaciones (resumen)

```
branches     1─n groups              (sucursal del grupo)
group_types  1─n groups              (tipo del grupo)
groups       1─n registrations       (alumnos inscritos al grupo)
students     1─1 registrations       (un alumno, un grupo; unique student_id)
branches     1─n groups 1─n registrations
students     1─n payments            (index: student_id + month; unicidad de pagos vigentes en el Service)
students     1─n attendances         (unique: student_id + date)
users        1─n payments            (received_by, voided_by)
users        1─n movements           (director_id, created_by, voided_by)
```

## Índices y constraints clave

- `groups`: unique `(branch_id, group_type_id, hour_start, hour_end)`.
- `registrations`: unique `student_id`; índice del FK `group_id`.
- `payments`: índice `(student_id, month)` **sin unique** — la unicidad de "un pago vigente por alumno y mes" se valida en el Service contra registros no anulados. Índice adicional por `month`.
- `attendances`: unique `(student_id, date)` e índice por `date`.
- `movements`: índice por `date`.
- `branches`, `group_types`: `name` unique.
- `users`: `email` unique.
- `cache`/`cache_locks`: `key` PK.
- `sessions`: índices por `user_id` y `last_activity`.
- `failed_jobs`: índice `(connection, queue, failed_at)`.

## Reglas del alumno y su inscripción

1. **Alta:** crear `student` + `registration` en una transacción. `enrolled_at` = fecha de alta; `registration.group_id` = grupo elegido.
2. **Edición de datos personales** (nombre, mensualidad, día de pago, promo): se actualiza `students`.
3. **Cambio de grupo:** se actualiza `registration.group_id`. No se guarda historial de grupos.
4. **Retiro:** `active = false`, `retired_at = hoy`, `is_promo = false`; la inscripción se conserva.
5. **Reactivación:** `active = true`, `enrolled_at = hoy`, `retired_at = null`; se actualiza el grupo de la inscripción y se renegocian mensualidad y promo.
6. **Borrado administrativo (soft delete):** no elimina la inscripción.
7. **Invariantes:** un alumno tiene exactamente una inscripción; la sucursal del alumno es la del grupo de su inscripción.

## Reglas de cálculo (deben vivir en Services)

Filtro de periodo único (decisión 2026-11): selector con atajos hoy / semana / mes / rango personalizado; **default = mes actual** (en La Paz). Se aplica a los totales de ingresos, salidas y saldo; el "Cierre del mes" usa su propio rango (mes calendario actual). Todo cálculo usa **solo registros vigentes** (no anulados).

- **Pendientes del mes seleccionado**: alumnos activos sin pago vigente cuyo `month` = mes seleccionado, **siempre que el mes esté dentro de la ventana de cobro** (`enrolled_at ≤ fin de mes` y, si `retired_at` existe, `retired_at ≥ inicio de mes`).
- **Ingresos**: suma de `payments.amount` vigentes **dentro del rango** (por `month` del pago; si no hay filtro explícito, históricos).
- **Salidas**: suma de `movements.amount` vigentes **dentro del rango** (por `date` del movimiento).
- **Saldo** = ingresos − salidas del rango.
- **Reparto 50/50 ("Cierre del mes")**: cálculo informativo, **no registra salidas**. Rango fijo: mes calendario actual:
  - `disponible = pagos vigentes cuyo month = mes calendario actual − salidas vigentes con date del mes calendario actual − reserva`
  - `reserva` = valor ingresado en la vista (default 0, no persistido en v1); cada directora recibe `disponible / 2`; si `disponible < 0`, no hay reparto.
  - La caja chica reservada es "dinero reservado", no una salida.
- **Anulación**: pagos y movimientos anulados (`voided_at` no nulo) no suman en ningún total.
- Los pagos **no** generan movimientos de caja.
- Las fechas se manejan en `America/La_Paz`; `month` se deriva de la fecha local, nunca de UTC.

## Seeders (estrategia)

Tres seeders con responsabilidades separadas; `DatabaseSeeder` invoca catálogo + directoras.

| Seeder | Qué crea | Dónde corre |
|---|---|---|
| `CatalogSeeder` | Sucursales (Centro fee 120, Norte fee 60), tipos de grupo (Infantil, Juvenil Intermedio, Juvenil Avanzado) y los grupos de cada sucursal con su horario real (16–17, 17–18 y 18–19). **No crea cuentas** | Siempre (dev y producción) |
| `DirectorSeeder` | Las 2 directoras (`is_director = true`, rol `admin`) con credenciales tomadas de `.env` (`DIRECTORA_1_EMAIL`, `DIRECTORA_1_PASSWORD`, ...) — sin valores por defecto en producción | Siempre, con credenciales por entorno |
| `DemoStudentsSeeder` | 3 alumnos de ejemplo (Valeria, Camila, Sofía) y su **inscripción** a un grupo; cada uno con su mensualidad, día de pago y promo | **Solo desarrollo** |

- Horarios de los grupos: los tres tipos se repiten en Centro y Norte con el mismo horario (Infantil 16:00–17:00, Juvenil Intermedio 17:00–18:00, Juvenil Avanzado 18:00–19:00). El seeder los deja explícitos y ajustables.
- `DemoStudentsSeeder` **no corre en producción**; los alumnos reales se cargan desde la UI.
- `make setup` ejecuta `migrate --seed` en desarrollo; en producción se corre `migrate` y luego los seeders deseados de forma explícita.
