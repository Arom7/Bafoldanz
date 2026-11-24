# Diseño UI — BAFOLDANZ Digital

> **Sistema visual vigente:** «Ritmo Andino Glow» / «Neon Glow».
> **Fuente canónica:** proyecto Stitch **“Plataforma Web Bafoldanz Academia”** (`projects/9559637951038574277`), tema `Ritmo Andino Glow`, actualizado el **2026-11-23**.
> **Revisión completa del mockup:** `07-revision-mockup.md`.
>
> Este documento **sustituye por completo** la guía anterior basada en la demo clara con barra arcoíris, tipografía Georgia y fondo `#f7f8fc`. Esos tokens quedan **obsoletos**: no deben reutilizarse en vistas nuevas.
>
> **Alcance:** esta guía rige el rediseño visual del MVP interno (Alumnos, Asistencia, Pagos, Finanzas, Dashboard y login). La landing pública, las solicitudes/leads, los elencos/danzas y los salones que aparecen en el mockup son **módulos futuros (v1.1)** y no forman parte de v1; ver la matriz de brechas en `07-revision-mockup.md`.

## 1. Principios de diseño

1. **Escenario nocturno, no “modo oscuro genérico”**: fondo casi negro, superficies de carbón y luces de escenario en magenta, cian y ámbar. La energía del folklore se expresa con acentos, no con un fondo colorido.
2. **Regla 60-30-10**: 60 % negro/carbón, 30 % superficies y bordes pizarra, 10 % acento magenta. El acento se reserva para la acción principal, el estado activo y los datos clave.
3. **Mobile-first real**: el mockup está diseñado a 390 px de ancho. Todo se valida primero a 390 px (iPhone), luego tablet y escritorio.
4. **Una sola acción principal por pantalla**: CTA magenta dominante; el resto son acciones secundarias o ghost.
5. **Cristal y profundidad**: tarjetas con vidrio oscuro, borde de 1 px y un glow ambiental discreto; nunca sombras duras ni degradados arcoíris.
6. **Jerarquía clara**: titular Montserrat extrabold → dato principal → etiqueta pequeña en mayúsculas. El dato manda; la decoración no compite.
7. **Datos reales**: el mockup usa cifras, nombres y montos de demostración. En la aplicación se muestran datos de la BD; **prohibido hardcodear placeholders**.
8. **Táctil**: objetivos de toque mínimos de **48 px**, separación de 8 px como mínimo.
9. **Sin mezcla de vocabulario**: en v1 se mantiene «Bailarín/Bailarines» para la UI; «Alumno» se usa en el mockup y en la inscripción pública futura. No mezclar ambos términos en la misma vista.

## 2. Tokens

### 2.1 Color

El mockup mezcla ligeramente los negros (`#0A0A0E`, `#0B0B0F`, `#0D0D12`, `#131318`). Para implementar se fija esta escala única:

| Token | Valor | Uso |
|---|---|---|
| `--bg` | `#0A0A0E` | Fondo de la app interna (canvas) |
| `--bg-public` | `#0B0B0F` | Fondo de la landing pública futura |
| `--surface` | `#131318` | Superficie base / contenedores |
| `--surface-card` | `#161620` | Tarjetas y filas |
| `--surface-alt` | `#1B1B26` | Tarjetas secundarias, inputs de la landing |
| `--surface-input` | `#181822` | Inputs y selects |
| `--border` | `#262638` | Borde estándar de 1 px |
| `--border-strong` | `#2E2D42` | Borde de énfasis / separadores |
| `--text` | `#F4F4F8` | Texto principal |
| `--text-muted` | `#9898AB` | Texto secundario, metadatos, placeholders |
| `--primary` | `#FF1D68` | Magenta fueguino: CTA, tab activa, foco, dato clave |
| `--primary-hover` | `#E01558` | Hover/pressed del primario |
| `--primary-soft` | `rgba(255, 29, 104, 0.12)` | Fondo de chips y halos suaves |
| `--primary-glow` | `rgba(255, 29, 104, 0.35)` | Sombra/glow del CTA |
| `--secondary` | `#FFB800` | Oro andino: advertencia, promoción, dato secundario |
| `--tertiary` | `#22D3EE` | Cian eléctrico: estados positivos, foco alterno, juventud |
| `--violet` | `#9D4EDD` | Violeta místico: bordes de tarjeta, morenada, detalles |
| `--success` | `#00E676` | Éxito / esmeralda |
| `--warning` | `#FBBF24` | Pendiente / atención |
| `--danger` | `#FFB4AB` | Texto de error o acción destructiva |
| `--danger-bg` | `#93000A` | Fondo de error |
| `--focus` | `#22D3EE` | Anillo de foco visible |

