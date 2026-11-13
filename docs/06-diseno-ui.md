# Diseño UI — BAFOLDANZ Digital

Extrae el diseño de la demo original (`index.html`, eliminada tras la migración). Es la **fuente única de verdad visual** del proyecto: cualquier vista Blade debe respetar estos tokens. Rige el requisito mobile-first (diseñado para iPhone, con adaptación simple a escritorio).

## Identidad

- **Marca (header)**: `🌈 BAFOLDANZ` en `Georgia, serif`, 24px, peso 800, letter-spacing 0.3px — la palabra `Digital` en color `#8b35d4`.
- **Subtítulo**: `Ballet Folclórico de Danza · Panel de directoras` — 12px, `#666`.
- **Saludo (Inicio)**: `¡Hola, directora! 💜` + subtítulo `Resumen de BAFOLDANZ · Centro y Norte`.
- **Firma (footer)**: texto pequeño centrado, 11px, `#777`.

## Paleta y degradado arcoíris

Barra arcoíris bajo el header: altura 5px, radio 10px, degradado 90°:

| Color | Uso |
|---|---|
| `#ef3578` | arcoíris 1 |
| `#f79e24` | arcoíris 2 |
| `#e4d82d` | arcoíris 3 |
| `#2ac98c` | arcoíris 4 |
| `#16a5e8` | arcoíris 5 |
| `#8737db` | arcoíris 6 |

### Colores de texto/estado

| Color | Uso |
|---|---|
| `#19152b` | texto base |
| `#252038` | texto de inputs |
| `#615a6d` | etiquetas (labels) |
| `#666` / `#777` / `#888` | textos secundarios (`muted`), firma, estados vacíos |
| `#792cce` (morado) | stat "Bailarines activos" |
| `#158c54` (verde) | stat "Mensualidades pagadas" |
| `#d97d08` (naranja) | stat "Por regularizar" / "Retiros" |
| `#dc396f` (rosa) | stat "Ingresos registrados" |

### Fondos

| Color | Uso |
|---|---|
| `#f7f8fc` | fondo de página |
| `#ffffff` | header, tarjetas, tabs, inputs |
| `#fff6e4` borde `#f7d99c` | aviso/notice |
| `#eaf8ee` texto `#147f43` | pill de estado positivo ("Pagado") |
| `#fff0dd` texto `#b56b00` | pill de advertencia ("Pendiente", "Por revisar") |
| `#ffebef` texto `#bb2949` | pill de error ("No hay saldo suficiente") |
| `#f0e9fb` texto `#6721a8` | botón secundario |
| `#f1e9fc` texto `#6624ae` | botón pequeño (smallbtn) |
| `#fff0f1` texto `#bd3045` | botón destructivo (anular registros) |
| `#22172e` | toast |

### Botones

| Variante | Estilo |
|---|---|
| Primario | fondo `#7e32d1`, texto blanco, 700, 14px, radio 12px, padding 13px 12px |
| Secundario | fondo `#f0e9fb`, texto `#6721a8` |
| Outline | fondo blanco, texto `#6e2ac4`, borde `1px solid #dac9f5` |
| Rojo (destructivo) | fondo `#fff0f1`, texto `#bd3045` |
| Deshabilitado | opacity .5 |

## Tipografía y jerarquía

- Base: `-apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif` (font del sistema, sin cargar webfonts).
- `h1` (título de sección): 22px, margen `5px 0 4px`.
- `h2` (título de tarjeta): 17px, margen `0 0 13px`.
- Números de stats: 24px, peso 800.
- Saldo destacado (`.balance`): 27px, peso 800.
- Texto secundario (`muted`): 12px.
- Etiquetas (label): 12px, 700, margen 11px 0.
- Inputs y selects: padding 12px, borde `1px solid #d9d7e0`, radio 11px, font-size 16px (evita zoom en iOS), ancho 100%.

## Estructura

