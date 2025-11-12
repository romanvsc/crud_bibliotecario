<?php 
require_once 'token_confirmar.php';
require_once 'obtenerBaseDeDatos.php';
redirigir(comprobarToken(ObtenerDB()));
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Iniciar Sesión - Sistema Biblioteca</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        /* Fondo y texto */
        --color-fondo: #ffffff;
        --color-fondo-claro: #f9fafb;
        --color-texto-principal: #111827;
        --color-texto-secundario: #4b5563;
        --color-borde: #e5e7eb;

        /* Borgoña clásico */
        --color-acento: #b91c1c;
        --color-acento-suave: #fee2e2;

        /* Estados */
        --color-exito: #10b981;
        --color-advertencia: #f59e0b;
        --color-error: #ef4444;
        --color-info: #3b82f6;
        
        /* Sombras */
        --sombra-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --sombra-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --sombra-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        
        /* Espaciado */
        --espaciado-xs: 0.25rem;
        --espaciado-sm: 0.5rem;
        --espaciado-md: 1rem;
        --espaciado-lg: 1.5rem;
        --espaciado-xl: 2rem;
        
        /* Bordes */
        --radio-sm: 0.25rem;
        --radio-md: 0.5rem;
        --radio-lg: 0.75rem;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%);
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: var(--espaciado-lg);
    }

    .login-wrapper {
        width: 100%;
        max-width: 420px;
    }

    .login-header {
        text-align: center;
        margin-bottom: var(--espaciado-xl);
    }

    .login-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--espaciado-sm);
        font-size: 2rem;
        font-weight: 700;
        color: var(--color-acento);
        margin-bottom: var(--espaciado-md);
    }

    .login-logo i {
        font-size: 2.5rem;
    }

    .login-subtitle {
        color: var(--color-texto-secundario);
        font-size: 0.95rem;
    }

    .login-container {
        background-color: var(--color-fondo);
        padding: var(--espaciado-xl);
        border-radius: var(--radio-lg);
        box-shadow: var(--sombra-lg);
    }

    .login-title {
        font-size: 1.5rem;
        color: var(--color-texto-principal);
        margin-bottom: var(--espaciado-xl);
        text-align: center;
        font-weight: 600;
    }

    .form-group {
        margin-bottom: var(--espaciado-lg);
    }

    .form-label {
        display: flex;
        align-items: center;
        gap: var(--espaciado-xs);
        margin-bottom: var(--espaciado-sm);
        font-weight: 600;
        color: var(--color-texto-principal);
        font-size: 0.95rem;
    }

    .form-label i {
        color: var(--color-acento);
    }

    .input-wrapper {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: var(--espaciado-md);
        top: 50%;
        transform: translateY(-50%);
        color: var(--color-texto-secundario);
        pointer-events: none;
    }

    .form-control {
        width: 100%;
        padding: var(--espaciado-sm) var(--espaciado-md) var(--espaciado-sm) 2.75rem;
        border: 1px solid var(--color-borde);
        border-radius: var(--radio-md);
        font-size: 1rem;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--color-acento);
        box-shadow: 0 0 0 3px var(--color-acento-suave);
    }

    .btn-login {
        width: 100%;
        padding: var(--espaciado-md);
        background-color: var(--color-acento);
        color: white;
        border: none;
        border-radius: var(--radio-md);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--espaciado-sm);
        margin-top: var(--espaciado-xl);
    }

    .btn-login:hover {
        background-color: #991b1b;
        box-shadow: var(--sombra-md);
        transform: translateY(-1px);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .btn-login:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Mensaje de error */
    #MensajeDeError {
        position: fixed;
        top: var(--espaciado-lg);
        left: 50%;
        transform: translateX(-50%);
        background-color: var(--color-acento-suave);
        color: var(--color-acento);
        border: 1px solid var(--color-acento);
        border-radius: var(--radio-md);
        padding: var(--espaciado-md) var(--espaciado-lg);
        min-width: 300px;
        max-width: 90%;
        text-align: center;
        font-weight: 500;
        box-shadow: var(--sombra-lg);
        display: none;
        z-index: 1000;
        animation: slideDown 0.3s ease-out;
    }

    #MensajeDeError i {
        margin-right: var(--espaciado-sm);
    }

    @keyframes slideDown {
        from { 
            opacity: 0; 
            transform: translate(-50%, -20px);
        }
        to { 
            opacity: 1; 
            transform: translate(-50%, 0);
        }
    }

    .login-footer {
        text-align: center;
        margin-top: var(--espaciado-lg);
        color: var(--color-texto-secundario);
        font-size: 0.875rem;
    }

    /* Loading spinner */
    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Responsive */
    @media (max-width: 480px) {
        .login-logo {
            font-size: 1.5rem;
        }

        .login-logo i {
            font-size: 2rem;
        }

        .login-container {
            padding: var(--espaciado-lg);
        }
    }
</style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-header">
        <div class="login-logo">
            <i class="fas fa-book"></i>
            <span>Sistema Biblioteca</span>
        </div>
        <p class="login-subtitle">Gestión de préstamos y catálogo</p>
    </div>

    <div class="login-container">
        <h2 class="login-title">Iniciar Sesión</h2>
        <form id="FormularioDeSesion">
            <div class="form-group">
                <label class="form-label" for="usuario">
                    <i class="fas fa-user"></i>
                    Usuario
                </label>
                <div class="input-wrapper">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" 
                           id="usuario" 
                           name="usuario" 
                           class="form-control" 
                           placeholder="Ingrese su usuario" 
                           required 
                           autocomplete="username">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="contraseña">
                    <i class="fas fa-lock"></i>
                    Contraseña
                </label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" 
                           id="contraseña" 
                           name="contraseña" 
                           class="form-control" 
                           placeholder="Ingrese su contraseña" 
                           required 
                           autocomplete="current-password">
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Iniciar Sesión
            </button>
        </form>
    </div>

    <div class="login-footer">
        <p>&copy; 2025 Sistema de Gestión de Biblioteca</p>
    </div>
</div>

<div id="MensajeDeError">
    <i class="fas fa-exclamation-circle"></i>
    <span id="MensajeTexto"></span>
</div>

<script>
    document.getElementById("FormularioDeSesion").addEventListener("submit", function(event) {
        event.preventDefault();

        const submitBtn = event.target.querySelector('.btn-login');
        const originalContent = submitBtn.innerHTML;
        
        // Deshabilitar botón y mostrar loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Iniciando sesión...';

        let formData = new FormData(this);
        fetch("./login_validar.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const mensaje = document.getElementById("MensajeDeError");
            const mensajeTexto = document.getElementById("MensajeTexto");
            
            if (data.success) {
                mensaje.style.display = "none";
                window.location.href = "index.php";
            } else {
                mensajeTexto.innerText = "Usuario o contraseña incorrectos.";
                mensaje.style.display = "block";
                
                // Ocultar mensaje después de 5 segundos
                setTimeout(() => {
                    mensaje.style.display = "none";
                }, 5000);
                
                // Restaurar botón
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalContent;
            }
        })
        .catch(error => {
            const mensaje = document.getElementById("MensajeDeError");
            const mensajeTexto = document.getElementById("MensajeTexto");
            mensajeTexto.innerText = "Error de conexión. Por favor, intente nuevamente.";
            mensaje.style.display = "block";
            
            // Ocultar mensaje después de 5 segundos
            setTimeout(() => {
                mensaje.style.display = "none";
            }, 5000);
            
            // Restaurar botón
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalContent;
            
            console.error("Error:", error);
        });
    });
</script>

</body>
</html>
