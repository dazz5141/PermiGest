# Fase 5: recordatorios de solicitudes pendientes

## Estado

Implementado y validado.

## Contexto y problema

La jefatura recibe un aviso al crearse una solicitud, pero no existe un recordatorio cuando esta permanece pendiente de resolucion.

## Objetivo

Recordar a la jefatura responsable las solicitudes que llevan al menos 24 horas pendientes, evitando duplicados y sin cambiar estados ni reglas del permiso.

## Actores y destinatarios

- Sistema: ejecuta el proceso programado.
- Validador asignado activo: unico destinatario del recordatorio.
- Solicitante: no recibe recordatorios adicionales en esta fase.

## Alcance

### Incluye

- Orden de consola para procesar solicitudes pendientes elegibles.
- Ejecucion programada de lunes a viernes a las 08:00, hora de Santiago.
- Un recordatorio maximo por solicitud y por dia local.
- Registro de auditoria usado tambien para deduplicacion.
- Correo con folio, solicitante, tipo, fechas y dias solicitados.
- Revalidacion transaccional antes de registrar y encolar cada aviso.

### Excluye

- Recordatorios al solicitante, administradores o secretaria.
- Fines de semana, escalamiento y configuracion personalizada del plazo.
- Motivo original, observaciones y token de validacion en el correo.
- SMTP, supervisor, cron de produccion, migraciones o nuevas tablas.

## Supuestos conservadores

- El plazo inicial es de 24 horas corridas desde la creacion de la solicitud.
- La jefatura responsable corresponde a `validador_id` y debe estar activa.
- Una ejecucion manual repetida el mismo dia no debe duplicar el aviso.

## Reglas y requisitos

- REQ-501: una solicitud pendiente por al menos 24 horas debe generar un recordatorio a su validador activo.
- REQ-502: una solicitud reciente, resuelta o sin validador activo no debe generar recordatorio.
- REQ-503: cada solicitud puede generar como maximo un recordatorio por dia en `America/Santiago`.
- REQ-504: el correo debe incluir folio, solicitante, tipo, fechas y dias solicitados.
- REQ-505: la orden debe informar cantidades enviadas, omitidas y fallidas.
- SEC-501: antes de enviar, el sistema debe bloquear y revalidar estado, antiguedad, destinatario y deduplicacion.
- SEC-502: el registro de auditoria y el despacho deben coordinarse dentro de la misma transaccion.
- SEC-503: el correo no debe incluir motivo, observaciones ni token de validacion.
- SEC-504: ejecuciones programadas solapadas deben impedirse.
- SEC-505: el fallo de una solicitud no debe impedir procesar las restantes y debe producir codigo de salida fallido.

## Diseno tecnico

- `SolicitudPendienteRecordatorio` extendera `PermiGestNotification` y usara el canal `mail`.
- `EnviarRecordatoriosSolicitudesPendientes` procesara candidatos en lotes y transacciones independientes.
- La accion `recordatorio_solicitud_pendiente` en `auditorias` identificara un envio diario ya registrado.
- `routes/console.php` programara la orden con `weekdays()`, `at('08:00')`, zona horaria de Santiago y `withoutOverlapping()`.
- El enlace conducira al listado autenticado de resoluciones.

## Criterios de aceptacion

- AC-501: una solicitud elegible genera un solo correo al validador asignado.
- AC-502: dos ejecuciones el mismo dia mantienen un solo correo y una sola auditoria.
- AC-503: solicitudes recientes, resueltas o con validador inactivo no notifican.
- AC-504: el contenido permitido es correcto y omite datos sensibles.
- AC-505: un fallo de auditoria no encola el recordatorio de esa solicitud.
- AC-506: la orden continua con otros candidatos ante un fallo individual.
- AC-507: el scheduler muestra la ejecucion configurada en hora de Santiago.
- AC-508: la suite existente permanece aprobada.

## Casos de prueba minimos

- Solicitud pendiente con mas de 24 horas y validador activo.
- Segunda ejecucion durante el mismo dia.
- Solicitud pendiente reciente.
- Solicitud aprobada o rechazada.
- Solicitud con validador inactivo.
- Fallo de auditoria junto con otro candidato valido.
- Inspeccion de contenido seguro y destinatario.
- Inspeccion de la programacion registrada.

## Tareas

- [x] TASK-501: crear la notificacion de recordatorio. Requisitos: REQ-504, SEC-503.
- [x] TASK-502: crear la orden con seleccion, revalidacion, auditoria y tolerancia a fallos. Requisitos: REQ-501 a REQ-503, REQ-505, SEC-501, SEC-502, SEC-505.
- [x] TASK-503: registrar la programacion protegida. Requisito: SEC-504.
- [x] TASK-504: agregar pruebas funcionales y de seguridad. Criterios: AC-501 a AC-507.
- [x] TASK-505: ejecutar suite, Pint y revision de diff. Criterio: AC-508.

## Riesgos y dependencias

- En produccion sera necesario ejecutar `php artisan schedule:run` cada minuto mediante cron o equivalente; queda fuera de esta fase.
- Los correos requieren un worker de cola y transporte configurado.
- El plazo de 24 horas y el horario pueden parametrizarse en una fase futura si el negocio lo requiere.
