# Plan de migración — BAFOLDANZ Digital → Laravel MVC

El proyecto migra de un prototipo web estático (retirado del repositorio) a un proyecto Laravel MVC nuevo, y entrega un **primer MVP en producción**.

## Decisiones tomadas

Acordadas con el dueño del proyecto:

1. **Auth**: sesión web + Blade. Sanctum queda diferenciado para una futura app móvil.
2. **Roles v1**: solo las **2 directoras** (rol `admin`, acceso completo, flag `is_director`). spatie queda instalado para agregar asistentes después.
3. **Catálogos** (sucursales, tipos de grupo y grupos): datos cargados con **seeders en v1**; la UI de administración CRUD va en v1.1.
4. **Producción**: destino **aún no definido** (pendiente). El compose de producción se diseña genérico (VPS con Docker es el escenario asumido).
5. **Convenciones**: URLs, clases y métodos en **inglés**; interfaz en español. Zona horaria `America/La_Paz`, locale `es`, moneda Bs. Diseño mobile-first con el sistema **«Ritmo Andino Glow»** (tokens en `06-diseno-ui.md`).
6. **Retiro de alumnos y pendientes**: retiro con `active` + `retired_at`; **softDeletes solo para borrado administrativo**; se agrega `enrolled_at` (alta o última reactivación). Un mes es "pendiente" solo dentro de la ventana de cobro (`enrolled_at` → `retired_at`).
7. **Filtros de Finanzas**: rango de fechas único con atajos (hoy / semana / mes / rango personalizado), default mes actual; aplica a ingresos, salidas y saldo. El "Cierre del mes" usa el mes calendario actual.
8. **Grupos por sucursal**: un grupo combina un tipo de grupo, una sucursal y un horario. El alumno se inscribe en un solo grupo mediante `registrations`; su sucursal se deriva del grupo y el formulario puede filtrar por sucursal como ayuda visual.
9. **`payments.month`**: columna `DATE` con el primer día del mes; en la app se usa como `YYYY-MM`. Unicidad alumno+mes validada en el Service contra pagos no anulados.
10. **Ubicación del proyecto**: el proyecto Laravel se crea **en esta misma carpeta** (scaffold en un directorio temporal dentro de Docker y se mueve aquí, conservando `docs/` y `Agents.md`).
11. **Esquema reestructurado antes del MVP**: alumno con una sola inscripción, finanzas como salidas sin cuentas ni transferencias, y anulación con motivo. Como no hay datos reales, no se edita las migraciones ni se modifica la base de datos, solamente se puede hacer cambios de forma manual.
12. **Mockup como fuente visual (2026-11-23)**: se adopta el proyecto Stitch **“Plataforma Web Bafoldanz Academia”** («Ritmo Andino Glow») como referencia de diseño y UX. El rediseño se aplica al MVP interno; la landing pública, solicitudes/leads, danzas/elencos, salones y métricas académicas quedan **fuera de v1** y se planifican como v1.1. El mockup no redefine reglas de negocio ni el modelo de datos. Detalle en `06-diseno-ui.md` y `07-revision-mockup.md`.

Del análisis funcional previo (independiente del mockup visual):

- Ingresos del dashboard son **históricos**; los totales de finanzas usan el filtro de rango (decisión 7). Reglas exactas en `03-modelo-datos.md`.
- El MVP incluye **editar alumno** y **anular pagos y movimientos** (con auditoría).
- Se agrega base de datos de **tests separada** (ver `05-despliegue.md`).

## Definición de MVP (criterio de aceptación)

El MVP está entregado cuando, en producción, con datos reales y backup diario verificado:

