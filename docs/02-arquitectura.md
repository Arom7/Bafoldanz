# Arquitectura — BAFOLDANZ Digital (Laravel MVC)

## Stack

- **Backend y frontend**: PHP 8.4 + Laravel 13 (MVC clásico con Blade). Sin SPA. (Nota: Laravel 13 requiere PHP ≥ 8.4 vía Symfony 8.)
- **Base de datos**: MySQL con Eloquent ORM.
- **Autenticación**: sesión web de Laravel (login/logout con Blade). Sanctum queda diferenciado para una futura app móvil.
- **Roles y permisos**: `spatie/laravel-permission` (en v1 solo existe el rol `admin` para las directoras).
- **PDF (v1.1)**: `barryvdh/laravel-dompdf`.
- **Assets**: Vite solo para CSS/JS básicos (**autocompletar la mensualidad al elegir el grupo según su sucursal** — Centro 120 / Norte 60 — y mantener la selección en los selects al recargar). No hay framework de frontend SPA.
- **Diseño mobile-first**: la UI sigue el sistema **«Ritmo Andino Glow»** del mockup Stitch (tema oscuro, acento magenta, Montserrat + Plus Jakarta Sans, tarjetas de vidrio, `safe-area`, nav inferior), pensada para iPhone y adaptable a escritorio. Los tokens, componentes y copys están en `06-diseno-ui.md`; la revisión del mockup y las brechas, en `07-revision-mockup.md`.
- **Tests**: PHPUnit. **Estilo de código**: Laravel Pint (PSR-12).

## Arquitectura frontend (normativa v1)

Para este MVP se adopta una arquitectura **server-first** con Blade:

- **Renderizado en servidor**: vistas HTML desde controladores Laravel.
- **Sin SPA en v1**: no Vue/React/Inertia para los módulos operativos del MVP.
- **JS mínimo y modular por pantalla**: solo para UX puntual (autocompletar mensualidad, mejoras de formulario, toasts, confirmaciones).
- **Sin lógica de negocio en frontend**: reglas de negocio, cálculos y consultas en Services/backend.

### Identidad visual y assets

- **Fuente de verdad:** `06-diseno-ui.md`. No se copian valores sueltos del HTML del mockup.
- **Tipografías:** Montserrat (titulares) y Plus Jakarta Sans (cuerpo), más **Material Symbols Outlined** para íconos. Se cargan con `preconnect`/`display=swap` o se autoalojan en producción.
- **Blade/Tailwind:** Blade + Tailwind v4, variables CSS en `resources/css/app.css`; el mockup usa Tailwind por CDN como prototipo, aquí se usa el paquete ya instalado.
- **Tema:** solo oscuro en v1; no se implementa selector claro/oscuro.

### Regla obligatoria: filtros y paginación en backend

- **Todos los filtros, búsqueda, orden y paginación se resuelven en backend**.
- Frontend **no** filtra ni pagina colecciones en memoria del navegador.
- Patrón obligatorio en listados:
  - Formulario de filtros por `GET`.
  - `FormRequest` para validar query params.
  - `Service` para construir la query.
  - `paginate(...)` + `withQueryString()` para conservar filtros entre páginas.

Contrato de query params para listados (v1):

- `search` (string, búsqueda por nombre)
- `status` (`active|retired`)
- `branch_id` (int)
- `group_type_id` (int)
- `page` (int, del paginador)

Reglas v1:

- **`per_page` fijo en 10** (no editable desde la UI en v1).
- Orden por defecto: **`name ASC`**.
- Los filtros y la página se conservan entre navegaciones (`withQueryString()`).

## Patrón MVC aplicado

```
app/
├── Http/
│   ├── Controllers/     # Controladores delgados: reciben Request, delegan en Services, devuelven respuesta
│   │   ├── Auth/SessionController.php   # login/logout (solo directoras)
│   │   ├── StudentController.php
│   │   ├── AttendanceController.php
│   │   ├── PaymentController.php
│   │   ├── FinanceController.php
│   │   └── DashboardController.php
│   ├── Requests/        # Form Requests: validación de entrada
│   │   ├── StoreStudentRequest.php
│   │   ├── UpdateStudentRequest.php
│   │   ├── StoreAttendanceRequest.php
│   │   ├── StorePaymentRequest.php
│   │   └── StoreMovementRequest.php
│   └── Middleware/      # auth (sesión), role (spatie)
├── Models/              # Modelos Eloquent con relaciones, casts y scopes
│   ├── User.php
│   ├── Branch.php
│   ├── GroupType.php
│   ├── Group.php
│   ├── Student.php
│   ├── Registration.php
│   ├── Payment.php
│   ├── Attendance.php
│   └── Movement.php
├── Services/            # LÓGICA DE NEGOCIO (aquí vive todo lo importante)
│   ├── StudentService.php
│   ├── AttendanceService.php
│   ├── PaymentService.php
│   └── FinanceService.php
└── ... (Providers, etc.)

database/
├── migrations/          # Esquema de tablas
├── seeders/             # DatabaseSeeder (catálogos) + DirectorSeeder + DemoStudentsSeeder (solo dev)
└── factories/           # Datos de prueba para tests

resources/views/         # Vistas Blade
├── layouts/app.blade.php   # Layout base: fuentes, tokens CSS, header con logo, nav inferior de 5 tabs, toasts
├── components/ui/          # Componentes reutilizables (button, input, select, textarea, card, stat, pill, toggle, alert, toast, modal, empty-state, pagination, avatar, month-selector)
├── auth/                   # login
├── dashboard/              # Inicio (stats + alertas)
├── students/               # Listado, formularios
├── attendance/
├── payments/
└── finance/

routes/web.php           # Todas las rutas (web clásico, no API)

resources/js/
├── app.js                # Bootstrap global mínimo
├── pages/                # JS por pantalla (students-index, students-form, payments-index, ...)
└── lib/                  # Utilidades pequeñas de UI (dom/query/money), sin reglas de negocio
```

