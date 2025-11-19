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

// Buscar libros con AJAX
function buscarLibros(termino) {
    const tabla = document.getElementById('tabla-libros');
    if (!tabla) return;
    
    // Obtener otros filtros si existen
    const categoria = document.getElementById('filtro-categoria')?.value || '';
    const estado = document.getElementById('filtro-disponibilidad')?.value || '';
    
    // Construir URL con parámetros
    let url = '/biblioteca/crud_bibliotecario/api/libros.php?';
    const params = new URLSearchParams();
    
    if (termino) params.append('busqueda', termino);
    if (categoria) params.append('categoria', categoria);
    if (estado) params.append('estado', estado);
    
    url += params.toString();
    
    // Realizar petición AJAX
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarTablaLibros(data.data);
            } else {
                console.error('Error al buscar libros:', data.message);
            }
        })
        .catch(error => {
            console.error('Error en la petición:', error);
        });
}

// Buscar usuarios con AJAX
function buscarUsuarios(termino) {
    const tabla = document.querySelector('#tabla-usuarios tbody') || document.getElementById('tabla-usuarios');
    if (!tabla) return;
    
    // Obtener otros filtros si existen
    const estado = document.getElementById('filtro-estado')?.value || '';
    
    // Construir URL con parámetros
    let url = '/biblioteca/crud_bibliotecario/api/usuarios.php?';
    const params = new URLSearchParams();
    
    if (termino) params.append('busqueda', termino);
    if (estado) params.append('estado', estado);
    
    url += params.toString();
    
    // Realizar petición AJAX
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarTablaUsuarios(data.data);
            } else {
                console.error('Error al buscar usuarios:', data.message);
            }
        })
        .catch(error => {
            console.error('Error en la petición:', error);
        });
}

// Buscar préstamos con AJAX
function buscarPrestamos(termino) {
    const tabla = document.querySelector('#tabla-prestamos tbody') || document.querySelector('.data-table tbody');
    if (!tabla) return;
    
    // Obtener otros filtros si existen
    const estado = document.getElementById('filtro-estado-prestamo')?.value || '';
    
    // Construir URL con parámetros
    let url = '/biblioteca/crud_bibliotecario/api/prestamos.php?';
    const params = new URLSearchParams();
    
    if (estado) params.append('estado', estado);
    
    url += params.toString();
    
    // Realizar petición AJAX
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Filtrar por término de búsqueda en el cliente si es necesario
                let prestamos = data.data;
                if (termino) {
                    const terminoLower = termino.toLowerCase();
                    prestamos = prestamos.filter(p => 
                        p.usuario_nombre.toLowerCase().includes(terminoLower) ||
                        p.libro_titulo.toLowerCase().includes(terminoLower) ||
                        p.libro_autor.toLowerCase().includes(terminoLower)
                    );
                }
                actualizarTablaPrestamos(prestamos);
            } else {
                console.error('Error al buscar préstamos:', data.message);
            }
        })
        .catch(error => {
            console.error('Error en la petición:', error);
        });
}

// Configurar filtros con actualización automática
function setupFiltros() {
    // Filtro de categoría - Auto submit en libros
    const filtroCategoria = document.getElementById('filtro-categoria');
    if (filtroCategoria) {
        filtroCategoria.addEventListener('change', function(e) {
            // Submit del formulario para que aplique los filtros
            const form = e.target.closest('form');
            if (form) {
                form.submit();
            }
        });
    }
    
    // Filtro de disponibilidad - Auto submit en libros
    const filtroDisponibilidad = document.getElementById('filtro-disponibilidad');
    if (filtroDisponibilidad) {
        filtroDisponibilidad.addEventListener('change', function(e) {
            // Submit del formulario para que aplique los filtros
            const form = e.target.closest('form');
            if (form) {
                form.submit();
            }
        });
    }
    
    // Filtro de tipo de usuario
    const filtroTipo = document.getElementById('filtro-tipo');
    if (filtroTipo) {
        filtroTipo.addEventListener('change', function(e) {
            const form = e.target.closest('form');
            if (form) {
                form.submit();
            }
        });
    }
    
    // Filtro de estado
    const filtroEstado = document.getElementById('filtro-estado');
    if (filtroEstado) {
        filtroEstado.addEventListener('change', function(e) {
            const form = e.target.closest('form');
            if (form) {
                form.submit();
            }
        });
    }
    
    // Filtro de estado de préstamo
    const filtroEstadoPrestamo = document.getElementById('filtro-estado-prestamo');
    if (filtroEstadoPrestamo) {
        filtroEstadoPrestamo.addEventListener('change', function(e) {
            const form = e.target.closest('form');
            if (form) {
                form.submit();
            }
        });
    }
}

// Aplicar todos los filtros
function aplicarFiltros() {
    // Determinar qué página estamos
    const buscarLibro = document.getElementById('buscar-libro');
    const buscarUsuario = document.getElementById('buscar-usuario');
    const buscarPrestamo = document.getElementById('buscar-prestamo');
    
    if (buscarLibro) {
        buscarLibros(buscarLibro.value);
    } else if (buscarUsuario) {
        buscarUsuarios(buscarUsuario.value);
    } else if (buscarPrestamo) {
        buscarPrestamos(buscarPrestamo.value);
    }
}