- [ ] Las 2 directoras inician sesión (y solo ellas pueden).
- [ ] Se registran, editan, retiran y reactivan alumnos (con grupo, mensualidad, día de pago y promo).
- [ ] Se cobran y **anulan** mensualidades; un solo pago vigente por alumno y mes.
- [ ] El dashboard muestra pagados/pendientes del mes seleccionado y alertas de pendientes.
- [ ] Se registra asistencia (6 marcas, sobrescritura por día).
- [ ] Se registran **salidas de caja** (concepto, monto, fecha y directora opcional) y se **anulan** con auditoría y motivo.
- [ ] Finanzas muestra ingresos/salidas/saldo con filtro de rango y la simulación de reparto 50/50 con reserva.
- [ ] La app es usable en navegadores como Chrome, Safari y Brave (mobile-first, tema oscuro «Ritmo Andino Glow», navegación inferior).
- [ ] `make test` y `make pint-test` en verde; README con rutas.

## Fase 0 — Docker mínimo + scaffold

> Docker va **antes** del scaffold porque no hay PHP/Composer en el host: el proyecto Laravel se crea dentro de un contenedor.

> **Nota (Fase 0):** el stack quedó en **PHP 8.4 + Laravel 13**: los documentos decían PHP 8.3, pero Laravel 13 depende de Symfony 8, que exige PHP ≥ 8.4.1. Decisión del dueño: mantener Laravel 13 y subir la versión de PHP.

- [x] `docker/Dockerfile` (stage `dev`, php:8.4-fpm-alpine — Laravel 13 exige PHP ≥ 8.4 vía Symfony 8, decisión del dueño —, usuario `app` uid=1000/gid=1000) + `docker-compose.yml` local (app + mysql:8.0) + `.env.example` + Makefile básico.
- [x] `composer create-project laravel/laravel` ejecutado **dentro de un contenedor temporal** (imagen `composer:2` corriendo como uid 1000) en un directorio temporal, y luego **se mueve todo a esta carpeta**, conservando `docs/` y `Agents.md` en la raíz.
- [x] `git init` en esta carpeta y `.gitignore` que ignore el scaffold temporal.
- [x] Verificación: `make setup && make up` y la ruta de bienvenida de Laravel sirviendo desde el contenedor como uid 1000.

## Fase 1 — Dominio (migraciones, modelos, seeders)

- [x] Migraciones y modelos según `03-modelo-datos.md`. *Nota: el esquema se reestructuró antes del MVP (alumno con una inscripción a grupo, `group_types`, salidas de caja sin cuentas). Como no hay datos reales, el dueño autorizó editar las migraciones existentes; el esquema final está en `03-modelo-datos.md`.*
- [x] Modelos con relaciones, casts y scopes (`scopeActive`, `scopePendingForMonth`, ...).
- [x] Seeders según la estrategia de `03-modelo-datos.md`: `CatalogSeeder` (siempre), `DirectorSeeder` (credenciales por `.env`), `DemoStudentsSeeder` (solo dev); `DatabaseSeeder` llama al catálogo **y a DirectorSeeder** (decisión del dueño).
- [x] Factories para tests.
- [x] **Base de datos de tests separada** (`.env.testing`, `phpunit.xml`) — ver `05-despliegue.md`. *(BD `bafoldanz_test` creada con `make test-db`; valores de conexión en `phpunit.xml`, `.env.testing` no se versiona).*
- [x] Configuración regional: `APP_TIMEZONE=America/La_Paz`, locale `es`. *(Ya presente en `.env.example`; verificado vía `artisan about`).*

## Fase 2 — Módulos core (incrementos separados, cada uno demostrable)

Cada módulo = FormRequest(s) + `app/Services/…` + Controller + rutas web + vistas Blade + **tests de la fase** (regla: test por endpoint).

**F2a — Alumnos** (`StudentService`) — dividido en entregas cortas

Microdecisiones v1:
- `per_page` fijo en **10** (paginación server-side).
- Orden por defecto del listado: **`name ASC`**.
- Punto de entrada operativo durante F2a: `/` redirige temporalmente a `/students`.

