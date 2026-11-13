# Modelo de datos — BAFOLDANZ Digital

Esquema inicial de la base de datos (MySQL + Eloquent). Complementa a `01-contexto-negocio.md` (reglas del negocio) y `02-arquitectura.md` (arquitectura).

## Tablas

### users
Usuarios del sistema (las dos directoras con rol `admin` en v1).

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| name | string | |
| email | string unique | |
| password | string | hash |
| is_director | boolean default false | distingue a las 2 directoras (select de retiros, reparto 50/50) |
| timestamps | | |

- Relaciones: `payments.received_by` → users (quién cobró), `movements.director_id` → users (quién retiró), `payments/movements.voided_by` → users (quién anuló).
- Roles vía spatie (`model_has_roles`): rol `admin` en v1.

### branches
Sucursales de la academia. Datos por **seeders en v1**; CRUD administrable en v1.1.

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| name | string unique | Centro, Norte (seeders) |
| default_fee | decimal(10,2) | tarifa regular de la sucursal (Centro 120, Norte 60) |
| timestamps | | |

### groups
Grupos de alumnos, pertenecen a una sucursal (decisión 2026-11). Datos por **seeders en v1**; CRUD en v1.1.

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| branch_id | FK → branches | |
| name | string | Infantil, Juvenil Intermedio, Juvenil Avanzado (seeders) |
| unique | (branch_id, name) | |
| timestamps | | |

- El formulario de alumno elige primero la sucursal y filtra los grupos de esa sucursal.
- Los seeders deben indicar explícitamente qué grupo pertenece a qué sucursal.

### students
Alumnos ("bailarines" en la UI).

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| branch_id | FK → branches | sucursal del alumno |
| group_id | FK → groups | **validación**: el grupo debe pertenecer a la sucursal del alumno |
| fee | decimal(10,2) | mensualidad vigente |
| payment_day | unsignedTinyInt | día del mes acordado para cobrar (1–28) |
| is_promo | boolean | promoción permanente (fee personalizado) |
| active | boolean | retirar/reactivar |
| enrolled_at | date | fecha de alta; en cada reactivación pasa a ser la fecha de esa reactivación |
| retired_at | date nullable | fecha del último retiro; se limpia al reactivar |
| timestamps | | softDeletes |

Semántica de los tres mecanismos (no se solapan):
- **Retiro** = `active = false` + `retired_at` + `is_promo = false` (es una baja lógica: los datos se conservan).
- **Reactivación** = `active = true` + `enrolled_at = hoy` + `retired_at = null` (fee y promo se renegocian en el formulario, que exige fee).
- **Borrado administrativo** = softDeletes (`deleted_at`). Solo para eliminar un registro creado por error; no es lo mismo que retirar.
- La **ventana de cobro** va de `enrolled_at` a (si corresponde) `retired_at`: un mes solo es "pendiente" dentro de esa ventana.
- Fechas (`timestamps`, `enrolled_at`, `retired_at`) en zona horaria `America/La_Paz`.

### payments
Mensualidades cobradas.

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| student_id | FK → students | |
| month | date | **decisión**: columnas `DATE`; se guarda el **primer día del mes** y en la app se lee/escribe como `YYYY-MM` (así se ordena y agrupa correctamente por mes) |
| amount | decimal(10,2) | al registrar = fee vigente del alumno |
| method | string | `Efectivo` / `QR bancario` (únicos en v1; enum o tabla `payment_methods` en v1.1) |
| paid_at | date | fecha del cobro (hoy, en La Paz) |
| received_by | FK → users | directora que cobró (**obligatorio**) |
| voided_at | datetime nullable | anulación del pago (auditoría); NULL = vigente |
| voided_by | FK → users nullable | quién anuló |
| index | (student_id, month) | no unique — ver regla de unicidad abajo |
| timestamps | | |

- **Unicidad alumno+mes**: se valida en `StorePaymentRequest`/`PaymentService` contra pagos **no anulados** (no es constraint de BD). Así se permite re-cobrar un mes después de anular el pago equivocado. Detalle en "Índices y constraints".

### attendances
Asistencias. Un registro por alumno y día (update-or-create).

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| student_id | FK → students | |
| date | date | |
| code | enum(P,F,J,R,H,S) | P presente, F falta injustificada, J falta justificada, R reposición, H feriado, S suspendida por academia |
| unique | (student_id, date) | |
| timestamps | | |

### accounts
Cuentas de caja. Datos por **seeders en v1**; CRUD en v1.1.

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| name | string unique | **Caja Efectivo**, **Banco QR** (nombres canónicos, seeders) |
| timestamps | | |

### movements
Movimientos de caja.

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| type | enum(gasto, retiro, transferencia) | |
| concept | string | descripción |
| amount | decimal(10,2) | |
| source_account_id | FK → accounts | cuenta de origen (para gasto/transferencia) |
| destination_account_id | FK → accounts nullable | solo para transferencia (origen ≠ destino) |
| director_id | FK → users nullable | solo para retiro; debe ser un usuario con `is_director = true` |
| date | date | |
| created_by | FK → users | quién registró el movimiento |
| voided_at | datetime nullable | anulación (auditoría); NULL = vigente |
| voided_by | FK → users nullable | quién anuló |
| timestamps | | |