**Estados semánticos** (siempre texto + punto/ícono, nunca solo color):

| Estado | Fondo | Borde | Texto |
|---|---|---|---|
| Pagado / Al día | `rgba(34, 211, 238, 0.10)` | `rgba(34, 211, 238, 0.30)` | `#22D3EE` |
| Pendiente | `rgba(245, 158, 11, 0.10)` | `rgba(245, 158, 11, 0.30)` | `#FBBF24` |
| Anulado / error | `rgba(255, 180, 171, 0.10)` | `rgba(255, 180, 171, 0.30)` | `#FFB4AB` |
| Retirado / neutro | `rgba(255, 255, 255, 0.05)` | `rgba(255, 255, 255, 0.10)` | `#9898AB` |
| Activo / gestión | `rgba(34, 211, 238, 0.10)` | `rgba(34, 211, 238, 0.30)` | `#22D3EE` |

**Acentos por danza** (solo informativos, para el futuro catálogo de elencos): Caporales `#FF1D68`, Tinkus `#FFB800`, Morenada `#9D4EDD`, Salay `#00E676`.

### 2.2 Tipografía

- **Titulares:** Montserrat (`400/600/700/800`). Transmite fuerza, ritmo y autoridad institucional.
- **Cuerpo y etiquetas:** Plus Jakarta Sans (`400/500/600/700`). Legibilidad en pantallas pequeñas.
- Cargar con `display=swap`; fallback: `system-ui, -apple-system, "Segoe UI", sans-serif`.
- Tracking: `-0.02em` en titulares grandes; `+0.04em` en etiquetas mayúsculas.

| Token | Fuente | Tamaño / línea | Peso | Uso |
|---|---|---|---|---|
| `display-lg` | Montserrat | 56 / 64 px | 800 | Hero de escritorio |
| `display-lg-mobile` | Montserrat | 36 / 44 px | 800 | Hero móvil |
| `headline-lg` | Montserrat | 32 / 40 px | 700 | Título de página en escritorio |
| `headline-lg-mobile` | Montserrat | 26 / 34 px | 700 | Título de página en móvil |
| `headline-md` | Montserrat | 24 / 32 px | 600 | Título de tarjeta destacada |
| `headline-sm` | Montserrat | 20 / 28 px | 600 | Título de tarjeta |
| `body-lg` | Plus Jakarta Sans | 18 / 28 px | 400 | Texto introductorio |
| `body-md` | Plus Jakarta Sans | 16 / 24 px | 400 | Texto base e inputs |
| `body-sm` | Plus Jakarta Sans | 14 / 20 px | 400 | Texto de tarjetas y tablas |
| `label-lg` | Plus Jakarta Sans | 14 / 20 px | 600 | Etiquetas de formulario |
| `label-md` | Plus Jakarta Sans | 12 / 16 px | 600 | Metadatos y ayudas |
| `label-sm` | Plus Jakarta Sans | 11 / 14 px | 700 | Badges y navegación (solo no crítico) |

> **Accesibilidad:** el mockup usa 10-11 px en navegación y badges. Se conserva solo para etiquetas cortas y no esenciales; el texto informativo nunca baja de 12 px y el cuerpo de lectura de 14 px.

### 2.3 Espaciado y medidas

