# Plan de migración — BAFOLDANZ Digital → Laravel MVC

El proyecto migra de un prototipo web estático (retirado del repositorio) a un proyecto Laravel MVC nuevo, y entrega un **primer MVP en producción**.

## Decisiones tomadas

Acordadas con el dueño del proyecto:

1. **Auth**: sesión web + Blade. Sanctum queda diferenciado para una futura app móvil.
2. **Roles v1**: solo las **2 directoras** (rol `admin`, acceso completo, flag `is_director`). spatie queda instalado para agregar asistentes después.
3. **Catálogos** (sucursales, grupos, cuentas): datos cargados con **seeders en v1**; la UI de administración CRUD va en v1.1.
4. **Producción**: destino **aún no definido** (pendiente). El compose de producción se diseña genérico (VPS con Docker es el escenario asumido).
5. **Convenciones**: URLs, clases y métodos en **inglés**; interfaz en español. Zona horaria `America/La_Paz`, locale `es`, moneda Bs. Diseño mobile-first (tokens en `06-diseno-ui.md`).
6. **Retiro de alumnos y pendientes**: retiro con `active` + `retired_at`; **softDeletes solo para borrado administrativo**; se agrega `enrolled_at` (alta o última reactivación). Un mes es "pendiente" solo dentro de la ventana de cobro (`enrolled_at` → `retired_at`).
7. **Filtros de Finanzas**: rango de fechas único con atajos (hoy / semana / mes / rango personalizado), default mes actual; aplica a ingresos, gastos, retiros y saldo. El "Cierre del mes" usa el mes calendario actual.
8. **Grupos por sucursal**: cada grupo pertenece a una sucursal; el formulario filtra los grupos por sucursal y el backend valida la pertenencia.
9. **`payments.month`**: columna `DATE` con el primer día del mes; en la app se usa como `YYYY-MM`. Unicidad alumno+mes validada en el Service contra pagos no anulados.
10. **Ubicación del proyecto**: el proyecto Laravel se crea **en esta misma carpeta** (scaffold en un directorio temporal dentro de Docker y se mueve aquí, conservando `docs/` y `Agents.md`).

Del análisis de la revisión:

- Ingresos del dashboard son **históricos**; los totales de finanzas usan el filtro de rango (decisión 7). Reglas exactas en `03-modelo-datos.md`.
- El MVP incluye **editar alumno** y **anular pagos y movimientos** (con auditoría).
- Se agrega base de datos de **tests separada** (ver `05-despliegue.md`).

## Definición de MVP (criterio de aceptación)

El MVP está entregado cuando, en producción, con datos reales y backup diario verificado:

- [ ] Las 2 directoras inician sesión (y solo ellas pueden).
- [ ] Se registran, editan, retiran y reactivan alumnos (con sucursal, grupo, fee, día de pago, promo).
- [ ] Se cobran y **anulan** mensualidades; un solo pago vigente por alumno y mes.
- [ ] El dashboard muestra pagados/pendientes del mes seleccionado y alertas de pendientes.
- [ ] Se registra asistencia (6 marcas, sobrescritura por día).
- [ ] Se registran gastos, retiros de directora y transferencias (con sus validaciones) y se **anulan** con auditoría.
- [ ] Finanzas muestra ingresos/gastos/retiros/saldo con filtro de rango y la simulación de reparto 50/50 con reserva.
- [ ] La app es usable en navegadores como Chrome, Safari y Brave (mobile-first, tabs inferiores).
- [ ] `make test` y `make pint-test` en verde; README con rutas.

## Fase 0 — Docker mínimo + scaffold

> Docker va **antes** del scaffold porque no hay PHP/Composer en el host: el proyecto Laravel se crea dentro de un contenedor.

> **Nota (Fase 0):** el stack quedó en **PHP 8.4 + Laravel 13**: los documentos decían PHP 8.3, pero Laravel 13 depende de Symfony 8, que exige PHP ≥ 8.4.1. Decisión del dueño: mantener Laravel 13 y subir la versión de PHP.

- [x] `docker/Dockerfile` (stage `dev`, php:8.4-fpm-alpine — Laravel 13 exige PHP ≥ 8.4 vía Symfony 8, decisión del dueño —, usuario `app` uid=1000/gid=1000) + `docker-compose.yml` local (app + mysql:8.0) + `.env.example` + Makefile básico.
- [x] `composer create-project laravel/laravel` ejecutado **dentro de un contenedor temporal** (imagen `composer:2` corriendo como uid 1000) en un directorio temporal, y luego **se mueve todo a esta carpeta**, conservando `docs/` y `Agents.md` en la raíz.
- [x] `git init` en esta carpeta y `.gitignore` que ignore el scaffold temporal.
- [x] Verificación: `make setup && make up` y la ruta de bienvenida de Laravel sirviendo desde el contenedor como uid 1000.

## Fase 1 — Dominio (migraciones, modelos, seeders)

- [ ] Migraciones y modelos según `03-modelo-datos.md` (users, branches, groups, students, payments, attendances, accounts, movements + tablas de spatie).
- [ ] Modelos con relaciones, casts y scopes (`scopeActive`, `scopePendingForMonth`, ...).
- [ ] Seeders según la estrategia de `03-modelo-datos.md`: `CatalogSeeder` (siempre), `DirectorSeeder` (credenciales por `.env`), `DemoStudentsSeeder` (solo dev); `DatabaseSeeder` solo llama al catálogo.
- [ ] Factories para tests.
- [ ] **Base de datos de tests separada** (`.env.testing`, `phpunit.xml`) — ver `05-despliegue.md`.
- [ ] Configuración regional: `APP_TIMEZONE=America/La_Paz`, locale `es`.

