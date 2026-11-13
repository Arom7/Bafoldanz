# Documentación del proyecto — BAFOLDANZ Digital

Guía de lectura para cualquier sesión futura. Orden recomendado:

1. **`Agents.md`** (raíz del proyecto) — reglas de trabajo: revisión del código, stack, comandos, convenciones y prohibiciones.
2. **`01-contexto-negocio.md`** — qué hace el negocio: sucursales y grupos, alumnos (alta/retiro/reactivación), asistencia, mensualidades (cobro/anulación), caja con filtro de periodo, cierre del mes y roles.
3. **`02-arquitectura.md`** — cómo lo construimos: Laravel MVC (PHP 8.4, Blade, MySQL/Eloquent, sesión web), estructura de carpetas, reglas de controladores/Services, glosario negocio↔código y rutas previstas.
4. **`03-modelo-datos.md`** — esquema de tablas, relaciones, unicidades, semántica de retiro/borrado y reglas de cálculo (pendientes, saldo, reparto 50/50) y estrategia de seeders.
5. **`04-plan-migracion.md`** — decisiones acordadas, definición de MVP, fases con checklists y criterios de aceptación.
6. **`05-despliegue.md`** — dockerización: entornos local y de producción, imagen con uid/gid 1000, BD de tests, backups, Makefile y procedimiento de despliegue.
7. **`06-diseno-ui.md`** — tokens de diseño extraídos del prototipo original (colores, tipografía, estructura, copys, íconos): fuente única de verdad visual.

Resumen rápido:

| Aspecto | Decisión |
|---|---|
| Stack | PHP 8.4, Laravel 13 (MVC + Blade), MySQL/Eloquent, Vite solo para assets, Docker |
| Auth | Sesión web; Sanctum diferido para app móvil futura |
| Roles v1 | 2 directoras (`admin` con spatie, flag `is_director`) |
| Lógica de negocio | Siempre en `app/Services`; controladores delgados |
| Moneda / zona horaria | Bs. (es-BO) · `America/La_Paz` · locale `es` |
| Diseño | Mobile-first (tabs inferiores); tokens en `06-diseno-ui.md` |
| Alcance MVP | Alumnos (con edición), Asistencia, Pagos (con anulación), Finanzas (con anulación y filtro de rango), Dashboard, login/roles, despliegue a producción con backups |
| Retiro de alumnos | `active` + `retired_at` + `enrolled_at` (ventana de cobro); softDeletes solo para borrado administrativo |
| Catálogos v1 | Datos por seeders; CRUD administrable en v1.1 |
| Diferido a v1.1 | PDFs (dompdf), importador/exportador JSON, CRUD de catálogos, exportaciones, conciliación por cuenta |
| Producción | Docker Compose (nginx + php-fpm + mysql + backup); hosting aún pendiente de definir |
| Despliegue local | `make setup` / `make up` / `make test`; contenedores con uid=1000/gid=1000 |
| Revisión del código | Todo código se entrega para revisión: nunca ejecutar `git commit` ni `git push` |
