# Fase 2: notificacion de solicitud creada

## Estado

Implementado y validado.

## Objetivo

Notificar por correo al funcionario y al jefe directo asignado cuando una solicitud de permiso se crea correctamente.

## Actores y destinatarios

- Solicitante: usuario autenticado que crea la solicitud.
- Jefe directo: usuario guardado en `solicitudes.validador_id` al crear la solicitud.

## Alcance

### Incluye

- Confirmacion de recepcion al solicitante.
- Aviso de solicitud pendiente al jefe directo asignado.
- Folio, tipo, fechas, dias y enlaces autenticados al sistema.
- Creacion de solicitud y auditoria dentro de una transaccion.
- Pruebas de destinatarios, contenido minimo y ausencia de envios ante validacion fallida.

### Excluye

- Aprobacion, rechazo, restauraciones, contrasenas y recordatorios.
- Motivo u otros datos sensibles dentro del correo.
- SMTP y puesta en marcha de produccion.
- Cambios en rutas, vistas, permisos, migraciones o estructura de base de datos.

## Reglas y requisitos

- REQ-201: una solicitud creada debe generar una confirmacion para su solicitante.
- REQ-202: una solicitud creada debe generar un aviso para el usuario asignado en `validador_id`.
- REQ-203: cada destinatario debe recibir una sola notificacion por solicitud.
- REQ-204: las notificaciones deben incluir folio, tipo, fechas y dias solicitados.
- REQ-205: el solicitante debe acceder al detalle y el jefe directo a su bandeja de resoluciones.
- SEC-201: los correos no deben incluir contrasenas, token de validacion ni motivo del permiso.
- SEC-202: una solicitud invalida o no persistida no debe producir notificaciones.
- SEC-203: la solicitud y su auditoria deben persistirse atomicamente antes del despacho.
- SEC-204: los enlaces deben conducir a rutas autenticadas existentes y no autorizar acciones por correo.

## Diseno tecnico

- `SolicitudCreadaSolicitante` y `SolicitudCreadaJefeDirecto` extenderan `PermiGestNotification`.
- Ambas usaran el canal `mail` y `MailMessage` de Laravel.
- `SolicitudController::store()` encapsulara creacion y auditoria en una transaccion y notificara despues de confirmarla.
- Las notificaciones cargaran solo las relaciones necesarias al construir el mensaje.

## Criterios de aceptacion

- AC-201: el solicitante recibe una notificacion por correo al crear una solicitud valida.
- AC-202: el jefe guardado como validador recibe una notificacion por correo.
- AC-203: usuarios ajenos no reciben la notificacion.
- AC-204: ambos mensajes identifican correctamente la solicitud sin revelar su motivo.
- AC-205: una entrada invalida no crea solicitud ni genera notificaciones.
- AC-206: la suite existente permanece aprobada.

## Tareas

- [x] TASK-201: crear las dos notificaciones y su contenido seguro. Requisitos: REQ-201 a REQ-205, SEC-201, SEC-204.
- [x] TASK-202: integrar transaccion y despacho en la creacion. Requisitos: SEC-202, SEC-203.
- [x] TASK-203: probar destinatarios, contenido y caso invalido. Criterios: AC-201 a AC-205.
- [x] TASK-204: ejecutar suite, Pint y revision de diff. Criterio: AC-206.

## Riesgos y dependencias

- La entrega se procesa mediante la cola configurada; esta fase prueba el despacho, no un servidor SMTP real.
- `APP_URL` debe representar el entorno correcto para que los enlaces generados sean validos.
