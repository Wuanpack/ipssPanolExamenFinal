## Evaluación FINAL Desarrollo FrontEnd
## Docente: Sebastián Cabezas Ríos
## Estudiante: Alonso Garcia Espinoza

# Proyecto Pañol - Inventario de Herramientas

## Descripción

Este proyecto es un sistema de gestión de inventario de herramientas para un Pañol, que permite:

- Crear nuevas herramientas.
- Modificar herramientas existentes.
- Activar o desactivar herramientas.
- Visualizar el inventario con paginación.
- Recibir notificaciones (toasts) de forma dinámica sobre el estado de las operaciones.

El frontend está desarrollado en **JavaScript puro** con Bootstrap para modales y estilos, y se comunica con una **API RESTful**.

No se han usado todos los endpoints de momento, pero se permiten usar los métodos GET, POST, PUT, PATCH y DELETE.

**Autor:** Alonso García Espinoza 

---

## Tabla de Contenidos

- [Estructura](#estructura-del-frontend)
- [Funcionalidades](#funcionalidades-clave-del-frontend)
- [Conexión con la API](#conexion-con-la-api)
- [Requisitos](#requisitos)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Autenticación](#autenticación)
- [Endpoints principales](#endpoints-principales)
- [Contacto](#contacto)

---

## Estructura del Frontend

- `inventario.js`: Archivo principal que gestiona la interfaz de usuario del inventario.
  - Maneja la carga de inventario, modales de creación y modificación, validaciones y paginación.
  - Implementa toasts para notificaciones temporales con animación.
- Elementos HTML importantes:
  - `tabla-inventario-body`: `<tbody>` donde se renderiza el inventario.
  - `inventario-paginator`: Contenedor de la paginación.
  - `modalModificar`: Modal para modificar herramientas.
  - `modalCrear`: Modal para crear herramientas.
  - Botones: `btn-crear-herramienta`, `btn-guardar-modificacion`, `btn-guardar-crear`.

---

### Funcionalidades Clave del Frontend

1. **Cargar inventario**
   - Llama a la API con paginación (`page` y `limit`) usando `fetch`.
   - Renderiza filas en la tabla con botones de modificar y activar/desactivar.

2. **Modificar herramienta**
   - Abre modal con los datos actuales.
   - Valida campos numéricos y la relación `cantidad_disponible <= cantidad`.
   - Envía PUT a `/modificar.php?id=ID` con JSON.
   - Maneja la respuesta:
     - `status 200` + `no_changes = true` → muestra toast "No se han realizado cambios".
     - `status 200` → muestra toast de éxito.
     - `status 409` → muestra conflicto (parte duplicada).
     - `status 400/422/500` → muestra mensaje de error.

3. **Crear herramienta**
   - Abre modal de creación.
   - Valida campos obligatorios y numéricos.
   - Envía POST a `/anadirHerramienta.php` con JSON.
   - Maneja respuesta similar a la modificación.

4. **Activar / Desactivar**
   - Botones en cada fila.
   - Envía PATCH (`activar.php`) o DELETE (`desactivar.php`) según estado.
   - Actualiza inventario tras operación exitosa.

5. **Toasts**
   - Muestra máximo 5 notificaciones simultáneas.
   - Animación: slide desde derecha y fade.
   - Se eliminan automáticamente tras 3 segundos o manualmente con botón.

---

## Conexión con la API

- Base URL: `/PROYECTO_PANOL/API_PANOL/v1/endpoints/herramientas`
- Token de autorización: `ipss.2025.T3` (Bearer token)
- Endpoints utilizados:

| Método | Endpoint                       | Función                                   |
|--------|--------------------------------|------------------------------------------|
| GET    | `/herramientas.php`             | Cargar inventario con paginación         |
| POST   | `/anadirHerramienta.php`        | Crear nueva herramienta                   |
| PUT    | `/modificar.php?id=ID`          | Modificar herramienta existente          |
| PATCH  | `/activar.php?id=ID`            | Activar herramienta                       |
| DELETE | `/desactivar.php?id=ID`         | Desactivar herramienta                     |

- Respuestas esperadas (JSON):
  ```json
  {
    "status": 200,
    "message": "OK",
    "data": {
        "message": "Herramienta actualizada correctamente",
        "no_changes": false
    }
  }

  ```

## Requisitos
- PHP >= 7.4
- Servidor web (Apache, Nginx, etc.)
- Base de datos MySQL / MariaDB
- Extensiones PHP: PDO, JSON

---

## Estructura del proyecto

Estructura del proyecto: Frontend Pañol de Herramientas Motores J85-GE-21 B/C

```

/frontend
├─ /css
│   ├─ dashboard.css
│   ├─ login.css
│   └─ main.css
├─ /js
│   ├─ dashboard.js
│   ├─ home.js
│   ├─ inventario.js
│   └─ login.js
├─ /images
│   ├─ f5-login-1.jpg
│   ├─ f5-login-2.jpg
│   └─ general-electric.webp
├─ /views
│   ├─ dashboard.html
│   ├─ login.html
│   ├─ /components
│   │   ├─ navbar.html
│   │   ├─ sidebar.html
│   │   ├─ offcanvas.html
│   │   └─ cards.html
│   │
│   └─ /pages
│       ├─ estado.html
│       ├─ home.html
│       ├─ inventario.html
│       ├─ solicitar.html
│       └─ usuario.html
└─ README.md

```
---

## Instalación
1. Clonar el repositorio:
   ```bash
   git clone https://github.com/Wuanpack/ipssPanolExamenFinal
   ```
2. Configurar base de datos en database.php
3. Importar el esquema SQL del pañol
4. Configurar tu servidor web apuntando al directorio PROYECTO_PANOL/API_PANOL/v1/endpoints

---

## Autenticación

Todas las peticiones requieren un header 'Authorization' con un token Bearer válido:

```
Authorization: Bearer ipss.2025.T3
```
---

## Endpoints principales

-   Todos los endpoints y detalles completos se encuentran en swagger.json
-   Para ver la UI de Swagger, apuntar a: /PROYECTO_PANOL/API_PANOL/v1/swagger/#/

## Contacto

-   Correo: alonso.garciae@mayor.cl

