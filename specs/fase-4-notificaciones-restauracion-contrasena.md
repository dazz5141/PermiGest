# Fase 4: notificaciones de restauracion y contrasena

## Estado

Implementado y validado.

## Contexto y problema

Las restauraciones de dias y los restablecimientos administrativos de contrasena quedan registrados y auditados, pero el usuario afectado no recibe una alerta directa.

## Objetivo

Notificar al usuario afectado despues de una restauracion de dias o de un restablecimiento administrativo de contrasena, sin exponer datos sensibles ni alterar los flujos existentes.

## Actores y destinatarios

- Administrador o encargado autorizado: ejecuta la operacion existente.
- Solicitante: recibe la notificacion de dias restaurados.
- Usuario cuya contrasena fue restablecida: recibe la alerta de seguridad.

## Alcance

### Incluye

- Correo al solicitante despues de registrar una restauracion total o parcial.
- Alerta de seguridad al usuario despues de restablecer su contrasena.
- Despacho posterior a la transaccion y auditoria de cada operacion.
- Pruebas de destinatario, contenido seguro, validacion y rollback.

### Excluye

- Avisos por activacion o desactivacion de cuenta.
- Recordatorios de solicitudes pendientes.
- SMTP, nuevas rutas, vistas, migraciones o cambios de base de datos.
- Contrasenas, hashes, tokens, motivo medico, observaciones y documentos de restauracion en los correos.

## Reglas y requisitos

- REQ-401: una restauracion confirmada debe notificar una vez al solicitante.
- REQ-402: el correo debe indicar folio, tipo de restauracion y cantidad de dias restaurados.
- REQ-403: un restablecimiento confirmado debe alertar una vez al usuario afectado.
- REQ-404: la alerta debe indicar que la contrasena cambio y recomendar contactar al administrador si no reconoce la accion.
- SEC-401: las notificaciones solo se generan despues de confirmar persistencia y auditoria.
- SEC-402: una validacion, autorizacion o transaccion fallida no debe generar notificaciones.
- SEC-403: ningun correo debe incluir contrasenas, hashes ni tokens.
- SEC-404: el correo de restauracion no debe incluir motivo, observacion ni documento de referencia.
- SEC-405: solo el usuario afectado recibe cada notificacion.

## Diseno tecnico

- `DiasRestauradosSolicitante` extendera `PermiGestNotification` y usara el canal `mail`.
- `ContrasenaRestablecidaUsuario` extendera `PermiGestNotification` y usara el canal `mail`.
- Ambos controladores devolveran el modelo afectado desde su transaccion y notificaran fuera de ella.
- El aviso de restauracion enlazara al detalle autenticado de la solicitud.
- La alerta de contrasena no incluira una accion que revele o modifique credenciales.

## Criterios de aceptacion

- AC-401: una restauracion valida genera una sola notificacion al solicitante.
- AC-402: el contenido de restauracion muestra los datos operativos permitidos y omite antecedentes sensibles.
- AC-403: una restauracion fallida no persiste ni notifica.
- AC-404: un restablecimiento valido genera una sola alerta al usuario afectado.
- AC-405: la alerta no contiene la contrasena nueva, hash ni token de sesion.
- AC-406: una validacion, autorizacion o auditoria fallida no notifica.
- AC-407: la suite existente permanece aprobada.

## Casos de prueba minimos

- Restauracion valida y destinatario exacto.
- Restauracion rechazada por saldo y ausencia de notificacion.
- Fallo de auditoria de restauracion con rollback y ausencia de notificacion.
- Restablecimiento valido y destinatario exacto.
- Contrasena actual incorrecta y ausencia de notificacion.
- Fallo de auditoria del restablecimiento con rollback y ausencia de notificacion.
- Inspeccion del contenido seguro de ambos correos.

## Tareas

- [x] TASK-401: crear ambas notificaciones. Requisitos: REQ-401 a REQ-404, SEC-403 a SEC-405.
- [x] TASK-402: integrar restauracion y despacho posterior a commit. Requisitos: SEC-401, SEC-402.
- [x] TASK-403: integrar restablecimiento y despacho posterior a commit. Requisitos: SEC-401, SEC-402.
- [x] TASK-404: agregar y adaptar pruebas. Criterios: AC-401 a AC-406.
- [x] TASK-405: ejecutar suite, Pint y revision de diff. Criterio: AC-407.

## Riesgos y dependencias

- Los mensajes usan la cola y el transporte configurados; la fase no valida un SMTP real.
- La entrega externa puede fallar sin revertir una operacion ya confirmada, pero el job quedara sujeto al manejo normal de la cola.
