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
<title>Login</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f3f4f6;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .login-container {
        background-color: white;
        padding: 30px 25px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 300px;
        text-align: center;
    }

    h2 {
        margin-bottom: 20px;
        color: #333;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 10px;
        margin: 8px 0 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    input[type="submit"] {
        background-color: #4f46e5;
        color: white;
        border: none;
        padding: 10px;
        width: 100%;
        border-radius: 5px;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        background-color: #4338ca;
    }

    p {
        margin-top: 10px;
        font-size: 0.9em;
        color: #666;
    }

    /* 🟥 Recuadro del mensaje de error */
    #MensajeDeError {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fca5a5;
        border-radius: 8px;
        padding: 12px 20px;
        min-width: 250px;
        text-align: center;
        font-weight: bold;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        display: none;/* Oculto hasta que haya error */
        z-index: 1;
        animation: aparecer 0.3s ease-in-out;
    }

    @keyframes aparecer {
        from { opacity: 0; transform: translate(-50%, 20px); }
        to { opacity: 1; transform: translate(-50%, 0); }
    }

</style>
</head>
<body>

<div class="login-container">
    <h2>Iniciar Sesión</h2>
    <form action="login_validar.php" method="post" id="FormularioDeSesion">
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" name="contraseña" placeholder="Contraseña" required>
        <input type="submit" value="Entrar">
    </form>
</div>

<div id="MensajeDeError">Texto:</div>

<script>
    document.getElementById("FormularioDeSesion").addEventListener("submit", function(event) {
        event.preventDefault();

        let formData = new FormData(this);
        fetch("./login_validar.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json()) // ahora esperamos JSON
        .then(data => {
            mensaje = document.getElementById("MensajeDeError");
            if (data.success) {
                mensaje.style.display = "none";
                window.location.href = "index.php";
            } else {
                mensaje.innerText = "Usuario o contraseña incorrectos.";
                mensaje.style.display = "block";
            }
        })
        .catch(error => {
            const mensaje = document.getElementById("MensajeDeError");
            mensaje.innerText = "Error de conexión.";
            mensaje.style.display = "block";
            console.error("Error:", error);
        });
    });
</script>

</body>
</html>
