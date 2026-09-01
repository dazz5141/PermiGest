# Fase 3: notificacion de aprobacion y rechazo

## Estado

Implementado y validado.

## Objetivo

Notificar al solicitante cuando su permiso sea aprobado o rechazado y exigir un motivo cuando la decision sea rechazo.

## Actores y destinatarios

- Jefe directo autorizado: resuelve la solicitud pendiente.
- Solicitante: unico destinatario de la notificacion de resultado.

## Alcance

### Incluye

- Correo de aprobacion o rechazo al solicitante.
- Folio, resultado, tipo, fechas, dias y comentario de la resolucion.
- Motivo obligatorio al rechazar y comentario opcional al aprobar.
- Ajuste minimo de los dos modales existentes para reflejar la regla.
- Despacho posterior a la transaccion de resolucion y auditoria.

### Excluye

- Avisos al jefe directo, administradores o secretaria.
- Restauraciones, contrasenas y recordatorios.
- Motivo original del permiso y token de validacion en el correo.
- SMTP, nuevas rutas, migraciones o cambios de base de datos.

## Reglas y requisitos

- REQ-301: una aprobacion confirmada debe notificar una vez al solicitante.
- REQ-302: un rechazo confirmado debe notificar una vez al solicitante.
- REQ-303: el rechazo debe requerir un motivo no vacio de hasta 1000 caracteres.
- REQ-304: la aprobacion puede incluir un comentario opcional.
- REQ-305: el mensaje debe incluir folio, resultado, tipo, fechas, dias y comentario cuando exista.
- SEC-301: solo se notifica despues de confirmar estado, resolucion y auditoria.
- SEC-302: una validacion, autorizacion o transaccion fallida no debe generar notificaciones.
- SEC-303: el correo no debe incluir el motivo original ni el token de validacion.
- SEC-304: el enlace solo conduce al detalle autenticado y no ejecuta la resolucion.

## Diseno tecnico

- `SolicitudResueltaSolicitante` extendera `PermiGestNotification` y usara el canal `mail`.
- `ResolucionController::update()` devolvera la solicitud desde la transaccion y notificara fuera de ella.
- La validacion usara `Rule::requiredIf()` para exigir comentario solo en rechazo.
- Los dos modales alternaran etiqueta, placeholder y atributo `required` segun la accion.

## Criterios de aceptacion

- AC-301: una aprobacion genera una sola notificacion al solicitante con resultado correcto.
- AC-302: un rechazo con motivo genera una sola notificacion al solicitante con dicho motivo.
- AC-303: un rechazo sin motivo conserva la solicitud pendiente, no crea resolucion y no notifica.
- AC-304: usuarios distintos del solicitante no reciben esta notificacion.
- AC-305: un fallo de auditoria revierte la resolucion y no notifica.
- AC-306: los modales muestran el comentario como obligatorio solo al rechazar.
- AC-307: la suite existente permanece aprobada.

## Tareas

- [x] TASK-301: crear la notificacion de resultado. Requisitos: REQ-301, REQ-302, REQ-304, REQ-305, SEC-303, SEC-304.
- [x] TASK-302: integrar validacion, transaccion y despacho. Requisitos: REQ-303, SEC-301, SEC-302.
- [x] TASK-303: ajustar ambos modales sin redisenarlos. Criterio: AC-306.
- [x] TASK-304: agregar y adaptar pruebas. Criterios: AC-301 a AC-305.
- [x] TASK-305: ejecutar suite, Pint, Blade y revision de diff. Criterio: AC-307.

## Riesgos y dependencias

- Los mensajes se procesan mediante la cola configurada y no prueban un SMTP real.
- Los comentarios ingresados por el jefe se renderizan mediante el sistema de correo escapado de Laravel.
