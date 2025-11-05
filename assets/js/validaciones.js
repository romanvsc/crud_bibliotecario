// Validaciones de formularios

// Validar email
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Validar teléfono (formato español)
function validarTelefono(telefono) {
    const regex = /^[6-9]\d{8}$/;
    return regex.test(telefono.replace(/\s/g, ''));
}

// Validar DNI/NIE español
function validarDNI(dni) {
    const regex = /^[0-9]{8}[TRWAGMYFPDXBNJZSQVHLCKE]$/i;
    if (!regex.test(dni)) return false;
    
    const numero = parseInt(dni.substr(0, 8));
    const letra = dni.substr(8, 1).toUpperCase();
    const letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
    
    return letras.charAt(numero % 23) === letra;
}

// Validar ISBN
function validarISBN(isbn) {
    // Limpiar guiones y espacios
    isbn = isbn.replace(/[-\s]/g, '');
    
    // ISBN-10
    if (isbn.length === 10) {
        let suma = 0;
        for (let i = 0; i < 9; i++) {
            suma += parseInt(isbn[i]) * (10 - i);
        }
        const digito = isbn[9].toUpperCase();
        const checksum = digito === 'X' ? 10 : parseInt(digito);
        suma += checksum;
        return suma % 11 === 0;
    }
    
    // ISBN-13
    if (isbn.length === 13) {
        let suma = 0;
        for (let i = 0; i < 12; i++) {
            suma += parseInt(isbn[i]) * (i % 2 === 0 ? 1 : 3);
        }
        const checksum = (10 - (suma % 10)) % 10;
        return checksum === parseInt(isbn[12]);
    }
    
    return false;
}

// Validar que un campo no esté vacío
function validarNoVacio(valor) {
    return valor.trim().length > 0;
}

// Validar longitud mínima
function validarLongitudMinima(valor, minimo) {
    return valor.trim().length >= minimo;
}

// Validar longitud máxima
function validarLongitudMaxima(valor, maximo) {
    return valor.trim().length <= maximo;
}

// Validar número entero positivo
function validarNumeroPositivo(numero) {
    return Number.isInteger(Number(numero)) && Number(numero) > 0;
}

// Validar fecha (no puede ser futura)
function validarFechaNoFutura(fecha) {
    const fechaIngresada = new Date(fecha);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    return fechaIngresada <= hoy;
}

// Validar fecha (debe ser futura)
function validarFechaFutura(fecha) {
    const fechaIngresada = new Date(fecha);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    return fechaIngresada > hoy;
}

// Mostrar error en campo
function mostrarError(campo, mensaje) {
    campo.classList.add('error');
    
    // Remover mensaje de error previo si existe
    const errorPrevio = campo.parentElement.querySelector('.error-message');
    if (errorPrevio) {
        errorPrevio.remove();
    }
    
    // Crear nuevo mensaje de error
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = mensaje;
    errorDiv.style.color = '#ef4444';
    errorDiv.style.fontSize = '0.85rem';
    errorDiv.style.marginTop = '0.25rem';
    
    campo.parentElement.appendChild(errorDiv);
}

// Limpiar error en campo
function limpiarError(campo) {
    campo.classList.remove('error');
    const errorMsg = campo.parentElement.querySelector('.error-message');
    if (errorMsg) {
        errorMsg.remove();
    }
}

// Validar formulario completo
function validarFormulario(formulario) {
    let valido = true;
    const campos = formulario.querySelectorAll('[required]');
    
    campos.forEach(campo => {
        if (!validarNoVacio(campo.value)) {
            mostrarError(campo, 'Este campo es obligatorio');
            valido = false;
        } else {
            limpiarError(campo);
        }
    });
    
    return valido;
}