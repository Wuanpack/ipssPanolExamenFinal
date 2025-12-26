# Proyecto Pañol - Gestión de Inventario de Herramientas
## Evaluación FINAL Desarrollo BackEnd
## Docente: Sebastián Cabezas Ríos
## Estudiante: Alonso Garcia Espinoza

## Descripción General

Este proyecto es un sistema de gestión de inventario de herramientas para un Pañol, diseñado para facilitar:

- La creación y modificación de herramientas.
- La activación o desactivación de herramientas.
- La visualización del inventario con paginación.
- La comunicación entre frontend y backend mediante una API RESTful.

El proyecto está dividido en dos partes principales:

1. **Frontend**: Interfaz de usuario desarrollada en JavaScript con Bootstrap, que permite interactuar con el inventario de manera intuitiva.
2. **API / Backend (API_PANOL)**: API RESTful desarrollada en PHP, que gestiona la lógica del inventario, conexión a base de datos y autenticación mediante token.

---

## Estructura del Proyecto

```
/PROYECTO_PANOL
├─ /frontend # Interfaz de usuario y archivos JS/CSS
│ └─ README.md # Detalles de instalación, uso y funcionalidades del frontend
├─ /API_PANOL # API RESTful para el inventario
│ └─ README.md # Detalles de instalación, endpoints y uso del backend
└─ README.md # Este archivo
```

---

## Funcionalidad General

- Permite administrar herramientas y su estado dentro de un inventario.
- Integración completa entre frontend y API mediante llamadas HTTP (`fetch`) con token de autorización.
- Notificaciones dinámicas (toasts) que informan al usuario sobre las operaciones realizadas.
- Soporte para paginación en la visualización del inventario.
- Validaciones básicas de campos y control de cantidad disponible versus cantidad total.

---

## Documentación Detallada

Para información más específica, por favor revisa los README.md dentro de cada carpeta:

- `/frontend/README.md` → Detalles sobre el frontend, modales, validaciones y lógica de interacción.
- `/API_PANOL/README.md` → Detalles sobre la API, endpoints, instalación, configuración y esquema de base de datos.

---

## Contacto

- **Autor**: Alonso García Espinoza  
- **Correo**: alonso.garciae@mayor.cl
