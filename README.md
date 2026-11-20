# El Cuervo — Backend (microservicios)

Sistema web de **reservas de mesas y pedidos** del restaurante **El Cuervo**, construido como microservicios
independientes con **PHP 8, Slim 4 y Eloquent**. Este repositorio contiene **solo el backend**; el frontend está en el
repositorio `frontend-restaurante`.

## Microservicios

| Servicio | Puerto | Base de datos | Responsabilidad |
|---|---|---|---|
| `ms-auth` | 8101 | `elcuervo_auth` | Inicio y cierre de sesión, validación de token |
| `ms-reservas` | 8102 | `elcuervo_reservas` | Mesas, reservas y disponibilidad |
| `ms-productos` | 8103 | `elcuervo_productos` | Categorías y productos del menú |
| `ms-pedidos` | 8104 | `elcuervo_pedidos` | Pedidos y sus detalles |

Cada servicio tiene su propia base de datos y conexión, expone una API REST en JSON y **no llama por HTTP a otros servicios**.
Las bases se crean solas a partir de `ms-*/database/*.sql` (scripts entregados con el ejercicio, sin modificar).

Respuesta JSON común: `{ "ok": true|false, "mensaje": "...", "data": ... }`.

## Estructura de cada servicio

```
ms-xxx/
├── app/{Controllers, Models, Middleware, Config, Routes, Support, Exceptions}
├── database/ms_xxx.sql      # script SQL del ejercicio (no modificar)
├── public/index.php
├── scripts/preparar_bd.php  # crea la BD y aplica el SQL si faltan las tablas
├── composer.json
└── .env.example
```

## Puesta en marcha

Requisitos: PHP 8.1+, Composer y MySQL (XAMPP).

```powershell
# 1. Dependencias (una vez por servicio)
cd ms-auth; composer install; cd ..
cd ms-reservas; composer install; cd ..
cd ms-productos; composer install; cd ..
cd ms-pedidos; composer install; cd ..

# 2. Prepara las BD y levanta los 4 servicios (copia .env.example a .env si falta)
.\iniciar_servicios.ps1
```

Comprobación: `http://127.0.0.1:8101/salud` (y 8102, 8103, 8104) responde `{"ok":true,...}`.

## Documentación

- [`docs/PLAN_16_DIAS.md`](docs/PLAN_16_DIAS.md): plan de trabajo diario.
- `docs/ENDPOINTS.md`: documentación de endpoints (se completa durante el desarrollo).

## Autenticación

`ms-auth` genera un token simple y lo guarda en `usuarios.token` junto con `sesion_activa`. Los demás servicios validan
ese token leyendo la tabla `usuarios` de `elcuervo_auth` con una conexión de **solo lectura** (sin llamadas HTTP entre servicios).