## Fase 2 — Auth y config base

- [ ] Login/logout con Blade (scaffolding manual; Breeze opcional). Sin registro público.
- [ ] Rol `admin` vía spatie + middleware en todas las rutas de gestión.
- [ ] Layout Blade base mobile-first según `06-diseno-ui.md`: header con marca + arcoíris, tabs inferiores, tarjetas, pills, toasts; rutas `/`, `/students`, `/attendance`, `/payments`, `/finance`.
- [ ] Vite con el JS mínimo: mostrar/ocultar campos del formulario de movimientos, **autocompletar tarifa al cambiar sucursal**, mantener selección en selects.

## Fase 3 — Módulos core (incrementos separados, cada uno demostrable)

Cada módulo = FormRequest(s) + `app/Services/…` + Controller + rutas web + vistas Blade + **tests de la fase** (regla: test por endpoint).

**F3a — Alumnos** (`StudentService`)
- [ ] Listar (filtro activos/retirados), crear, **editar**, retirar/reactivar.
- [ ] Retiro: `active=false`, `is_promo=false`, `retired_at=hoy`. Reactivación: `enrolled_at=hoy`, `retired_at=null`, y el formulario exige el nuevo fee.
- [ ] Validación grupo–sucursal (el grupo debe pertenecer a la sucursal elegida).
- *Aceptación*: alta y edición de un alumno real; la tarifa se autocompleta por sucursal; los meses previos al alta no aparecen como pendientes.

**F3b — Pagos + Dashboard** (`PaymentService`) — mayor valor de negocio, va primero entre los módulos operativos
- [ ] Registrar mensualidad (monto = fee vigente), unicidad alumno+mes sobre pagos vigentes, estado Pagado/Pendiente por mes seleccionado (`?month=`), historial, directora que cobra obligatoria.
- [ ] **Anular pago** (auditoría `voided_at`/`voided_by` + motivo opcional); tras anular el mes vuelve a estar pendiente.
- [ ] Dashboard: stats + alertas de pendientes del mes seleccionado (respetando la ventana de cobro).
- *Aceptación*: cobrar la mensualidad de un alumno real y verla reflejada en pendientes/dashboard; anular y volver a cobrar el mismo mes.

**F3c — Asistencia** (`AttendanceService`)
- [ ] Registrar/sobrescribir por alumno+fecha (update-or-create), listar últimas 30.
- *Aceptación*: registrar la asistencia de una clase y corregirla el mismo día.

**F3d — Finanzas** (`FinanceService`)
- [ ] Registrar gasto/retiro/transferencia con validaciones (origen ≠ destino; directora con `is_director`).
- [ ] **Anular movimiento** (auditoría).
- [ ] Totales con filtro de rango (hoy/semana/mes/rango) + reparto 50/50 del mes calendario con reserva (simulación, no persistida).
- *Aceptación*: registrar un gasto y verlo descontar del disponible del reparto; cambiar el rango y ver los totales ajustarse.

## Fase 4 — Verificación (endurecimiento)

- [ ] Repaso de cobertura: todo endpoint con su test Feature; Unit para `FinanceService` y `PaymentService` (reparto, unicidad, anulación, ventanas de cobro).
- [ ] Pint (PSR-12) sin errores; `make test` verde **contra la BD de tests**.
- [ ] README con rutas actualizado.

## Fase 5 — Producción y puesta en marcha

- [ ] Completar la dockerización de producción (`docker-compose.prod.yml`, nginx **con assets de Vite**, entrypoint con `migrate --force` + caches) — detalle en `05-despliegue.md`.
- [ ] **Backups** de MySQL (dump programado, retención, procedimiento de restauración probado).
- [ ] Definir hosting de producción (pendiente) y TLS.
- [ ] Crear las **2 directoras reales** (comando/seeder con credenciales por `.env`; `DemoStudentsSeeder` no corre en producción).
- [ ] Cargar catálogos reales (sucursales, grupos, cuentas) y alumnos reales desde la UI.
- [ ] Prueba de humo en iPhone + validación final con las directoras (criterios del MVP).

## Fase 6 (Puntos de mejora posteriores al primer MVP)

- [ ] PDFs con dompdf: recibo de pago, resumen mensual de pendientes, estado de caja.
- [ ] CRUD administrable de catálogos (branches, groups, accounts) con UI propia.
- [ ] Exportaciones (incluido respaldo JSON), historial de tarifas, configuración persistente del % de reparto y de la reserva.
- [ ] Conciliación por cuenta (QR bancario ↔ Banco QR) y saldo por cuenta.

## Estimación orientativa

| Fase | Tamaño relativo |
|---|---|
| F0 Docker + scaffold | S |
| F1 Dominio | M |
| F2 Auth + config | S–M |
| F3a Alumnos | M |
| F3b Pagos + Dashboard | M |
| F3c Asistencia | S |
| F3d Finanzas | M |
| F4 Verificación | S |
| F5 Producción y puesta en marcha | M (depende del hosting pendiente) |

## Cómo empezar una sesión de trabajo

1. Leer `Agents.md` (reglas) y estos documentos (`docs/`).
2. Consultar este archivo para saber en qué fase se está y marcar el avance.
3. Todo se ejecuta vía `make ...` (contenedores, uid 1000); nunca PHP/Composer en el host.
4. **Todo el código se entrega para revisión del usuario: nunca ejecutar `git commit` ni `git push`** (regla en `Agents.md`).