- **Header**: fondo blanco, sticky arriba, padding 18px 18px 14px, sombra `0 2px 16px #2221`, con la barra arcoíris debajo.
- **Contenedor** (`.wrap`): max-width 1000px, centrado, padding `16px 15px 95px` (el inferior deja espacio a los tabs fijos).
- **Tabs**: fijas abajo (mobile), fondo blanco, sombra superior `0 -3px 20px #18152820`, padding `9px 3px max(12px, env(safe-area-inset-bottom))`, botones de min-width 58px con ícono (21px) + etiqueta (11px). Tab activa: color `#782bd0`, peso 800.
- **Tarjetas** (`.card`): fondo blanco, radio 18px, padding 17px, sombra `0 3px 18px #29204410`, margen inferior 13px.
- **Grid de stats**: 2 columnas en móvil; fila de "acciones rápidas" a 2 columnas con botones.
- **Items de lista** (`.item`): padding 13px 0, borde inferior `1px solid #eee` (sin borde el último), flex con `space-between`.
- **Pills**: radio 20px, padding `5px 8px`, 11px, 700.
- **Botón pequeño** (`.smallbtn`): radio 9px, padding 8px, 700.
- **Toast**: fijo abajo (bottom 85px), `left/right 15px`, fondo `#22172e`, texto blanco, radio 12px, centrado, se muestra ~2.6s. En Laravel: flash messages con este estilo.
- **Estados vacíos** (`.empty`): padding 20px, centrado, `#888` (ej. "Sin bailarines", "Aún no hay asistencias").

## Íconos y etiquetas de las 5 pestañas

| Ícono | Etiqueta | Ruta |
|---|---|---|
| ⌂ | Inicio | `/` (dashboard) |
| ♧ | Bailarines | `/students` |
| ✓ | Asistencia | `/attendance` |
| ▣ | Pagos | `/payments` |
| ◈ | Finanzas | `/finance` |

## Textos de UI (copys) a respetar

- Aviso de secciones: la demo usaba un aviso de "versión de prueba"; en producción **no aplica** (no copiar).
- Formularios: "Agregar bailarín", "Guardar bailarín", "Registrar mensualidad completa", "Guardar movimiento", "Guardar asistencia".
- Etiquetas de formularios: `Sucursal`, `Grupo`, `Mensualidad (Bs.)`, `Día fijo de pago`, `Tarifa promocional permanente`, `Fecha`, `Bailarín`, `Marca`, `Mes que se paga`, `Medio de pago`, `Tipo`, `Concepto`, `Monto (Bs.)`, `Origen`, `Destino`, `Directora`, `Reserva para caja chica (Bs.)`.
- Marcas de asistencia: `P · Presente`, `F · Falta injustificada`, `J · Falta justificada`, `R · Reposición`, `H · Feriado`, `S · Suspendida por academia`.
- Estados: `Pagado` / `Pendiente` (pills), `Por revisar` (alertas), `Retirar` / `Reactivar` (smallbtn), `Bailarín retirado` (en historiales).
- Mensajes de éxito (toast): "Bailarín agregado", "Asistencia registrada", "Pago completo registrado", "Movimiento registrado". (El mensaje "Demo restablecida" del prototipo no aplica: ya no existe el botón de reset; el "anulado con éxito" de anulaciones seguirá este estilo.)
- Mensajes de error (toast): "Registra primero un bailarín", "Esta mensualidad ya está registrada", "Origen y destino deben ser diferentes", "No se pudo guardar en este navegador".
- Textos informativos: "Registra tarifas individuales y fecha fija de renovación." (Bailarines); "Solo pagos completos · Sin abonos · Efectivo o QR." (Pagos); "Una caja general · Una cuenta QR · Reparto 50/50." (Finanzas); "P presente · F falta · J justificada · R reposición · H feriado · S suspendida por academia." (Asistencia).
- Cierre del mes: título "Cierre del mes · Simulación", subtítulo "Ingresos menos gastos. Indica cuánto reservar antes del reparto.", nota "El cálculo no registra automáticamente retiros. La caja chica es dinero reservado, no un gasto."
- Sucursales (tarjeta del dashboard): "Centro · Tarifa regular Bs. 120", "Norte · Tarifa regular Bs. 60", nota "Cada bailarín puede tener una tarifa promocional permanente mientras permanezca activo."

## Adaptación a escritorio (media query ≥ 750px)

- Tabs pasan a estáticas (bajo el header, no fijas abajo), alineadas a la izquierda con `gap 20px`, sin sombra y con borde inferior.
- Botón de tab: ícono y texto en línea (17px ícono, 14px texto).
- Grid de stats y acciones: 4 columnas.
- Contenedor sin padding inferior extra.

## Notas de implementación

- `env(safe-area-inset-bottom)` es obligatorio en los tabs fijos (iPhone con notch).
- Font-size 16px en inputs evita el zoom automático de iOS.
- Colores de acento derivados: primario `#7e32d1` / `#782bd0` (tab activa) / `#8b35d4` (logo); pueden unificarse como variable CSS `--primary` al implementar.
- Las sombras de la demo usan alpha (ej. `#29204410`); conservarlas tal cual.
- La demo era una sola página con secciones ocultas; en Laravel cada pestaña es una ruta propia con el mismo layout.
