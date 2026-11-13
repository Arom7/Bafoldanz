# Contexto del negocio — BAFOLDANZ Digital

Sistema de **control de asistencia y pagos** para una academia de danza (BAFOLDANZ, "Ballet Folclórico de Danza"), pensado para un negocio pequeño o mediano. El equipo está formado por **dos directoras** que gestionan alumnos, cobran mensualidades y administran la caja de la academia.

## Requisitos no funcionales

- **Mobile-first**: la app se usa principalmente en iPhone (navegación por pestañas inferior con `safe-area`, tarjetas apiladas, tipografía compacta). En escritorio se adapta con una media query simple. El detalle visual completo está en `06-diseno-ui.md`.
- **Zona horaria y locale**: `APP_TIMEZONE=America/La_Paz` y `app.locale=es`. Un "mes" o un "día" siempre se calcula en hora de Bolivia, nunca en UTC.
- **Dos directoras**: el negocio funciona con exactamente dos directoras (el reparto es 50/50). Existen como usuarios del sistema (`users` con flag `is_director`) para poder seleccionarlas en los retiros.
- **Acceso**: las directoras son las únicas que pueden realizar todas las funciones descritas (rol `admin` de spatie).

## Sucursales y grupos

- **Sucursales**: Centro y Norte (datos cargados por seeders en v1).
- **Grupos por sucursal** (decisión 2026-11): cada grupo pertenece a una sucursal. El formulario de alumno filtra los grupos según la sucursal elegida y el backend valida esa pertenencia.
- Grupos iniciales (seeders, asignados a su sucursal): Infantil, Juvenil Intermedio, Juvenil Avanzado.

## Alumnos (bailarines)

En la interfaz se les llama "bailarines"; en el código, `Student` (glosario en `02-arquitectura.md`).

- Datos: nombre, sucursal, grupo (de esa sucursal), mensualidad (fee), **día de pago** (día del mes), promoción permanente.
- **Tarifa por defecto según sucursal**: Centro = Bs. 120, Norte = Bs. 60 (autocompleta el formulario al cambiar de sucursal; editable).
- Un alumno con **promoción permanente** paga un fee personalizado (ej. Bs. 90 estando en la sucursal Centro).
- **Alta**: al registrar un alumno se guarda `enrolled_at` = fecha de alta.
- Al **retirar** un alumno (baja temporal/definitiva):
  - `active = false`, `is_promo = false`, se registra `retired_at`.
  - El retiro es lógico: los datos del alumno se conservan.
  - No se elimina el registro del alumno (borrado administrativo es otra cosa, ver modelo de datos).
- Al **reactivar**: la tarifa y la promoción se renegocian manualmente (el formulario exige fee), `active = true`, `enrolled_at` pasa a ser la fecha de reactivación y `retired_at` se limpia. No se recupera automáticamente la promoción.
- **Edición**: se puede editar un alumno (nombre, grupo, sucursal, fee, día de pago, promo).
- La baja y el alta definen la ventana de cobro: un mes solo es "pendiente" para un alumno si cae entre `enrolled_at` y (si retiró) `retired_at`. Los meses anteriores al alta o posteriores al retiro no generan deuda.

## Asistencia

- Un registro por alumno por día (se sobrescribe si se vuelve a registrar el mismo día).
- Marcas (códigos):
  - `P` — Presente
  - `F` — Falta injustificada
  - `J` — Falta justificada
  - `R` — Reposición
  - `H` — Feriado
  - `S` — Suspendida por academia

## Pagos / mensualidades

- El pago corresponde a un **mes** (`YYYY-MM`) y a un alumno.
- Monto por defecto = fee vigente del alumno (puede ser el de promoción).
- Medios de pago: **Efectivo** y **QR bancario** (únicos en v1).
- **Un solo pago vigente por alumno y mes**.
- Estado por mes: **Pagado / Pendiente**.
- Al cobrar se registran automáticamente la fecha del cobro y la **directora que cobró** (obligatoria). El alumno pasa al estado "Pagado" del mes correspondiente.
- **Anulación**: se puede anular un pago registrado por error, con registro de quién y cuándo anuló (y un motivo opcional). Tras anular, el mes vuelve a estar "pendiente" y se puede volver a cobrar.
- Nota del negocio: el campo `payment_day` (día de pago) aún no dispara alertas automáticas; en v1 alimenta la vista de alertas ("vence el día X").

## Finanzas / Caja

- **Cuentas** (nombres canónicos, por seeders en v1): **Caja Efectivo** y **Banco QR**.
- Tipos de movimiento:
  - `gasto` — salida de una cuenta.
  - `retiro` — retiro de directora (requiere indicar **qué directora** retira).
  - `transferencia` — movimiento entre cuentas (origen ≠ destino).
- **Filtro único de periodo** (decisión 2026-11): un selector de rango de fechas con atajos — hoy / semana / mes / rango personalizado — por defecto el **mes actual**. Se aplica a ingresos, gastos, retiros y saldo; el total mostrado siempre corresponde al rango seleccionado.
- Los cuatro totales (ingresos, gastos, retiros, saldo) se calculan **solo con registros vigentes** (no anulados).
- **Saldo** = ingresos − gastos − retiros del rango seleccionado.
- **Anulación**: se puede anular un movimiento registrado por error, con registro de quién anuló (motivo opcional). Un movimiento anulado no suma en ningún total.
- **Cuentas y transferencias (aclaración)**: los totales y el saldo no se calculan por cuenta; una transferencia entre cuentas **no altera** el saldo total en v1. La conciliación por cuenta (QR bancario ↔ Banco QR) es una mejora futura (v1.1).
- **Cierre del mes** (cálculo informativo que **no registra retiros automáticamente**). Usa el **mes calendario actual**, no el rango seleccionado:
  - `disponible = pagos vigentes del mes calendario actual − gastos vigentes con fecha del mes calendario actual − reserva`
  - `reserva` = valor que la directora ingresa en la vista (por defecto 0; no se persiste en v1).
  - Cada directora recibe el 50 % del disponible. Si `disponible < 0`, no hay reparto.
  - La caja chica reservada es "dinero reservado", no un gasto.
- **Los pagos no generan movimientos de caja** (los ingresos se calculan desde la tabla de pagos).

## Dashboard

- Indicadores: alumnos activos, mensualidades pagadas del mes seleccionado, pendientes ("por regularizar") del mes seleccionado, e **ingresos históricos** (todos los pagos vigentes, sin filtro de periodo).
- El **mes seleccionado** se elige con un selector (parámetro `?month=YYYY-MM` en la URL); por defecto el mes actual en hora de Bolivia. Es el mismo selector que usa la sección de pagos.
- Alertas: listado de alumnos activos con mensualidad pendiente en el mes seleccionado.

## Roles y usuarios (v1)

- Solo **directoras/admin** usan el sistema (acceso completo). Sin registro público.
- En v1 son exactamente **dos directoras**: se distinguen con el flag `is_director` de `users` (se usan en el select de retiros y en el reparto 50/50). El rol `admin` de spatie controla el acceso.
- Si una directora olvida su contraseña, se restablece con `make admin` (no hay recuperación por correo en v1).
- La estructura queda preparada para sumar **asistentes** en versiones futuras (con permisos limitados: registrar asistencia y pagos, sin ver el reparto).

## Utilidades

- Moneda: **Bs. (Bolivianos)**, formato `es-BO`.
- El respaldo/exportación en JSON queda para una versión futura (v1.1).