- Escala base de 4 px; ritmo principal de 8 px: `4, 8, 12, 16, 24, 32, 40, 48`.
- Márgenes: 16 px móvil, 32 px tablet, 48 px escritorio.
- Gutter: 16 px móvil, 24 px tablet, 32 px escritorio.
- Ancho de contenido: `max-width: 512px` en móvil/tablet (el mockup usa `max-w-lg`), `1280px` en escritorio.
- Header: 64 px + `safe-area-inset-top`.
- Navegación inferior: 64 px + `safe-area-inset-bottom`.
- Área táctil mínima: 48 × 48 px.

### 2.4 Radios, elevación y movimiento

| Token | Valor | Uso |
|---|---|---|
| `--radius-sm` | 8 px | Chips pequeños, inputs compactos |
| `--radius-md` | 12 px | Botones, inputs, selects |
| `--radius-lg` | 16 px | Tarjetas y contenedores |
| `--radius-xl` | 24 px | Modales y bloques destacados |
| `--radius-full` | 9999 px | Pills, avatares, tabs circulares |

| Elevación | Valor | Uso |
|---|---|---|
| Tarjeta | `0 0 15px rgba(168, 85, 247, 0.15)` | Tarjeta estándar |
| Tarjeta activa | `0 0 18px rgba(255, 29, 104, 0.18)` | Foco/selección |
| CTA | `0 4px 20px rgba(255, 29, 104, 0.35)` | Botón primario |
| Nav inferior | `0 -8px 24px rgba(0, 0, 0, 0.45)` | Barra fija |
| Modal | `0 16px 40px rgba(0, 0, 0, 0.60)` | Diálogos |

- Transiciones: 150-200 ms `ease-out`; `active: scale(0.98)` en botones.
- Glow ambiental: círculos difusos de 200-260 px con `blur(80-110px)` y opacidad 10-15 %, solo decorativos y `pointer-events-none`.
- Respetar `prefers-reduced-motion`: desactivar pulsos y escalados.

## 3. Estructura de pantalla

### 3.1 Header

- Fijo arriba, 64 px de alto, `padding-top: env(safe-area-inset-top)`.
- Fondo `rgba(10, 10, 14, 0.90)` + `backdrop-blur(24px)` y borde inferior `1px solid rgba(38, 38, 56, 0.5)`.
- Logo circular de 36 px con borde sutil.
- Marca: `BAFOLDANZ` en Montserrat 15-16 px, bold, tracking amplio; subtítulo de 10 px en mayúsculas (`Panel de Control` en la app; `Ballet Folklórico` en la landing futura).
- Acción derecha: avatar/`account_circle` en la app; botón pill `Inscribirse` en la landing.

### 3.2 Contenido

- `main` con `padding-top: 80px` y `padding-bottom: 96px` para dejar sitio al header y a la nav.
- Contenedor centrado `max-width: 512px`, `padding-inline: 16px`, `gap: 24px` entre secciones.
- Encabezado de página: badge de contexto (opcional) + `h1` + subtítulo corto.
- En escritorio el contenido puede pasar a `max-width: 1100px` y rejilla de 2-4 columnas.

### 3.3 Navegación inferior

- Fija abajo, 64 px + `safe-area`, fondo `rgba(10, 10, 14, 0.95)` + blur 24 px, borde superior.
- Cada ítem: ícono Material Symbols 20 px + etiqueta 10-11 px; activo en `--primary` y `font-weight: 600`.
- El mockup define **4 tabs públicas**. La app interna v1 conserva **5 tabs operativas** con el mismo componente visual:

| Contexto | Tab | Ícono | Ruta |
|---|---|---|---|
| Mockup público (v1.1) | Explorar | `explore` | `/` |
| Mockup público (v1.1) | Elencos | `theater_comedy` | `/elencos` |
| Mockup público (v1.1) | Inscripción | `event_available` | `/inscripcion` |
| Mockup público (v1.1) | Gestión | `admin_panel_settings` | `/login` |
| **App interna v1** | Inicio | `space_dashboard` | `/` |
| **App interna v1** | Bailarines | `groups` | `/students` |
| **App interna v1** | Asistencia | `fact_check` | `/attendance` |
| **App interna v1** | Pagos | `payments` | `/payments` |
| **App interna v1** | Finanzas | `account_balance_wallet` | `/finance` |

