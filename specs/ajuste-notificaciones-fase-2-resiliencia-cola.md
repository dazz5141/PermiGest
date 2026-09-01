# Ajuste de notificaciones - Fase 2: resiliencia de cola

## Estado

Implementado y validado.

## Contexto y problema

Las operaciones web se confirman antes de encolar sus notificaciones. Si el encolado falla, la respuesta puede indicar error aunque los datos ya quedaron guardados. Ademas, los jobs no definen una politica propia de reintentos y un recordatorio fallido puede conservar su marca diaria e impedir un nuevo intento.

## Objetivo

Desacoplar el resultado funcional de los fallos de encolado, aplicar reintentos controlados a los correos y conservar la capacidad de reintentar recordatorios que no lograron encolarse.

## Alcance

### Incluye

- Despacho seguro y centralizado para notificaciones iniciadas desde controladores web.
- Registro de excepciones de encolado sin devolver un falso error funcional.
- Un intento inicial y tres reintentos por job con pausas progresivas.
- Limpieza de la auditoria diaria del recordatorio cuando falla su encolado.
- Pruebas forzando fallos en la frontera de la cola.

### Excluye

- Outbox transaccional, nuevas tablas o migraciones.
- Configuracion de SMTP, Supervisor, cron o monitoreo externo.
- Cambios de destinatarios, contenido, estados, saldos, rutas o vistas.

## Reglas y requisitos

- REQ-A201: una solicitud, resolucion, restauracion o contrasena confirmada debe conservar su respuesta exitosa si falla el encolado del aviso.
- REQ-A202: cada fallo de encolado debe reportarse al manejador de excepciones.
- REQ-A203: el fallo de un destinatario no debe impedir intentar los demas destinatarios.
- REQ-A204: cada job de notificacion debe permitir un intento inicial y tres reintentos.
- REQ-A205: los reintentos deben usar pausas progresivas de 60, 300 y 900 segundos.
- REQ-A206: un recordatorio no encolado no debe conservar la marca que bloquea otro intento diario.
- SEC-A201: el registro de errores no debe incluir contrasenas, hashes, tokens ni contenido sensible del formulario.
- SEC-A202: una falla de notificacion no debe revertir ni repetir automaticamente una operacion ya confirmada.

## Diseno tecnico

- `NotificacionSegura` envolvera `notify()` con captura de `Throwable`, `report()` y resultado booleano.
- Los cuatro controladores que notifican despues de confirmar datos usaran el despachador seguro.
- `PermiGestNotification` definira `tries` y `backoff()` para todas las notificaciones del sistema.
- El comando de recordatorios eliminara solo su auditoria del dia y solicitud afectados cuando capture un fallo posterior al commit.

## Criterios de aceptacion

- AC-A201: crear una solicitud conserva registro, auditoria y redireccion exitosa ante fallo de cola.
- AC-A202: resolver, restaurar y restablecer contrasena conservan su resultado exitoso ante fallo de cola.
- AC-A203: dos destinatarios se intentan de manera independiente.
- AC-A204: el job generado refleja tres intentos y el backoff definido.
- AC-A205: un recordatorio cuyo encolado falla queda sin auditoria diaria y puede intentarse nuevamente.
- AC-A206: la suite existente permanece aprobada.

## Tareas

- [x] TASK-A201: crear el despachador seguro e integrarlo en controladores. Requisitos: REQ-A201 a REQ-A203, SEC-A201, SEC-A202.
- [x] TASK-A202: definir reintentos y backoff comunes. Requisitos: REQ-A204, REQ-A205.
- [x] TASK-A203: hacer recuperable el fallo de encolado de recordatorios. Requisito: REQ-A206.
- [x] TASK-A204: agregar pruebas de fallos forzados y regresion. Criterios: AC-A201 a AC-A205.
- [x] TASK-A205: ejecutar suite, Pint y revision final. Criterio: AC-A206.

## Riesgos y dependencias

- Sin un outbox no existe garantia absoluta entre la confirmacion del negocio y la creacion del job; esta fase evita respuestas falsas y permite diagnostico, pero un fallo persistente puede dejar un aviso sin entregar.
- Los jobs agotados permaneceran en `failed_jobs` y requeriran revision operativa.