**F2a.1 — Listado server-side** ✅
- [x] `GET /students` con filtros backend: `search`, `status`, `branch_id`, `group_type_id`.
- [x] Paginación backend fija en 10 y orden `name ASC`.
- [x] Vista de listado simple (sin pulido visual final).
- *Aceptación*: los filtros y la paginación se resuelven en backend y la URL conserva los filtros.

**F2a.2 — Alta de alumno** ✅
- [x] `GET /students/create` + `POST /students`.
- [x] Alta = `student` + `registration` en una transacción; `enrolled_at = hoy`; `active = true`.
- *Aceptación*: se crea un alumno real con su grupo y aparece en el listado.

**F2a.3 — Edición**
- [ ] `GET /students/{id}/edit` + `PUT /students/{id}`.
- [ ] Cambio de grupo actualiza `registrations.group_id` (sin historial).
- *Aceptación*: se editan datos y se cambia de grupo sin duplicar inscripciones.

**F2a.4 — Retiro y reactivación**
- [ ] `PATCH /students/{id}/toggle` con `action=retire|reactivate`.
- [ ] Retiro: `active=false`, `is_promo=false`, `retired_at=hoy`.
- [ ] Reactivación: `active=true`, `enrolled_at=hoy`, `retired_at=null`; exige nueva mensualidad y actualiza grupo.
- *Aceptación*: retirar y reactivar un alumno real, con la nueva mensualidad.

**F2a.5 — Rediseño visual «Ritmo Andino Glow»**
- [ ] Layout base mobile-first: variables CSS, fuentes Montserrat/Plus Jakarta Sans, Material Symbols, header fijo y nav inferior de 5 tabs.
- [ ] Componentes UI reutilizables: botones, inputs, selects, cards, stats, pills, toggle, toasts, modales, estados vacíos y paginación.
- [ ] Aplicar el sistema al listado y alta de bailarines (referencias en `06-diseno-ui.md` §5); el dashboard se alinea al construirse en F2c.
- [ ] JS modular: autocompletar mensualidad por sucursal del grupo, filtrar grupos, toggle de promoción, toasts y confirmaciones.
- *Aceptación*: flujo usable a 390 px, con la identidad visual nueva, sin datos placeholder y con la mensualidad autocompletada al elegir grupo.

**F2a.6 — Tests de la fase**
- [ ] Test Feature por endpoint (listado, alta, edición, retiro/reactivación).
- [ ] Test de filtros y paginación server-side.
- *Aceptación*: `make test` verde.

**F2b — Asistencia** (`AttendanceService`)
- [ ] Registrar/sobrescribir por alumno+fecha (update-or-create), listar últimas 30.
- *Aceptación*: registrar la asistencia de una clase y corregirla el mismo día.

**F2c — Pagos + Dashboard** (`PaymentService`) — mayor valor de negocio, va primero entre los módulos operativos
- [ ] Registrar mensualidad (monto = mensualidad vigente), unicidad alumno+mes sobre pagos vigentes, estado Pagado/Pendiente por mes seleccionado (`?month=`), historial, directora que cobra obligatoria.
- [ ] **Anular pago** (auditoría `voided_at`/`voided_by` + motivo opcional); tras anular el mes vuelve a estar pendiente.
- [ ] Dashboard: stats + alertas de pendientes del mes seleccionado (respetando la ventana de cobro), con el sistema visual de `06-diseno-ui.md`.
- *Aceptación*: cobrar la mensualidad de un alumno real y verla reflejada en pendientes/dashboard; anular y volver a cobrar el mismo mes.

**F2d — Finanzas** (`FinanceService`)
- [ ] Registrar **salidas de caja** (concepto, monto, fecha y directora opcional).
- [ ] **Anular movimiento** (auditoría `voided_at`/`voided_by` + motivo opcional).
- [ ] Totales de ingresos, salidas y saldo con filtro de rango (hoy/semana/mes/rango) + reparto 50/50 del mes calendario con reserva (simulación, no persistida).
- *Aceptación*: registrar una salida y verla descontar del disponible del reparto; cambiar el rango y ver los totales ajustarse.

## Fase 3 — Auth y config base