> El tab «Gestión» del mockup es la puerta de entrada a la app interna, no una sexta sección. En v1 se llega por `/login`.

### 3.4 Adaptación a escritorio

El mockup solo define móvil. La adaptación se resuelve así:

- **≥ 768 px (tablet):** contenido a 720-820 px; stats en 2 columnas; formularios en 2 columnas cuando el campo lo permita; nav inferior permanece.
- **≥ 1024 px (escritorio):** header y nav pasan a una barra superior estática bajo el header; contenido hasta 1280 px; listados en tabla/tarjetas de 2 columnas; CTA alineado a la derecha del título.
- No introducir una segunda identidad visual en escritorio: mismos tokens y mismo lenguaje de tarjetas.

## 4. Componentes

### 4.1 Botones

| Variante | Especificación |
|---|---|
| Primario | Fondo `--primary`, texto blanco, Montserrat 700 14 px, alto 48 px, radio 12 px, padding 0 16 px, glow `--primary-glow` |
| Secundario | Fondo `rgba(255,255,255,0.06)`, borde 1 px `--secondary`, texto `--secondary` |
| Ghost / terciario | Sin fondo ni borde, texto `--tertiary` o `--text`; glow suave al enfocar |
| Destructivo | Fondo `rgba(147, 0, 10, 0.20)`, borde `rgba(255,180,171,0.40)`, texto `--danger` |
| Enlace | Texto `--primary` o `--tertiary`, subrayado al hover |
| Deshabilitado | `opacity: 0.5`, `cursor: not-allowed`, sin glow |
| Solo ícono | 44-48 px cuadrados, radio 12 px, `aria-label` obligatorio |

- Un solo botón primario por pantalla; en formularios largos puede repetirse arriba/abajo solo si es la misma acción.
- Estado de carga: spinner de 16 px + texto «Guardando…», ancho estable.

### 4.2 Tarjetas

- Fondo `--surface-card`, borde 1 px `--border`, radio 16 px, padding 16 px, sombra de tarjeta.
- Variante **destacada**: borde `rgba(168, 85, 247, 0.35)` y glow violeta.
- Variante **interactiva**: hover con borde `--primary`, `translateY(-1px)` y glow suave.
- Título de tarjeta con ícono 18-20 px en un cuadrado tintado de 28 px, `headline-sm`.
- No anidar más de dos niveles de tarjetas.

### 4.3 Chips, badges y pills

- Radio full, padding `4px 10px`, `label-sm`/`label-md`, 600-700.
- Punto de estado de 6 px cuando aplique (`Al día`, `Pendiente`, `Gestión activa`).
- `Gestión activa`: fondo cian 10 %, borde cian 30 %, punto cian con `animate-pulse` (desactivar con reduced motion).
- Nunca usar el color como único portador del estado: siempre texto.

### 4.4 Métricas / stats

- Tarjeta de 2 columnas en móvil.
- Etiqueta 12 px `--text-muted` + ícono 20 px (acento según métrica).
- Valor en Montserrat 800, 28-30 px.
- Delta o ayuda en 11-12 px; solo si el dato es real y tiene sentido (no inventar «+18 %»).
- Métricas v1: bailarines activos (magenta), pendientes del mes (ámbar), pagadas del mes (cian), ingresos históricos (violeta).

### 4.5 Formularios

