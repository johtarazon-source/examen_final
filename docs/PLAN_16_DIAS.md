# Plan de 16 días — Restaurante "El Cuervo"

Reglas de trabajo: al menos **un commit por día en cada repositorio** (backend `examen_final` y `frontend-restaurante`),
con el formato `Usuario: [mensaje]` (p. ej. `Johan: [Configuración inicial del proyecto]`). Sin `vendor/`, `composer.lock` ni `.env` en los repos.
El SQL entregado con el ejercicio **no se modifica**.

| Día | Entrega | Estado |
|---|---|---|
| 1 | Estructura de los 4 microservicios, `composer.json`, Config/Support/Middleware comunes (CORS, errores), scripts SQL literales, `/salud`, `iniciar_servicios.ps1`, plan y README; esqueleto del frontend | Hecho |
| 2 | **ms-auth**: modelo `Usuario`, login por usuario o correo, logout, `GET /auth/validar`; pruebas con curl | Pendiente |
| 3 | Middleware `Auth` (conexión de solo lectura a `elcuervo_auth`) en reservas, productos y pedidos; pruebas de 401 | Pendiente |
| 4 | **ms-reservas**: mesas (crear, editar, listar, cambiar estado; sin duplicados; capacidad > 0) | Pendiente |
| 5 | **ms-reservas**: reservas (crear, consultar, editar) con validaciones de fecha, mesa fuera de servicio y capacidad | Pendiente |
| 6 | **ms-reservas**: doble reserva, filtros (fecha, cliente, estado), cancelar y cambiar estado; pruebas | Pendiente |
| 7 | **ms-productos** completo: categorías, CRUD de productos, filtros, validaciones y duplicados | Pendiente |
| 8 | **ms-pedidos**: crear pedido, detalles y cálculos; validación de mesa y producto | Pendiente |
| 9 | **ms-pedidos**: agregar, modificar y eliminar ítems; cambio de estado; filtros; pruebas | Pendiente |
| 10 | Documentación de endpoints (`docs/ENDPOINTS.md`, `endpoints.http`) y pruebas integrales del backend con BD temporal | Pendiente |
| 11 | **Frontend**: base (CSS de El Cuervo, `ApiClient`, sesión, login/logout, guardia de ruta, navegación, logo) | Pendiente |
| 12 | **Frontend**: módulo de mesas | Pendiente |
| 13 | **Frontend**: módulo de reservas (formulario dinámico, filtros, cancelar) | Pendiente |
| 14 | **Frontend**: módulos de productos y pedidos (selección, cantidades, totales, estados) | Pendiente |
| 15 | **Frontend**: panel de inicio, validaciones de cliente, mensajes, responsive y accesibilidad | Pendiente |
| 16 | Pruebas de extremo a extremo, correcciones, README final y lista de entrega | Pendiente |

## Lista de entrega (Día 16)
- [ ] Repo del frontend y repo del backend, sin `vendor/`, `composer.lock` ni `.env`.
- [ ] Documentación de endpoints actualizada.
- [ ] Un commit por día en cada repo, con el formato `Usuario: [mensaje]`.
- [ ] Los 4 microservicios arrancan desde cero con `iniciar_servicios.ps1` y el frontend funciona completo.
- [ ] Sin frameworks de frontend ni jQuery; backend solo con Slim + Eloquent.
