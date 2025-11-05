// JavaScript principal del sistema de biblioteca

document.addEventListener('DOMContentLoaded', function() {
    console.log('Sistema de Biblioteca cargado correctamente');
    
    // Inicializar tooltips si existen
    initTooltips();
    
    // Cargar estadísticas si estamos en el dashboard
    if (document.querySelector('.dashboard-container')) {
        loadDashboardStats();
    }
});

// Función para inicializar tooltips
function initTooltips() {
    const elements = document.querySelectorAll('[title]');
    elements.forEach(el => {
        el.addEventListener('mouseenter', function() {
            // Placeholder para tooltips personalizados
        });
    });
}

// Cargar estadísticas del dashboard (simulado sin backend)
function loadDashboardStats() {
    // Simular datos de ejemplo
    const stats = {
        totalLibros: 150,
        totalUsuarios: 45,
        prestamosActivos: 23,
        prestamosVencidos: 5
    };
    
    // Animar los contadores
    animateCounter('total-libros', stats.totalLibros);
    animateCounter('total-usuarios', stats.totalUsuarios);
    animateCounter('prestamos-activos', stats.prestamosActivos);
    animateCounter('prestamos-vencidos', stats.prestamosVencidos);
}

// Animar contador numérico
function animateCounter(elementId, target) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    let current = 0;
    const increment = target / 50;
    const duration = 1000;
    const stepTime = duration / 50;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, stepTime);
}

// Función para mostrar alertas
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `
        <i class="fas fa-${getAlertIcon(type)}"></i>
        <span>${message}</span>
    `;
    
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.insertBefore(alertDiv, mainContent.firstChild);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

// Obtener icono según tipo de alerta
function getAlertIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Confirmar acción con mensaje personalizado
function confirmAction(message) {
    return confirm(message);
}

// Formatear fecha a formato local
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}