> La landing pública, el formulario de clase de prueba y las vistas de elencos **no se crean en v1**. Cuando se implementen (v1.1) se añadirán `resources/views/public/` y rutas propias, reutilizando el mismo layout y componentes.

## Reglas de la arquitectura

1. **Controladores delgados**: reciben la petición, validan con Form Requests, delegan la operación en un Service y responden (redirect + flash / vista). **Nada de lógica de negocio ni consultas Eloquent en controladores.**
2. **Services**: contienen las reglas de negocio y las consultas Eloquent (ej. `PaymentService::register()`, `FinanceService::summary()`, `FinanceService::splitForMonth()`).
3. **Modelos**: relaciones, casts, scopes de consulta reutilizables (`scopeActive`, `scopePendingForMonth`, ...). Sin lógica de casos de uso compleja.
4. **Validación**: siempre en Form Requests, nunca inline en el controlador.
5. **Un caso de uso = una acción de controlador + un método de service** (con nombre en inglés).
6. **Listados server-side**: filtros, búsqueda, orden y paginación siempre en backend.
7. **Frontend desacoplado por pantalla**: JS modular por vista; prohibido centralizar lógica de módulos en un único archivo gigante.
8. **Blade sin lógica de negocio**: Blade renderiza; Services deciden reglas y resultados.

## Rutas previstas (v1)

Convención decidida: **URLs, clases y métodos en inglés**. Verbos de acción estándar de Laravel: `create` (formulario), `toggle` (retirar/reactivar), `void` (anular). El "mes seleccionado" viaja como query param (`?month=YYYY-MM`), con el mes actual en hora de Bolivia como valor por defecto.

```
GET    /login                             # sesión web (sin registro público)
POST   /login
POST   /logout
GET    /                                  → dashboard (stats + alertas; durante F2a redirige a /students)
GET    /students                          # listado (filtro activos/retirados)
GET    /students/create                   # formulario de alta
POST   /students                          # crear
GET    /students/{id}/edit                # formulario de edición
PUT    /students/{id}                     # actualizar (nombre, grupo, sucursal, fee, día, promo)
PATCH  /students/{id}/toggle              # retirar / reactivar
GET    /attendance                        # listado últimos 30
POST   /attendance                        # registrar (update-or-create por alumno+fecha)
GET    /payments                          # estado por mes (?month=) + listado últimos 30
POST   /payments                          # registrar mensualidad
PATCH  /payments/{id}/void                # anular pago (auditoría: quién, cuándo, motivo opcional)
GET    /finance                           # totales por rango + reparto 50/50 + listado de salidas
POST   /finance/movements                 # registrar salida (concepto, monto, fecha, directora opcional)
PATCH  /finance/movements/{id}/void       # anular movimiento (auditoría)
```

(En v1 los catálogos `branches`, `group_types` y `groups` se cargan con **seeders**; su CRUD administrable va en v1.1 — ver `04-plan-migracion.md`.)

## Glosario (negocio ↔ código)

| En la UI (español) | En el código (inglés) |
|---|---|
| Bailarín / Bailarines | Student / Students |
| Mensualidad | Payment (fee = `amount`) |
| Tarifa promocional permanente | `is_promo` |
| Retirar / reactivar | toggle (`active`, `retired_at`, `enrolled_at`) |
| Anular | void (`voided_at`, `voided_by`) |
| Marca de asistencia | attendance `code` |
| Inscripción | Registration |
| Sucursal | Branch |
| Tipo de grupo | GroupType |
| Grupo (tipo + sucursal + horario) | Group |
| Salida de caja | Movement |
| Caja | Finance |
| Cierre del mes | reparto 50/50 (simulación) |

## Convenciones de trabajo

Resumen en `Agents.md` (reglas del proyecto). Puntos clave:

- Tipado estricto en parámetros y retornos; PSR-12 vía Pint.
- Clases y URLs en inglés.
- Zona horaria del sistema: `America/La_Paz`; locale `es`; moneda Bs. (es-BO).
- Mobile-first: todo diseño de vista se prueba primero en móvil (390 px, nav inferior); tokens y componentes en `06-diseno-ui.md`. El tema es **solo oscuro** en v1.
- Tests en `tests/Feature` (endpoints) y `tests/Unit` (services), archivo `NombreTest.php`.
- Todo endpoint nuevo o modificado debe tener un test.
- No editar migraciones ya aplicadas: crear una nueva.
- No leer ni modificar `.env` (usar `.env.example`).
- Actualizar el README si cambian rutas.
- **El código se entrega para revisión: no se ejecutan `git commit` ni `git push`** (los decide el usuario tras revisar).