- Etiqueta `label-lg` 14 px/600; ayuda `label-md` 12 px `--text-muted`; obligatorio con asterisco `--primary`.
- Input/select: alto 48 px, fondo `--surface-input`, borde 1 px `--border`, radio 12 px, padding 0 16 px; con ícono, `padding-left: 44px`.
- Texto de input 16 px (evita zoom en iOS), color `--text`, placeholder `--text-muted` al 60 %.
- Foco: borde `--focus` + anillo `0 0 0 3px rgba(34, 211, 238, 0.20)`; alternativa magenta en flujos de pago.
- Select: ícono `expand_more` a la derecha, `appearance: none`.
- Toggle: 48 × 28 px, riel `--surface`/`--border`, activo `--primary` con glow; thumb blanco de 24 px; `role="switch"` + `aria-checked`.
- Errores: borde `--danger`, texto 12 px `--danger` bajo el campo, `aria-describedby`.
- Mantener la selección de sucursal/grupo al recargar (old input / query params).
- No usar placeholders como etiquetas.

### 4.6 Filas de lista

- Contenedor `--surface-card`, radio 16 px, padding 14 px, borde 1 px.
- Avatar de 40 px con iniciales (12 px, 700) y tinte por estado/danza.
- Nombre 14 px/600; metadato 12 px `--text-muted` (grupo · sucursal, día de pago).
- Derecha: pill de estado + acción/chevron.
- Separación entre filas: 10-12 px; sin líneas divisorias dentro de la tarjeta.

### 4.7 Navegación

- Tabs inferiores: ver 3.3.
- Tabs de filtro (mes, rango, estado): pills horizontales con scroll si no caben; activo con fondo `--primary-soft`, borde `--primary`, texto `--text`.
- Breadcrumb/volver: ícono `arrow_back` + texto 14 px, área táctil 44 px.

### 4.8 Toasts

- Fijo abajo, `bottom: calc(80px + env(safe-area-inset-bottom))`, `left/right: 16px`.
- Fondo `--surface-card` (no negro puro), borde 1 px, radio 12 px, texto 13 px, ícono de estado.
- Duración ~2.6 s; éxito con `check_circle` cian/verde, error con `error` ámbar/rojo.
- No bloquear la navegación; `role="status"` y `aria-live="polite"`.

### 4.9 Diálogos y confirmaciones

- Overlay `rgba(0,0,0,0.60)` + blur 8 px.
- Tarjeta centrada de hasta 360 px, radio 24 px, título `headline-sm`, texto 14 px.
- Acciones: secundaria «Cancelar» + destructiva/primaria a la derecha.
- Para anulaciones, pedir motivo opcional dentro del diálogo.
- Cerrar con `Escape`, clic en overlay y botón visible; enfocar el primer campo/acción.

### 4.10 Estados vacíos y carga

- Vacío: ícono 32 px `--text-muted`, título 14 px, texto 12-13 px, CTA opcional.
- Carga: skeleton con superficies `--surface-alt` y pulso suave; nunca spinner de página completa si se puede evitar.
- Error de página: tarjeta con mensaje claro y acción «Reintentar».
- Paginación: botones de 44 px, activo con fondo `--primary`, deshabilitado con opacidad.

### 4.11 Selector de mes / rango

- Control de 44-48 px, fondo `--surface-input`, borde, ícono `calendar_month`.
- Mes en formato `MMMM YYYY` en español (`noviembre 2026`).
- Atajos de rango en pills: Hoy · Semana · Mes · Personalizado.
- Mantener el periodo en la URL (`?month=YYYY-MM` o `from`/`to`).

## 5. Pantallas de referencia (v1)

El mockup cubre con detalle la landing, el alta de alumno y un panel de inscripción. Las demás pantallas del MVP se construyen con los mismos patrones.

### 5.1 Panel de Dirección (dashboard)

Referencia: «Bafoldanz - Gestión Panel Esencial» y variante «Panel de Dirección».

1. Header estándar.
2. Badge «Gestión activa» (opcional) + `h1` «Panel de Dirección» + subtítulo «Resumen de BAFOLDANZ · Centro y Norte».
3. Selector de mes (`?month=`) alineado a la derecha del título o debajo.
4. Dos tarjetas hero: **Bailarines activos** (magenta) y **Pendientes del mes** (ámbar).
5. Dos métricas compactas: **Pagadas del mes** (cian) e **Ingresos históricos** (violeta).
6. CTA primario a ancho completo: «Agregar bailarín» (en la inscripción pública futura será «Registrar Nuevo Alumno»).
7. Sección «Pendientes del mes»: hasta 5 filas con nombre, grupo · sucursal, «Vence el día X» y pill «Pendiente»; enlace «Ver todos» a `/payments`.
8. Si no hay pendientes: estado vacío positivo («Todo al día»).

