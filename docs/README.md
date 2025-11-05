# Instrucciones de instalación

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
