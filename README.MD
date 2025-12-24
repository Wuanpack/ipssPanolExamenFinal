# APIPANOL - API REST para Gestión de Pañol Industrial

Sistema de gestión de herramientas para pañoles industriales desarrollado en PHP con arquitectura REST. Permite controlar inventario, solicitudes de préstamo, aprobaciones, devoluciones y seguimiento completo de movimientos.

---

## Características Principales

- Gestión completa de inventario de herramientas
- Sistema de solicitudes y aprobaciones de préstamos
- Control automático de stock con transacciones ACID
- Validación de RUT chileno con algoritmo módulo 11
- Dashboard con métricas en tiempo real
- Autenticación mediante Bearer Token
- Paginación de resultados
- Triggers de base de datos para integridad
- Protección contra condiciones de carrera
- API RESTful con respuestas JSON estandarizadas

---

## Requisitos del Sistema

- PHP >= 7.4
- MySQL >= 5.7 o MariaDB >= 10.3
- Apache/Nginx con mod_rewrite habilitado
- Extensiones PHP requeridas:
  - mysqli
  - json
  - mbstring

---

## Instalación

### 1. Clonar o Descargar el Proyecto

```bash
git clone https://github.com/tu-usuario/apipanol.git
cd apipanol