> No usar «Nuevas Solicitudes» ni porcentajes de crecimiento en v1: dependen de módulos futuros o de datos que no existen.

### 5.2 Alta de bailarín

Referencia: «Bafoldanz - Registro de Nuevo Alumno».

1. `h1` «Agregar bailarín»; sin badge decorativo. (El mockup usa «Registrar Alumno»; ese copy se reserva para la inscripción pública v1.1.)
2. **Datos personales** (tarjeta): Nombre completo (obligatorio). No incluir fecha de nacimiento en v1: no existe en el modelo.
3. **Asignación de elenco** (tarjeta): Sucursal/Sede (obligatoria) y **Grupo** (obligatorio). El mockup omite el grupo; la app debe incluirlo, filtrarlo por sucursal y mostrar el horario como ayuda. La sucursal se deriva del grupo, pero se mantiene el filtro visual.
4. **Mensualidad y cobros** (tarjeta):
   - Toggle «¿Aplica tarifa con promoción?»: solo marca `is_promo`; no bloquea la edición del monto.
   - Monto (Bs.): al elegir grupo se autocompleta la tarifa de la sucursal (Centro 120 / Norte 60); siempre editable. El «Bs. 220» del mockup es placeholder.
   - Día de cobro mensual: selector del 1 al 28 (no solo 5/10/15/20).
5. Acciones: primaria «Guardar bailarín»; secundaria «Cancelar» → vuelve al listado. (En la inscripción pública futura: «Guardar e Inscribir Alumno» / «Cancelar / Descartar».)
6. Validación inline, conservar valores al recargar, toast de éxito/error.

### 5.3 Listado de bailarines

Patrón extrapolado del mockup (no hay pantalla específica):

1. Título «Bailarines» + CTA «Agregar bailarín».
2. Tarjeta de filtros: búsqueda, estado (activos/retirados), sucursal y tipo de grupo; botón «Filtrar» y «Limpiar».
3. Contador de resultados y orden `name ASC`.
4. Filas: avatar, nombre, grupo · sucursal, mensualidad, pill Activo/Retirado, acciones «Editar» y «Retirar».
5. Paginación server-side fija de 10, conservando filtros.
6. Estado vacío con CTA de alta.

### 5.4 Asistencia

Patrón extrapolado (el mockup no la incluye):

1. Título «Asistencia» + fecha (por defecto hoy en La Paz) + filtro de grupo.
2. Resumen del día: presentes, faltas, justificadas.
3. Filas de bailarines con seis botones de marca `P F J R H S`; el seleccionado se rellena con su color semántico (P cian, F danger, J ámbar, R violeta, H neutro, S muted).
4. CTA «Guardar asistencia»; toast «Asistencia registrada».
5. Estado vacío si no hay bailarines en el grupo.

### 5.5 Pagos

Patrón extrapolado:

1. Título «Pagos» + selector de mes.
2. Stat cards: pagados, pendientes, recaudado del mes.
3. Pestañas o filtros «Pendientes» / «Pagados» / «Todos».
4. Filas con nombre, grupo, monto y pill; acción «Cobrar» (primaria) o «Anular» (destructiva) según corresponda.
5. Formulario de cobro: bailarín, mes, monto (solo lectura), medio (Efectivo / QR), directora; botón «Registrar mensualidad completa».
6. Anulación: diálogo con motivo opcional y confirmación.
7. Estados vacíos: «Sin pagos este mes», «No hay pendientes».

### 5.6 Finanzas

Patrón extrapolado:

