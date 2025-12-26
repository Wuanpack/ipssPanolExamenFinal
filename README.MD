## Evaluación FINAL Desarrollo BackEnd
## Docente: Sebastián Cabezas Ríos
## Estudiante: Alonso Garcia Espinoza

# API Pañol de Herramientas J85-GE-21 B/C

API para la gestión de un pañol de herramientas para motores **J85-GE-21 B/C**. Permite controlar inventario de herramientas, registrar movimientos (préstamos y devoluciones) y obtener estadísticas generales.

**Autor:** Alonso García Espinoza  
**Versión:** LOCAL  

---

## Tabla de Contenidos
- [Estructura](#estructura-del-proyecto)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Autenticación](#autenticación)
- [Endpoints principales](#endpoints-principales)
- [Ejemplos de uso](#ejemplos-de-uso)
- [Notas](#notas)
- [Contacto](#contacto)

---

## Requisitos
- PHP >= 7.4
- Servidor web (Apache, Nginx, etc.)
- Base de datos MySQL / MariaDB
- Extensiones PHP: PDO, JSON

---

## Estructura del proyecto

/API_PANOL
│
├─ /common
│   ├─ database.php                      # Conexión a base de datos
│   ├─ validators.php                    # Funciones de validación
│   └─ response.php                      # Funciones para enviar JSON
│
├─ /v1
│   ├─/swagger
│   │   └─ swagger.json                  # Archivo json para visualizacion de Swagger
│   │   └─ swagger-initializer.json      # Modificar desde donde se ve Swagger
│   │   └─ {...}                         # Resto de archivos Swagger
│   │
│   └─ /endpoints
│       ├─ /dashboard
│       │   ├─ dashboard.model.php       # Realizar querys a la base de datos
│       │   └─ dashboard.php             # GET de las cards e historial de movimientos
|       |
│       ├─ /herramientas
│       │   ├─ herramientas.model.php    # Realizar querys a la base de datos
│       │   ├─ herramientas.php          # GET inventario 
│       │   ├─ anadirHerramienta.php     # POST crear herramientas
│       │   ├─ modificar.php             # PUT modificar herramientas
│       │   ├─ activar.php               # PATCH activar herramientas
│       │   └─ desactivar.php            # DELETE desactivar herramientas (lógico)
│       │
│       └─ /movimientos
│           ├─ movimientos.model.php     # Realizar querys a la base de datos
│           ├─ movimientos.php           # POST realizar una solicitud de prestamo
│           ├─ aceptar.php               # POST aceptar una solicitud de prestamo
│           ├─ rechazar.php              # POST rechazar una solicitud de prestamo
│           └─ devolver.php              # POST devolver una herramienta prestada
│
└─ config.php


## Instalación
1. Clonar el repositorio:
   ```bash
   git clone https://github.com/Wuanpack/ipssPanolAPI


2. Configurar base de datos en database.php
3. Importar el esquema SQL del pañol
4. Configurar tu servidor web apuntando al directorio /API_PANOL/v1/endpoints

## Autenticación

Todas las peticiones requieren un header 'Authorization' con un token Bearer válido:
Authorization: Bearer ipss.2025.T3

## Endpoints principales

El detalle de los endpoints se encuentra en swagger.json, para visualizar la ui, apuntar a /API_PANOL/v1/swagger/#/

## Contacto

*   Correo: alonso.garciae@mayor.cl

