# Ajuste de notificaciones - Fase 1: destinatarios y vigencia

## Estado

Implementado y validado.

## Contexto y problema

Una solicitud puede asignarse a una jefatura desactivada y los avisos encolados que indican estado pendiente pueden entregarse despues de que la solicitud ya fue resuelta o reasignada.

## Objetivo

Garantizar que las solicitudes nuevas se asignen a una jefatura activa y que los mensajes pendientes solo se entreguen si siguen siendo vigentes al momento real del envio.

## Alcance

### Incluye

- Validar actividad y rol del jefe directo antes de asignarlo.
- Usar como alternativa solo una jefatura activa.
- Impedir la creacion cuando no exista una jefatura activa.
- Revalidar estado pendiente, destinatario asignado, rol y actividad antes de enviar avisos a jefatura.
- Aplicar la revalidacion al aviso inicial y al recordatorio diario.
- Pruebas con director inactivo, alternativa activa y cola atrasada.

### Excluye

- Reintentos, backoff y manejo de fallos al encolar, correspondientes a la fase 2.
- Cambios en estados, permisos, rutas, vistas, migraciones o base de datos.
- Reasignacion automatica de solicitudes ya existentes.

## Reglas y requisitos

- REQ-A101: una solicitud nueva solo puede asignarse a un usuario activo con rol `jefe_directo`.
- REQ-A102: si el jefe configurado no es elegible, el sistema puede usar otra jefatura activa existente.
- REQ-A103: si no existe una jefatura activa, la solicitud no se crea ni notifica.
- REQ-A104: el aviso inicial a jefatura y el recordatorio solo se envian si la solicitud sigue pendiente.
- REQ-A105: al entregar el correo, el destinatario debe seguir activo, conservar el rol de jefatura y coincidir con `validador_id`.
- SEC-A101: la revalidacion debe consultar el estado persistido y no confiar en el modelo serializado por la cola.
- SEC-A102: la correccion no debe ampliar permisos ni exponer datos adicionales.

## Diseno tecnico

- `SolicitudController::store()` comprobara rol y actividad del jefe configurado y del fallback.
- `SolicitudCreadaJefeDirecto` y `SolicitudPendienteRecordatorio` implementaran `shouldSend()`.
- `shouldSend()` consultara la solicitud actual por ID, el estado base pendiente y la relacion con el destinatario.
- Las notificaciones al solicitante, resoluciones, restauraciones y contrasenas no cambian.

## Criterios de aceptacion

- AC-A101: un jefe directo activo recibe y queda asignado normalmente.
- AC-A102: un jefe directo inactivo no queda asignado ni recibe el aviso.
- AC-A103: una alternativa activa puede quedar asignada cuando el jefe configurado no es elegible.
- AC-A104: sin jefatura activa no se crea solicitud ni se genera notificacion.
- AC-A105: una notificacion encolada se suprime si la solicitud fue resuelta antes de procesarla.
- AC-A106: una notificacion encolada se suprime si el destinatario fue desactivado, cambio de rol o dejo de ser el validador.
- AC-A107: la suite existente permanece aprobada.

## Tareas

- [x] TASK-A101: reforzar seleccion de jefatura activa. Requisitos: REQ-A101 a REQ-A103.
- [x] TASK-A102: agregar revalidacion de entrega a ambos avisos pendientes. Requisitos: REQ-A104, REQ-A105, SEC-A101.
- [x] TASK-A103: agregar pruebas de asignacion y cola atrasada. Criterios: AC-A101 a AC-A106.
- [x] TASK-A104: ejecutar suite, Pint y revision de diff. Criterio: AC-A107.

## Riesgos y dependencias

- Las solicitudes pendientes existentes con un validador inactivo no se reasignan en esta fase.
- La confiabilidad ante fallos de infraestructura de cola permanece pendiente para la fase 2.