1. Título «Finanzas» + atajos de rango (Hoy / Semana / Mes / Personalizado).
2. Stat cards: ingresos, salidas, saldo; el saldo usa el acento principal y puede ser negativo en danger.
3. Tarjeta «Cierre del mes»: reserva editable, disponible y reparto 50/50; nota de que es una simulación y no registra salidas.
4. CTA «Registrar salida» + listado de movimientos con fecha, concepto, monto y acción «Anular».
5. Formulario de salida: concepto, monto, fecha, directora (opcional); toast «Salida registrada».
6. Estado vacío: «Sin movimientos en este periodo».

## 6. Copys

| Contexto | Texto |
|---|---|
| Marca (app) | `BAFOLDANZ` · `Panel de Control` |
| Marca (pública futura) | `BAFOLDANZ` · `Ballet Folklórico` |
| Dashboard | `Panel de Dirección` · `Resumen de BAFOLDANZ · Centro y Norte` |
| Alta | `Agregar bailarín` · `Guardar bailarín` · `Cancelar` (mockup público: `Registrar Alumno` · `Guardar e Inscribir Alumno` · `Cancelar / Descartar`) |
| Listado | `Bailarines` · `Agregar bailarín` · `Buscar` · `Filtros` · `Limpiar` |
| Formulario | `Sucursal` · `Grupo` · `Mensualidad (Bs.)` · `Día de cobro mensual` · `¿Aplica tarifa con promoción?` · `Monto (Bs.)` |
| Pagos | `Registrar mensualidad completa` · `Medio de pago` · `Mes que se paga` · `Anular pago` |
| Finanzas | `Registrar salida` · `Concepto` · `Monto (Bs.)` · `Directora` · `Reserva para caja chica (Bs.)` · `Cierre del mes` |
| Asistencia | `Guardar asistencia` · `P · Presente` · `F · Falta injustificada` · `J · Falta justificada` · `R · Reposición` · `H · Feriado` · `S · Suspendida por academia` |
| Estados | `Pagado` (alias visual `Al día`) · `Pendiente` · `Anulado` · `Activo` · `Retirado` |
| Acciones | `Editar` · `Retirar` · `Reactivar` · `Cobrar` · `Anular` · `Ver todos` |
| Toasts de éxito | `Bailarín agregado` · `Bailarín actualizado` · `Asistencia registrada` · `Pago completo registrado` · `Pago anulado` · `Salida registrada` · `Salida anulada` |
| Toasts de error | `Registra primero un bailarín` · `Esta mensualidad ya está registrada` · `No se pudo guardar` |
| Vacíos | `Sin bailarines` · `Aún no hay asistencias` · `Sin pagos este mes` · `No hay pendientes` · `Sin movimientos en este periodo` |
| Ayudas | `Solo pagos completos · Sin abonos · Efectivo o QR.` · `Una caja general · Salidas con su concepto · Reparto 50/50.` · `Cada bailarín puede tener una tarifa promocional permanente mientras permanezca activo.` |

Reglas de copy:

- Español neutro con términos bolivianos de la academia (`Bs.`, `La Paz`, `sucursal`).
- Tuteo consistente («Registra», «Elige»), no mezclar con usted.
- No copiar textos de demostración del mockup (`Gestión 2025`, `+18 %`, `100 % seguro`, testimonios, «Bs. 220», sedes de otros departamentos).
- `Al día` es un alias visual de `Pagado`; el estado persistido y los reportes siguen usando `Pagado`.

## 7. Accesibilidad

- Contraste AA: blanco sobre `#FF1D68` da ≈3.7:1 y **no alcanza** para texto normal; para botones con texto blanco usar `#E01558` (≈4.8:1) o subir a 19 px/700, o usar texto oscuro `#1A0008` sobre el magenta. Verificar con herramienta antes de cerrar la UI.
- Foco visible de 2-3 px cian/magenta en todos los controles; no eliminar el outline sin reemplazo.
- `aria-label` en botones de solo ícono; `aria-current="page"` en la tab activa; `role="switch"` en toggles.
- Estados con texto además de color; errores asociados con `aria-describedby`.
- No usar solo hover para información; todo debe funcionar con teclado y toque.
- `prefers-reduced-motion` para pulsos, glows animados y escalados.
- Formularios con `autocomplete` adecuado (`name`, `tel`, `off` para montos).
- `lang="es"` en `<html>`; títulos de página descriptivos.

