# Fase 1: base de notificaciones

## Estado

Implementado y validado.

## Objetivo

Preparar la infraestructura comun para que las siguientes fases puedan enviar notificaciones por correo al campo `correo_institucional`, mediante la cola existente y solo despues de confirmar las transacciones de base de datos.

## Alcance

### Incluye

- Enrutamiento de notificaciones de correo desde `User` hacia `correo_institucional`.
- Clase base abstracta para notificaciones encoladas con despacho posterior al commit.
- Pruebas de enrutamiento, contrato de cola y comportamiento `afterCommit`.

### Excluye

- Envio desde solicitudes, resoluciones, restauraciones o restablecimientos de contrasena.
- Contenido y plantillas especificas de cada evento.
- Recordatorios de solicitudes pendientes.
- Configuracion SMTP o puesta en marcha de produccion.
- Cambios en rutas, controladores, vistas, permisos, migraciones o base de datos.

## Requisitos

- REQ-101: una notificacion enviada a un usuario debe usar su `correo_institucional`.
- REQ-102: las notificaciones funcionales de PermiGest deben compartir un contrato comun encolado.
- REQ-103: el trabajo en cola debe despacharse despues de confirmar la transaccion activa.
- SEC-101: no se deben registrar ni exponer credenciales o datos sensibles en esta infraestructura.
- SEC-102: esta fase no debe iniciar envios reales ni alterar el flujo funcional existente.

## Diseno tecnico

- `User::routeNotificationForMail()` devolvera el correo institucional.
- `PermiGestNotification` extendera `Notification`, implementara `ShouldQueueAfterCommit` y usara `Queueable`.
- Las clases concretas y sus mensajes se agregaran en las fases correspondientes a cada evento.

## Criterios de aceptacion

- AC-101: Laravel resuelve el canal `mail` de un usuario a `correo_institucional`.
- AC-102: una notificacion concreta derivada de la base implementa `ShouldQueue`.
- AC-103: la notificacion implementa el contrato `ShouldQueueAfterCommit`.
- AC-104: el trabajo generado por Laravel queda marcado para despacho posterior al commit.
- AC-105: la suite existente permanece aprobada.

## Tareas

- [x] TASK-101: implementar el enrutamiento institucional. Requisito: REQ-101.
- [x] TASK-102: crear la base encolada posterior al commit. Requisitos: REQ-102, REQ-103, SEC-101, SEC-102.
- [x] TASK-103: agregar pruebas de infraestructura. Criterios: AC-101 a AC-104.
- [x] TASK-104: ejecutar pruebas, Pint y revision de diff. Criterio: AC-105.

## Riesgos y dependencias

- La entrega real seguira dependiendo del mailer y del trabajador de cola configurados en cada entorno.
- Esta fase no genera correos por si sola; deja preparada la infraestructura para las fases siguientes.
