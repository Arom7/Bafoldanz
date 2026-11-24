# Contexto del negocio — BAFOLDANZ Digital

Sistema de **control de asistencia y pagos** para una academia de danza (BAFOLDANZ, "Ballet Folclórico de Danza"), pensado para un negocio pequeño o mediano. El equipo está formado por **dos directoras** que gestionan alumnos, cobran mensualidades y administran la caja de la academia.

## Alcance v1 y relación con el mockup

El MVP sigue siendo la **app interna** de las dos directoras: alumnos, asistencia, pagos, finanzas, dashboard y login. El mockup de Stitch **“Plataforma Web Bafoldanz Academia”** aporta la **dirección visual y UX** (sistema «Ritmo Andino Glow»), pero también explora módulos públicos que **no forman parte de v1**: landing, clase demostrativa, solicitudes/leads, danzas/elencos, salones y métricas académicas. Esos módulos quedan documentados como **v1.1** en `04-plan-migracion.md` y `07-revision-mockup.md`.

El mockup **no redefine reglas de negocio, catálogos ni el modelo de datos**. Si hay conflicto entre un dato del mockup (sedes, montos, horarios, estados) y los documentos 01/03, mandan los documentos.

## Requisitos no funcionales

- **Diseño mobile-first**: la app se usa principalmente en el navegador de Safari, pero debe funcionar en Chrome y Brave. La identidad vigente es **«Ritmo Andino Glow»** (tema oscuro, acento magenta, Montserrat + Plus Jakarta Sans, tarjetas de vidrio, navegación inferior con `safe-area`). En escritorio se adapta con media queries. Los tokens y componentes están en `06-diseno-ui.md`; la revisión del mockup, en `07-revision-mockup.md`.
- **Zona horaria y locale**: `APP_TIMEZONE=America/La_Paz` y `app.locale=es`. Un "mes" o un "día" siempre se calcula en hora de Bolivia, nunca en UTC.
- **Dos directoras**: el negocio funciona con exactamente dos directoras (el reparto es 50/50). Existen como usuarios del sistema (`users` con flag `is_director`) para identificar quién realizó una salida y para el reparto.
- **Acceso**: las directoras son las únicas que pueden realizar todas las funciones descritas (rol `admin` de spatie).

## Sucursales y grupos

- **Sucursales**: Centro y Norte (datos cargados por seeders en v1).
- **Tipos de grupo** (globales): Infantil, Juvenil Intermedio, Juvenil Avanzado.
- **Grupos**: combinan un tipo de grupo, una sucursal y un horario (hora de inicio y fin). Se cargan por seeders en v1.
  - Horarios v1 (iguales en Centro y Norte): Infantil 16:00–17:00, Juvenil Intermedio 17:00–18:00, Juvenil Avanzado 18:00–19:00.
- Cada alumno se inscribe en **un solo grupo** mediante `registrations`; su sucursal se deriva del grupo.
- El formulario de alumno puede filtrar los grupos por sucursal como ayuda visual. No hace falta una validación adicional de pertenencia: la relación grupo → sucursal ya la garantiza.
- No se modelan días de la semana: el grupo se identifica por sucursal, tipo y horario.

## Alumnos (bailarines)

En la interfaz se les llama "bailarines"; en el código, `Student` (glosario en `02-arquitectura.md`).

- Datos: nombre, grupo (la sucursal se deriva del grupo), mensualidad, **día de pago** (día del mes), promoción permanente.
- **Tarifa por defecto según la sucursal del grupo elegido**: Centro = Bs. 120, Norte = Bs. 60 (autocompleta el formulario; editable).
- Un alumno con **promoción permanente** paga una mensualidad personalizada (ej. Bs. 90 estando en la sucursal Centro).
- **Alta**: al registrar un alumno se crea también su inscripción al grupo y se guarda `enrolled_at` = fecha de alta.
- Al **retirar** un alumno (baja temporal/definitiva):
  - `active = false`, `is_promo = false`, se registra `retired_at`.
  - El retiro es lógico: los datos del alumno se conservan.
  - No se elimina el registro del alumno (borrado administrativo es otra cosa, ver modelo de datos).
