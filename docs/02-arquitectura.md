# Arquitectura — BAFOLDANZ Digital (Laravel MVC)

## Stack

- **Backend y frontend**: PHP 8.4 + Laravel 13 (MVC clásico con Blade). Sin SPA. (Nota: Laravel 13 requiere PHP ≥ 8.4 vía Symfony 8.)
- **Base de datos**: MySQL con Eloquent ORM.
- **Autenticación**: sesión web de Laravel (login/logout con Blade). Sanctum queda diferenciado para una futura app móvil.
- **Roles y permisos**: `spatie/laravel-permission` (en v1 solo existe el rol `admin` para las directoras).
- **PDF (v1.1)**: `barryvdh/laravel-dompdf`.
- **Assets**: Vite solo para CSS/JS básicos (mostrar/ocultar campos del formulario de movimientos, **autocompletar la tarifa al cambiar de sucursal** — Centro 120 / Norte 60 —, y mantener la selección en los selects al recargar). No hay framework de frontend SPA.
- **Diseño mobile-first**: la UI replica el prototipo original (tabs inferiores con `safe-area`, tarjetas, pills, toasts), pensada para iPhone. Todos los tokens de diseño (colores, tipografía, copys, íconos) están en `06-diseno-ui.md`.
- **Tests**: PHPUnit. **Estilo de código**: Laravel Pint (PSR-12).

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
│   ├── Group.php
│   ├── Student.php
│   ├── Payment.php
│   ├── Attendance.php
│   ├── Account.php
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
├── layouts/app.blade.php   # Layout base: header con marca, navegación por tabs, tarjetas, toasts
├── auth/                   # login
├── dashboard/              # Inicio (stats + alertas)
├── students/               # Listado, formularios
├── attendance/
├── payments/
└── finance/

routes/web.php           # Todas las rutas (web clásico, no API)
```

## Reglas de la arquitectura

1. **Controladores delgados**: reciben la petición, validan con Form Requests, delegan la operación en un Service y responden (redirect + flash / vista). **Nada de lógica de negocio ni consultas Eloquent en controladores.**
2. **Services**: contienen las reglas de negocio y las consultas Eloquent (ej. `PaymentService::register()`, `FinanceService::summary()`, `FinanceService::splitForMonth()`).
3. **Modelos**: relaciones, casts, scopes de consulta reutilizables (`scopeActive`, `scopePendingForMonth`, ...). Sin lógica de casos de uso compleja.
4. **Validación**: siempre en Form Requests, nunca inline en el controlador.
5. **Un caso de uso = una acción de controlador + un método de service** (con nombre en inglés).

## Rutas previstas (v1)

Convención decidida: **URLs, clases y métodos en inglés**. Verbos de acción estándar de Laravel: `create` (formulario), `toggle` (retirar/reactivar), `void` (anular). El "mes seleccionado" viaja como query param (`?month=YYYY-MM`), con el mes actual en hora de Bolivia como valor por defecto.

```
GET    /login                             # sesión web (sin registro público)
POST   /login
POST   /logout
GET    /                                  → dashboard (stats + alertas)
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
GET    /finance                           # totales por rango + reparto 50/50 + listado movimientos
POST   /finance/movements                 # registrar gasto/retiro/transferencia
PATCH  /finance/movements/{id}/void       # anular movimiento (auditoría)
```

(En v1 los catálogos `branches`, `groups`, `accounts` se cargan con **seeders**; su CRUD administrable va en v1.1 — ver `04-plan-migracion.md`.)

## Glosario (negocio ↔ código)

| En la UI (español) | En el código (inglés) |
|---|---|
| Bailarín / Bailarines | Student / Students |
| Mensualidad | Payment (fee = `amount`) |
| Tarifa promocional permanente | `is_promo` |
| Retirar / reactivar | toggle (`active`, `retired_at`, `enrolled_at`) |
| Anular | void (`voided_at`, `voided_by`) |
| Marca de asistencia | attendance `code` |
| Caja | Finance (`Account`, `Movement`) |
| Cierre del mes | reparto 50/50 (simulación) |

## Convenciones de trabajo

Resumen en `Agents.md` (reglas del proyecto). Puntos clave:

- Tipado estricto en parámetros y retornos; PSR-12 vía Pint.
- Clases y URLs en inglés.
- Zona horaria del sistema: `America/La_Paz`; locale `es`; moneda Bs. (es-BO).
- Mobile-first: todo diseño de vista se prueba primero en móvil (tabs inferiores); tokens en `06-diseno-ui.md`.
- Tests en `tests/Feature` (endpoints) y `tests/Unit` (services), archivo `NombreTest.php`.
- Todo endpoint nuevo o modificado debe tener un test.
- No editar migraciones ya aplicadas: crear una nueva.
- No leer ni modificar `.env` (usar `.env.example`).
- Actualizar el README si cambian rutas.
- **El código se entrega para revisión: no se ejecutan `git commit` ni `git push`** (los decide el usuario tras revisar).
