// Búsquedas con AJAX (sin backend - simulado)

// Búsqueda de libros
document.addEventListener('DOMContentLoaded', function() {
    const buscarLibro = document.getElementById('buscar-libro');
    if (buscarLibro) {
        buscarLibro.addEventListener('input', debounce(function(e) {
            buscarLibros(e.target.value);
        }, 300));
    }
    
    // Búsqueda de usuarios
    const buscarUsuario = document.getElementById('buscar-usuario');
    if (buscarUsuario) {
        buscarUsuario.addEventListener('input', debounce(function(e) {
            buscarUsuarios(e.target.value);
        }, 300));
    }
    
    // Búsqueda de préstamos
    const buscarPrestamo = document.getElementById('buscar-prestamo');
    if (buscarPrestamo) {
        buscarPrestamo.addEventListener('input', debounce(function(e) {
            buscarPrestamos(e.target.value);
        }, 300));
    }
    
    // Filtros
    setupFiltros();
});

// Función debounce para optimizar búsquedas
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Buscar libros (simulado)
function buscarLibros(termino) {
    console.log('Buscando libros:', termino);
    // Aquí irá la llamada AJAX cuando tengas el backend
    // fetch('/api/libros.php?buscar=' + termino)
    //     .then(response => response.json())
    //     .then(data => actualizarTablaLibros(data));
}

// Buscar usuarios (simulado)
function buscarUsuarios(termino) {
    console.log('Buscando usuarios:', termino);
    // Aquí irá la llamada AJAX cuando tengas el backend
}

// Buscar préstamos (simulado)
function buscarPrestamos(termino) {
    console.log('Buscando préstamos:', termino);
    // Aquí irá la llamada AJAX cuando tengas el backend
}

// Configurar filtros
function setupFiltros() {
    // Filtro de categoría
    const filtroCategoria = document.getElementById('filtro-categoria');
    if (filtroCategoria) {
        filtroCategoria.addEventListener('change', function(e) {
            aplicarFiltros();
        });
    }
    
    // Filtro de disponibilidad
    const filtroDisponibilidad = document.getElementById('filtro-disponibilidad');
    if (filtroDisponibilidad) {
        filtroDisponibilidad.addEventListener('change', function(e) {
            aplicarFiltros();
        });
    }
    
    // Filtro de tipo de usuario
    const filtroTipo = document.getElementById('filtro-tipo');
    if (filtroTipo) {
        filtroTipo.addEventListener('change', function(e) {
            aplicarFiltros();
        });
    }
    
    // Filtro de estado
    const filtroEstado = document.getElementById('filtro-estado');
    if (filtroEstado) {
        filtroEstado.addEventListener('change', function(e) {
            aplicarFiltros();
        });
    }
    
    // Filtro de estado de préstamo
    const filtroEstadoPrestamo = document.getElementById('filtro-estado-prestamo');
    if (filtroEstadoPrestamo) {
        filtroEstadoPrestamo.addEventListener('change', function(e) {
            aplicarFiltros();
        });
    }
}

// Aplicar todos los filtros
function aplicarFiltros() {
    console.log('Aplicando filtros...');
    // Aquí irá la lógica para aplicar filtros
    // Recoger todos los valores de los filtros
    // Hacer llamada AJAX con los parámetros
}

// Actualizar tabla de libros
function actualizarTablaLibros(data) {
    const tbody = document.getElementById('tabla-libros');
    if (!tbody) return;
    
    // Limpiar tabla
    tbody.innerHTML = '';
    
    // Agregar filas
    data.forEach(libro => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${libro.isbn}</td>
            <td>${libro.titulo}</td>
            <td>${libro.autor}</td>
            <td><span class="badge badge-info">${libro.categoria}</span></td>
            <td><span class="badge badge-${libro.disponible ? 'success' : 'warning'}">${libro.disponible ? 'Disponible' : 'Prestado'}</span></td>
            <td>${libro.disponibles} de ${libro.total}</td>
            <td class="table-actions">
                <a href="editar.php?id=${libro.id}" class="btn-icon btn-icon-primary" title="Editar">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="#" class="btn-icon btn-icon-info" title="Ver detalles">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="eliminar.php?id=${libro.id}" class="btn-icon btn-icon-danger" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este libro?')">
                    <i class="fas fa-trash"></i>
                </a>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Exportar datos (simulado)
const btnExport = document.getElementById('btn-export');
if (btnExport) {
    btnExport.addEventListener('click', function() {
        console.log('Exportando datos...');
        alert('Función de exportación disponible cuando se implemente el backend');
    });
}