- [ ] Login/logout con Blade (scaffolding manual; Breeze opcional). Sin registro público.
- [ ] Rol `admin` vía spatie + middleware en todas las rutas de gestión.
- [ ] Layout Blade base mobile-first según `06-diseno-ui.md` (tema oscuro, header con logo/marca, nav inferior, tarjetas, pills, toasts) y login; rutas `/`, `/students`, `/attendance`, `/payments`, `/finance`.
- [ ] Vite con el JS mínimo: **autocompletar la mensualidad al elegir el grupo según su sucursal** y mantener la selección en los selects.

## Fase 4 — Verificación (endurecimiento)

- [ ] Repaso de cobertura: todo endpoint con su test Feature; Unit para `FinanceService` y `PaymentService` (reparto, unicidad, anulación, ventanas de cobro).
- [ ] Pint (PSR-12) sin errores; `make test` verde **contra la BD de tests**.
- [ ] README con rutas actualizado.

## Fase 5 — Producción y puesta en marcha

- [ ] Completar la dockerización de producción (`docker-compose.prod.yml`, nginx **con assets de Vite**, entrypoint con `migrate --force` + caches) — detalle en `05-despliegue.md`.
- [ ] **Backups** de MySQL (dump programado, retención, procedimiento de restauración probado).
- [ ] Definir hosting de producción (pendiente) y TLS.
- [ ] Crear las **2 directoras reales** (comando/seeder con credenciales por `.env`; `DemoStudentsSeeder` no corre en producción).
- [ ] Cargar catálogos reales (sucursales, tipos de grupo y grupos) y alumnos reales desde la UI.
- [ ] Prueba de humo en iPhone + validación final con las directoras (criterios del MVP).

## Fase 6 (Puntos de mejora posteriores al primer MVP)

- [ ] PDFs con dompdf: recibo de pago, resumen mensual de pendientes, estado de caja.
- [ ] CRUD administrable de catálogos (`branches`, `group_types`, `groups`) con UI propia.
- [ ] Exportaciones (incluido respaldo JSON), historial de tarifas, configuración persistente del % de reparto y de la reserva.
- [ ] **Landing pública** con danzas/elencos, propuesta de valor y formulario de clase de prueba (con consentimiento del tutor).
- [ ] **Solicitudes/leads**: bandeja, estados, contacto por WhatsApp y conversión a alumno.
- [ ] **Catálogo de danzas/elencos** y relación con grupos/sucursales.
- [ ] **Panel ampliado** del mockup: salones en vivo, aforo, asistencia semanal, avance curricular, becas y reportes docentes.
- [ ] Revisar el alcance y el modelo de datos antes de cualquiera de estos módulos; el mockup no es especificación funcional.

## Estimación orientativa

| Fase | Tamaño relativo |
|---|---|
| F0 Docker + scaffold | S |
| F1 Dominio | M |
| F2a.1 Listado server-side | S |
| F2a.2 Alta de alumno | S |
| F2a.3 Edición | S |
| F2a.4 Retiro y reactivación | S |
| F2a.5 Rediseño visual | S–M |
| F2a.6 Tests de la fase | S |
| F2b Asistencia | S |
| F2c Pagos + Dashboard | M |
| F2d Finanzas | M |
| F3 Auth + config | S–M |
| F4 Verificación | S |
| F5 Producción y puesta en marcha | M (depende del hosting pendiente) |

## Cómo empezar una sesión de trabajo

1. Leer `Agents.md` (reglas) y estos documentos (`docs/`). Para cualquier trabajo de interfaz, leer `06-diseno-ui.md` y `07-revision-mockup.md`.
2. Consultar este archivo para saber en qué fase se está y marcar el avance.
3. Todo se ejecuta vía `make ...` (contenedores, uid 1000); nunca PHP/Composer en el host.
4. **Todo el código se entrega para revisión del usuario: nunca ejecutar `git commit` ni `git push`** (regla en `Agents.md`).