Reglas:
- `gasto`: requiere `source_account_id`.
- `retiro`: requiere `source_account_id` y `director_id` (con `is_director = true`).
- `transferencia`: requiere `source_account_id` y `destination_account_id`, y deben ser distintas.
- **Aclaración**: los totales y el saldo no se calculan por cuenta; una transferencia **no altera** el saldo total en v1. Las cuentas existen como datos de contexto (y para la conciliación por cuenta de v1.1).
- Los pagos **no** generan movimientos.

### (spatie) — model_has_roles, roles, permissions
Tablas estándar de `spatie/laravel-permission`. En v1 solo el rol `admin`.

## Relaciones (resumen)

```
branches 1─n groups 1─n students      (validación: student.branch_id = group.branch_id)
students 1─n payments        (index: student_id + month; unicidad de pagos vigentes en el Service)
students 1─n attendances     (unique: student_id + date)
users    1─n payments        (received_by, voided_by)
users    1─n movements       (director_id, created_by, voided_by)
accounts 1─n movements       (source / destination)
```

## Índices y constraints clave

- `payments`: índice `(student_id, month)` **sin unique** — la unicidad de "un pago vigente por alumno y mes" se valida en el Service contra registros no anulados (así se permite re-cobrar tras anular). Alternativa descartada: unique de BD con columna generada, innecesaria para este volumen.
- `attendances`: unique `(student_id, date)` — permite update-or-create por día.
- `groups`: unique `(branch_id, name)`.
- `accounts`, `branches`: `name` unique.

## Reglas de cálculo (deben vivir en Services)

Filtro de periodo único (decisión 2026-11): selector con atajos hoy / semana / mes / rango personalizado; **default = mes actual** (en La Paz). Se aplica a los cuatro totales; el "Cierre del mes" usa su propio rango (mes calendario actual). Todo cálculo usa **solo registros vigentes** (no anulados).

- **Pendientes del mes seleccionado**: alumnos activos sin pago vigente cuyo `month` = mes seleccionado, **siempre que el mes esté dentro de la ventana de cobro** (`enrolled_at ≤ fin de mes` y, si `retired_at` existe, `retired_at ≥ inicio de mes`).
- **Ingresos**: suma de `payments.amount` vigentes **dentro del rango** (por `month` del pago; si no hay filtro explícito, históricos).
- **Gastos**: suma de `movements` vigentes con `type = gasto` **dentro del rango** (por `date` del movimiento).
- **Retiros**: suma de `movements` vigentes con `type = retiro` **dentro del rango** (por `date`).
- **Saldo** = ingresos − gastos − retiros del rango.
- **Reparto 50/50 ("Cierre del mes")**: cálculo informativo, **no registra retiros**. Rango fijo: mes calendario actual:
  - `disponible = pagos vigentes cuyo month = mes calendario actual − movimientos vigentes type=gasto con date del mes calendario actual − reserva`
  - `reserva` = valor ingresado en la vista (default 0, no persistido en v1); cada directora recibe `disponible / 2`; si `disponible < 0`, no hay reparto.
- Las fechas se manejan en `America/La_Paz`; `month` se deriva de la fecha local, nunca de UTC.

## Seeders (estrategia)

Tres seeders con responsabilidades separadas; `DatabaseSeeder` **solo** invoca al catálogo:

| Seeder | Qué crea | Dónde corre |
|---|---|---|
| `CatalogSeeder` | Sucursales (Centro fee 120, Norte fee 60) con sus grupos: Infantil, Juvenil Intermedio y Juvenil Avanzado en **Centro** (y los mismos grupos también en **Norte** — los tres en cada sucursal, ajustable), cuentas **Caja Efectivo** y **Banco QR** | Siempre (dev y producción) |
| `DirectorSeeder` | Las 2 directoras (`is_director = true`, rol `admin`) con credenciales tomadas de variables de `.env` (`DIRECTORA_1_EMAIL`, `DIRECTORA_1_PASSWORD`, ...) — **sin valores por defecto en producción** | Siempre, con credenciales por entorno |
| `DemoStudentsSeeder` | 3 alumnos de ejemplo: Valeria (Centro, Juvenil Intermedio, 120, día 10), Camila (Centro, Infantil, 90 con promo, día 15), Sofía (Norte, Juvenil Avanzado, 60, día 5) | **Solo desarrollo**; los alumnos reales se cargan desde la UI |

- `make setup` ejecuta `migrate --seed` en **desarrollo**; en producción se corre `migrate` y luego los seeders deseados de forma explícita (`php artisan db:seed --class=CatalogSeeder`).
- `DemoStudentsSeeder` **no corre en producción**.
