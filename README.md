# Sistema de Gestión de Biblioteca

Sistema CRUD para la administración integral de una biblioteca, desarrollado en PHP con MySQL.

## Descripción

Sistema web diseñado para facilitar la gestión completa de una biblioteca, permitiendo el control de libros, usuarios y préstamos de manera eficiente y centralizada.

## Alcances del Sistema

### Gestión de Libros
- Registro de nuevos ejemplares con información detallada (ISBN, título, autor, categoría, editorial, año de publicación)
- Búsqueda y filtrado avanzado por múltiples criterios
- Edición de información bibliográfica
- Control de inventario y disponibilidad en tiempo real
- Eliminación de registros obsoletos
- Visualización del estado de cada ejemplar

### Gestión de Usuarios
- Registro de usuarios con datos completos (DNI/NIE, nombre, correo, teléfono, dirección)
- Clasificación por tipo de usuario (estudiante, profesor, externo)
- Consulta del historial de préstamos por usuario
- Actualización de información personal
- Control de estado de cuenta (activo/inactivo)
- Baja de usuarios del sistema

### Gestión de Préstamos
- Registro de nuevos préstamos con validación de disponibilidad
- Control de fechas de préstamo y devolución
- Identificación automática de préstamos vencidos
- Proceso de devolución de ejemplares
- Historial completo de transacciones
- Generación de reportes de actividad
- Sistema de alertas para préstamos próximos a vencer

### Características Técnicas
- Interfaz web responsiva adaptable a dispositivos móviles
- Sistema de búsqueda en tiempo real
- Validación de formularios del lado del cliente y servidor
- API REST para operaciones CRUD
- Base de datos relacional MySQL
- Arquitectura MVC
- Diseño modular y escalable

### Panel de Control
- Estadísticas generales del sistema
- Visualización de métricas clave (total de libros, usuarios activos, préstamos en curso)
- Registro de actividad reciente
- Accesos rápidos a funciones principales
- Indicadores de préstamos vencidos

## Tecnologías Utilizadas

- PHP 7.4+
- MySQL 5.7+
- HTML5
- CSS3 (Variables CSS, Flexbox, Grid)
- JavaScript (ES6+)
- Font Awesome 6.4.0

## Estructura del Proyecto
# Estructura del código
biblioteca/
│
├── index.php                    # Redirige al login o dashboard
├── login.php                    # Página de inicio de sesión
├── logout.php                   # Cierre de sesión
├── dashboard.php                # Página principal (post-login)
│
├── config/
│   ├── database.php             # Conexión a BD
│   └── config.php               # Configuraciones generales
│
├── includes/
│   ├── header.php               # Header común (nav, meta)
│   ├── footer.php               # Footer común
│   └── auth.php                 # Validación de sesión
│
├── libros/
│   ├── index.php                # Listar libros
│   ├── crear.php                # Formulario nuevo libro
│   ├── editar.php               # Formulario editar libro
│   ├── eliminar.php             # Eliminar libro (confirmación)
│   └── buscar.php               # Búsqueda AJAX (opcional)
│
├── usuarios/
│   ├── index.php                # Listar usuarios
│   ├── crear.php                # Formulario nuevo usuario
│   ├── editar.php               # Formulario editar usuario
│   ├── detalle.php              # Ver perfil y préstamos del usuario
│   └── eliminar.php             # Eliminar usuario
│
├── prestamos/
│   ├── index.php                # Listar préstamos activos
│   ├── nuevo.php                # Registrar nuevo préstamo
│   ├── devolver.php             # Registrar devolución
│   ├── historial.php            # Ver historial completo
│   └── ajax_validar.php         # Validaciones AJAX
│
├── api/                         # (Opcional - para requests AJAX)
│   ├── libros.php               # Endpoints JSON de libros
│   ├── usuarios.php             # Endpoints JSON de usuarios
│   └── prestamos.php            # Endpoints JSON de préstamos
│
├── assets/
│   ├── css/
│   │   ├── style.css            # Estilos principales
│   │   └── dashboard.css        # Estilos del dashboard
│   ├── js/
│   │   ├── main.js              # Scripts generales
│   │   ├── validaciones.js      # Validaciones del cliente
│   │   └── busquedas.js         # Búsquedas con AJAX
│   └── img/
│       └── logo.png             # Logo de la biblioteca
│
├── sql/
│   ├── estructura.sql           # Estructura de tablas
│   └── datos_prueba.sql         # Datos de ejemplo
│
└── docs/
    ├── README.md                # Instrucciones de instalación
    ├── division_tareas.md       # Quién hizo qué
    └── presentacion.pdf         # Slides de la presentación


## Requisitos del Sistema

- Servidor web Apache 2.4+
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Navegador web moderno (Chrome, Firefox, Edge, Safari)

## Instalación

1. Clonar el repositorio en el directorio del servidor web
2. Importar el archivo `sql/estructura.sql` en MySQL
3. Opcionalmente, importar `sql/datos_prueba.sql` para datos de ejemplo
4. Configurar las credenciales de base de datos en `config/database.php`
5. Acceder al sistema mediante el navegador web

### Roles Disponibles:
1. **Usuario** - Solo puede solicitar préstamos y ver su historial
2. **Bibliotecario** - Puede gestionar libros, usuarios y préstamos
3. **Administrador** - Acceso total al sistema