## 8. Implementación

### 8.1 Variables CSS base

```css
:root {
  --bg: #0a0a0e;
  --bg-public: #0b0b0f;
  --surface: #131318;
  --surface-card: #161620;
  --surface-alt: #1b1b26;
  --surface-input: #181822;
  --border: #262638;
  --border-strong: #2e2d42;
  --text: #f4f4f8;
  --text-muted: #9898ab;
  --primary: #ff1d68;
  --primary-hover: #e01558;
  --primary-soft: rgba(255, 29, 104, 0.12);
  --primary-glow: rgba(255, 29, 104, 0.35);
  --secondary: #ffb800;
  --tertiary: #22d3ee;
  --violet: #9d4edd;
  --success: #00e676;
  --warning: #fbbf24;
  --danger: #ffb4ab;
  --danger-bg: #93000a;
  --focus: #22d3ee;
  --font-headline: "Montserrat", system-ui, sans-serif;
  --font-body: "Plus Jakarta Sans", system-ui, sans-serif;
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 24px;
  --radius-full: 9999px;
  --shadow-card: 0 0 15px rgba(168, 85, 247, 0.15);
  --shadow-primary: 0 4px 20px rgba(255, 29, 104, 0.35);
  --shadow-nav: 0 -8px 24px rgba(0, 0, 0, 0.45);
  --header-h: 64px;
  --nav-h: 64px;
}
```

### 8.2 Fuentes e íconos

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
```

- Si producción debe funcionar sin CDN, **autoalojar** las fuentes e íconos en `public/` o compilarlos con Vite.
-Blade usa Tailwind (v4) como librería de estilo y variables CSS en `resources/css/app.css`; el mockup usa Tailwind por CDN como prototipo, aquí se usa el paquete ya instalado.

### 8.3 Componentes Blade

Reutilizar `resources/views/components/ui/`: `button`, `input`, `select`, `textarea`, `card`, `stat`, `pill`, `toggle`, `alert`, `toast`, `modal`, `empty-state`, `pagination`, `avatar`, `month-selector`.

- El layout `layouts/app.blade.php` concentra header, nav, fuentes, variables CSS y el contenedor de toasts.
- No duplicar estilos entre vistas; cada componente expone variantes por clase.
- JS por pantalla en `resources/js/pages/` (autocompletar tarifa, filtrar grupos, toggle de promo, confirmaciones, toasts).

### 8.4 Orden de implementación (F2a.5 / F3)

1. Variables CSS + fuentes + header + nav inferior + contenedor base.
2. Botones, inputs, selects, cards, pills, stats, toasts y estados vacíos.
3. Dashboard, listado y alta de alumnos.
4. Asistencia, pagos y finanzas.
5. Revisión visual a 390 px y 1280 px; teclado y lectores de pantalla.
6. Actualizar capturas/README si cambia la navegación.

### 8.5 Tokens retirados

Quedan prohibidos en código nuevo:

- Fondo claro `#f7f8fc`, tarjetas blancas, header blanco y barra arcoíris.
- Tipografía Georgia y `-apple-system` como fuente de marca.
- Morado `#7e32d1` / `#782bd0` / `#8b35d4` como acento principal.
- Emojis en el header (🌈) y textos `Digital` con color.
- La estructura de 5 tabs de la demo anterior como única navegación: se conserva la operativa v1, pero con el componente y estilo nuevos.

## 9. Fuera de alcance v1

El mockup también propone landing pública, formulario de clase de prueba, solicitudes/leads, danzas/elencos, salones en vivo, aforo, asistencia semanal, avance curricular, becas, testimonios y WhatsApp. **No se implementan en v1.** Su análisis, datos placeholder y decisión de aplazamiento están en `07-revision-mockup.md`.
