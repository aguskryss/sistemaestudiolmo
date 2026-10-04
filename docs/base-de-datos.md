# Base de datos

MySQL/MariaDB en producción (Hostinger). Migraciones en `database/migrations/2026_10_04_*`.

```mermaid
erDiagram
    users ||--o{ obras : "responsable de"
    users ||--o{ recordatorios : recibe
    users ||--o{ actividad : genera

    estudios ||--o{ obras : "nos subcontrata"
    estudios ||--o{ cotizaciones : "emitidas a"
    clientes ||--o{ obras : "cliente final"
    clientes ||--o{ notas : tiene
    clientes ||--o{ cotizaciones : "emitidas a"

    obras ||--o{ obra_checklist_items : "checklist de inicio"
    obras ||--o{ carpetas : tiene
    carpetas ||--o{ carpetas : subcarpetas
    carpetas ||--o{ documentos : contiene
    documentos ||--|{ documento_versiones : "versiones (vigente = mayor número)"

    obras ||--o{ obra_materiales : necesita
    materiales ||--o{ obra_materiales : ""
    contactos ||--o{ obra_materiales : provee
    obra_materiales ||--o{ obra_material_movimientos : "pedidos / entregas"

    obras ||--o{ tareas : "gantt"
    tareas }o--o{ tareas : "depende de (tarea_dependencias)"
    rubros ||--o{ tareas : ""
    contactos ||--o{ tareas : "asignado"

    obras ||--o{ cotizaciones : ""
    contactos ||--o{ cotizaciones : "recibidas de"
    rubros ||--o{ cotizaciones : ""
    cotizaciones ||--|{ cotizacion_items : ""

    obras ||--o{ permisos : ""
    contactos ||--o{ seguros : ""
    seguros }o--o{ obras : "cubre (obra_seguro)"

    contactos }o--o{ rubros : "contacto_rubro"
    rubros ||--o{ materiales : ""
    obras ||--o{ notas : ""
```

Polimórficas (guardan `tipo` + `id`, con nombres cortos definidos en `AppServiceProvider`):

- `adjuntos` → permisos, seguros, cotizaciones.
- `recordatorios.recordable` → obra, permiso, seguro, cotización, etc.
- `actividad.sujeto` → cualquier entidad (auditoría).

## Decisiones

| Tema | Decisión |
|---|---|
| Subcontratación | `estudios` = estudios que nos pasan obras. Cada obra tiene `estudio_id` (null = obra directa), `codigo_estudio` (cómo la identifican ellos) y `cliente_id` (comitente final). |
| Última versión de un archivo | `documento_versiones` con `numero` incremental; la vigente es la de número mayor. Las anteriores quedan como historial. |
| Materiales | Necesito / pedí / entregaron se calcula desde `obra_material_movimientos`, así se soportan entregas parciales y queda registro de quién, cuándo y con qué remito. |
| Cotizaciones | Una sola tabla con `tipo` = `recibida` (de un contacto) o `emitida` (a un cliente). `obra_id` es opcional para presupuestos previos a la obra. |
| Moneda | `ARS` o `USD` por cotización y por seguro, con `tipo_cambio` opcional. |
| Seguros | Un seguro pertenece a un contacto y puede cubrir varias obras. |
| Inicio de obra | Datos de la obra + checklist que se copia desde `checklist_plantilla_items` (editable). |
| Permisos de acceso | 3 usuarios del estudio, todos ven todo. Roles `admin` (gestiona usuarios y plantillas) y `miembro`. |
| Borrado | Borrado lógico (`deleted_at`) en entidades principales. |
| Archivos | Se guardan fuera de `public_html` y se sirven solo a usuarios autenticados. |