- Al **reactivar**: la tarifa y la promoción se renegocian manualmente (el formulario exige mensualidad), `active = true`, `enrolled_at` pasa a ser la fecha de reactivación y `retired_at` se limpia. Se actualiza el grupo de su inscripción. No se recupera automáticamente la promoción.
- **Edición**: se puede editar un alumno (nombre, grupo, mensualidad, día de pago, promo). Cambiar de grupo actualiza su inscripción; no se guarda historial de grupos.
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
- Monto por defecto = mensualidad vigente del alumno (puede ser el de promoción).
- Medios de pago: **Efectivo** y **QR bancario** (únicos en v1).
- **Un solo pago vigente por alumno y mes**.
- Estado por mes: **Pagado / Pendiente**.
- Al cobrar se registran automáticamente la fecha del cobro y la **directora que cobró** (obligatoria). El alumno pasa al estado "Pagado" del mes correspondiente.
- **Anulación**: se puede anular un pago registrado por error, con registro de quién y cuándo anuló (y un motivo opcional). Tras anular, el mes vuelve a estar "pendiente" y se puede volver a cobrar.
- Nota del negocio: el campo `payment_day` (día de pago) aún no dispara alertas automáticas; en v1 alimenta la vista de alertas ("vence el día X").

## Finanzas / Caja

- **Una sola caja general.** No se modelan cuentas separadas ni transferencias entre cuentas.
- **Salidas**: cada movimiento de caja es una salida de dinero con `concept` (justificación), `amount` y `date`.
  - `director_id` es opcional e informativo: indica quién realizó la salida cuando corresponde (normalmente una directora). No altera ningún cálculo.
- **Filtro único de periodo** (decisión 2026-11): un selector de rango de fechas con atajos — hoy / semana / mes / rango personalizado — por defecto el **mes actual**. Se aplica a ingresos, salidas y saldo; el total mostrado siempre corresponde al rango seleccionado.
- Los totales (ingresos, salidas, saldo) se calculan **solo con registros vigentes** (no anulados).
- **Saldo** = ingresos − salidas del rango seleccionado.
- **Anulación**: se puede anular un movimiento registrado por error, con registro de quién anuló y un motivo opcional. Un movimiento anulado no suma en ningún total.
- **Cierre del mes** (cálculo informativo que **no registra salidas automáticamente**). Usa el **mes calendario actual**, no el rango seleccionado:
  - `disponible = pagos vigentes del mes calendario actual − salidas vigentes con fecha del mes calendario actual − reserva`
  - `reserva` = valor que la directora ingresa en la vista (por defecto 0; no se persiste en v1).
  - Cada directora recibe el 50 % del disponible. Si `disponible < 0`, no hay reparto.
  - La caja chica reservada es "dinero reservado", no una salida.
- **Los pagos no generan movimientos de caja** (los ingresos se calculan desde la tabla de pagos).

## Dashboard

- Indicadores: alumnos activos, mensualidades pagadas del mes seleccionado, pendientes ("por regularizar") del mes seleccionado, e **ingresos históricos** (todos los pagos vigentes, sin filtro de periodo).
- El **mes seleccionado** se elige con un selector (parámetro `?month=YYYY-MM` en la URL); por defecto el mes actual en hora de Bolivia. Es el mismo selector que usa la sección de pagos.
- Alertas: listado de alumnos activos con mensualidad pendiente en el mes seleccionado.

## Roles y usuarios (v1)

- Solo **directoras/admin** usan el sistema (acceso completo). Sin registro público.
- En v1 son exactamente **dos directoras**: se distinguen con el flag `is_director` de `users` (se usan para identificar salidas y en el reparto 50/50). El rol `admin` de spatie controla el acceso.
- Si una directora olvida su contraseña, se restablece con `make admin` (no hay recuperación por correo en v1).
- La estructura queda preparada para sumar **asistentes** en versiones futuras (con permisos limitados: registrar asistencia y pagos, sin ver el reparto).

## Utilidades

- Moneda: **Bs. (Bolivianos)**, formato `es-BO`.
- El respaldo/exportación en JSON queda para una versión futura (v1.1).