// Actualizar tabla de libros
function actualizarTablaLibros(data) {
    const tbody = document.getElementById('tabla-libros');
    if (!tbody) return;
    
    // Limpiar tabla
    tbody.innerHTML = '';
    
    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <p>No se encontraron libros</p>
                </td>
            </tr>
        `;
        return;
    }
    
    // Agregar filas
    data.forEach(libro => {
        const tr = document.createElement('tr');
        const estadoBadge = libro.estado === 'disponible' 
            ? '<span class="badge badge-success"><i class="fas fa-check"></i> Disponible</span>'
            : '<span class="badge badge-warning"><i class="fas fa-hand-holding"></i> Prestado</span>';
        
        const categoriaBadge = libro.categoria 
            ? `<span class="badge badge-info">${libro.categoria.charAt(0).toUpperCase() + libro.categoria.slice(1)}</span>`
            : '<span class="badge badge-secondary">Sin categoría</span>';
        
        tr.innerHTML = `
            <td>${libro.isbn || 'N/A'}</td>
            <td>
                <strong>${libro.titulo}</strong>
                ${libro.anio ? `<br><small class="text-muted">(${libro.anio})</small>` : ''}
            </td>
            <td>${libro.autor}</td>
            <td>${categoriaBadge}</td>
            <td>${estadoBadge}</td>
            <td>${libro.editorial || 'N/A'}</td>
            <td class="table-actions">
                <a href="editar.php?id=${libro.id}" class="btn-icon btn-icon-primary" title="Editar">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="eliminar.php?id=${libro.id}" class="btn-icon btn-icon-danger" title="Eliminar" 
                   onclick="return confirm('¿Está seguro de eliminar este libro?\\n\\nTítulo: ${libro.titulo.replace(/'/g, "\\'")}')">
                    <i class="fas fa-trash"></i>
                </a>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Actualizar tabla de usuarios
function actualizarTablaUsuarios(data) {
    const tbody = document.querySelector('#tabla-usuarios tbody') || document.getElementById('tabla-usuarios');
    if (!tbody) return;
    
    // Limpiar tabla
    tbody.innerHTML = '';
    
    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>No se encontraron usuarios</p>
                </td>
            </tr>
        `;
        return;
    }
    
    // Agregar filas
    data.forEach(usuario => {
        const tr = document.createElement('tr');
        const estadoBadge = usuario.estado === 'activo'
            ? '<span class="badge badge-success">Activo</span>'
            : '<span class="badge badge-secondary">Inactivo</span>';
        
        tr.innerHTML = `
            <td>${usuario.dni}</td>
            <td>
                <div class="user-cell">
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <strong>${usuario.nombre_completo}</strong>
                    </div>
                </div>
            </td>
            <td>${usuario.email}</td>
            <td>${usuario.telefono || 'N/A'}</td>
            <td>${usuario.tipo_usuario ? usuario.tipo_usuario.charAt(0).toUpperCase() + usuario.tipo_usuario.slice(1) : 'N/A'}</td>
            <td>${estadoBadge}</td>
            <td class="table-actions">
                <a href="detalle.php?id=${usuario.id}" class="btn-icon btn-icon-info" title="Ver detalles">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="editar.php?id=${usuario.id}" class="btn-icon btn-icon-primary" title="Editar">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="eliminar.php?id=${usuario.id}" class="btn-icon btn-icon-danger" title="Eliminar"
                   onclick="return confirm('¿Está seguro de eliminar este usuario?\\n\\nNombre: ${usuario.nombre_completo.replace(/'/g, "\\'")}')">
                    <i class="fas fa-trash"></i>
                </a>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Actualizar tabla de préstamos
function actualizarTablaPrestamos(data) {
    const tbody = document.querySelector('.data-table tbody');
    if (!tbody) return;
    
    // Limpiar tabla
    tbody.innerHTML = '';
    
    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No se encontraron préstamos</p>
                </td>
            </tr>
        `;
        return;
    }
    
    // Agregar filas
    data.forEach(prestamo => {
        const tr = document.createElement('tr');
        const esVencido = prestamo.vencido || (prestamo.estado === 'activo' && prestamo.dias_retraso > 0);
        
        if (esVencido) {
            tr.classList.add('row-warning');
        }
        
        let estadoBadge;
        if (prestamo.estado === 'devuelto') {
            estadoBadge = '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Devuelto</span>';
        } else if (esVencido) {
            estadoBadge = '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Vencido</span>';
        } else {
            estadoBadge = '<span class="badge badge-primary"><i class="fas fa-clock"></i> Activo</span>';
        }
        
        const fechaPrestamo = new Date(prestamo.fecha_prestamo).toLocaleDateString('es-ES');
        const fechaDevolucion = new Date(prestamo.fecha_devolucion).toLocaleDateString('es-ES');
        
        let retrasoHTML = '';
        if (esVencido && prestamo.dias_retraso > 0) {
            retrasoHTML = `<br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> ${Math.abs(prestamo.dias_retraso)} días de retraso</small>`;
        }
        
        tr.innerHTML = `
            <td>#${String(prestamo.id).padStart(3, '0')}</td>
            <td>
                <div class="user-cell">
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <strong>${prestamo.usuario_nombre}</strong>
                        <br><small class="text-muted">${prestamo.usuario_dni}</small>
                    </div>
                </div>
            </td>
            <td>
                <div class="book-cell">
                    <i class="fas fa-book"></i>
                    <div>
                        <strong>${prestamo.libro_titulo}</strong>
                        <br><small class="text-muted">${prestamo.libro_autor}</small>
                    </div>
                </div>
            </td>
            <td>${fechaPrestamo}</td>
            <td>${fechaDevolucion}${retrasoHTML}</td>
            <td>${estadoBadge}</td>
            <td class="table-actions">
                ${prestamo.estado === 'activo' ? `
                    <a href="devolver.php?id=${prestamo.id}" class="btn-icon btn-icon-success" title="Marcar como devuelto">
                        <i class="fas fa-check"></i>
                    </a>
                ` : ''}
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