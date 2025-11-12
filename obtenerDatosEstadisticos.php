<?php
// indicamos que la salida será JSON
header('Content-Type: application/json');
include 'obtenerBaseDeDatos.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$con = ObtenerDB();
$con->set_charset("utf8mb4");

$totalLibros = ObtenerTotalDeTabla($con, "libros");
$totalUsuarios = ObtenerTotalDeTabla($con, "usuarios");
$totalPrestamosActivos = ObtenerTotalDeTabla($con, "prestamos", 1);
$totalPrestamosVencidos = ObtenerTotalDeTabla($con, "prestamos", 2);
$nuevoPrestamo = ObtenerPrestamoReciente($con, true);
$nuevoLibro = ObtenerLibroAgregadoReciente($con);
$libroDevuelto = ObtenerPrestamoReciente($con);

mysqli_report(MYSQLI_REPORT_OFF);


// ------------- FUNCIONES -----------------
function ObtenerTotalDeTabla($con, $tabla, $prestamoTipo = 0){
    $total = 0;
    
    if($tabla === "prestamos" && $prestamoTipo === 1){
        $estado = "activo";
        $sql = "SELECT COUNT(*) AS total FROM `$tabla` WHERE estado = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $estado);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();

    } else if($tabla === "prestamos" && $prestamoTipo === 2){
        $estado = "vencido";
        $sql = "SELECT COUNT(*) AS total FROM `$tabla` WHERE estado = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $estado);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();

    } else {
        $sql = "SELECT COUNT(*) AS total FROM `$tabla`";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();
    }
    
    return $total;
}

function ObtenerPrestamoReciente($con, $created_at = false){
    $Retorno = "No disponible";

    if($created_at === true){

        try{
            $SQL = "SELECT MAX(created_at) FROM prestamos";
            $Resultado = $con->query($SQL);

            if($Resultado && $Resultado->num_rows > 0) {
                $Registro = $Resultado->fetch_assoc();
                $HoraOriginal = $Registro['MAX(created_at)'];
                
                if($HoraOriginal !== null) {
                    $DateTime_HoraOriginal = new DateTime($HoraOriginal);
                    $DateTime_Servidor = new DateTime();
                    $DateInterval_Diferencia = $DateTime_HoraOriginal->diff($DateTime_Servidor);
                    $Retorno = FormatearDateInterval($DateInterval_Diferencia);
                }
            } else {
                error_log("No se encontró el registro o ocurrió un error.");
            }

            if (isset($Resultado)) { 
                $Resultado->free(); 
            }

        } catch (mysqli_sql_exception $e) {
            error_log("Error SQL: " . $e->getMessage());
            error_log("Ocurrió un error al procesar la solicitud.");
        }

    } else {

        try{
            $SQL = "SELECT MAX(fecha_dev_real) FROM prestamos WHERE estado = 'devuelto'";
            $Resultado = $con->query($SQL);

            if($Resultado && $Resultado->num_rows > 0) {
                $Registro = $Resultado->fetch_assoc();
                $HoraOriginal = $Registro['MAX(fecha_dev_real)'];
                
                if($HoraOriginal !== null) {
                    $DateTime_HoraOriginal = new DateTime($HoraOriginal);
                    $DateTime_Servidor = new DateTime();
                    $DateInterval_Diferencia = $DateTime_HoraOriginal->diff($DateTime_Servidor);
                    $Retorno = FormatearDateInterval($DateInterval_Diferencia);
                }
            } else {
                error_log("No se encontró el registro o ocurrió un error.");
            }

            if (isset($Resultado)) { 
                $Resultado->free(); 
            }

        } catch (mysqli_sql_exception $e) {
            error_log("Error SQL: " . $e->getMessage());
            error_log("Ocurrió un error al procesar la solicitud.");
        }
    }

    return $Retorno;
}

function ObtenerLibroAgregadoReciente($con){
    $Retorno = "No disponible";

    try{
        $SQL = "SELECT MAX(created_at) FROM libros";
        $Resultado = $con->query($SQL);

        if($Resultado && $Resultado->num_rows > 0) {
            $Registro = $Resultado->fetch_assoc();
            $HoraOriginal = $Registro['MAX(created_at)'];
            
            if($HoraOriginal !== null) {
                $DateTime_HoraOriginal = new DateTime($HoraOriginal);
                $DateTime_Servidor = new DateTime();
                $DateInterval_Diferencia = $DateTime_HoraOriginal->diff($DateTime_Servidor);
                $Retorno = FormatearDateInterval($DateInterval_Diferencia);
            }
        } else { 
            error_log("No se encontró el registro o ocurrió un error."); 
        }

        if (isset($Resultado)) { 
            $Resultado->free(); 
        }

    } catch (mysqli_sql_exception $e) {
        error_log("Error SQL: " . $e->getMessage());
        error_log("Ocurrió un error al procesar la solicitud.");
    }

    return $Retorno;
}

function FormatearDateInterval($diferencia){
    $texto = "";
    $mes = 'meses';
    $dia = 'días';
    $hora = 'horas';
    $minuto = 'minutos';
    $segundo = 'segundos';

    if ($diferencia->y > 0 || $diferencia->m > 0) {

        if($diferencia->m == 1) { $mes = 'mes'; }
        if($diferencia->d == 1) { $dia = 'día'; }

        $texto = $diferencia->format("%m $mes y %d $dia");

    } elseif ($diferencia->d > 0) {

        if($diferencia->d == 1) { $dia = 'día'; }

        $texto = $diferencia->format("%a $dia");

    } elseif ($diferencia->h > 0) {

        if($diferencia->h == 1) { $hora = 'hora'; }

        $texto = $diferencia->format("%h $hora y %i $minuto");

    } elseif ($diferencia->i > 0) {

        if($diferencia->i == 1) { $minuto = 'minuto'; }

        $texto = $diferencia->format("%i $minuto");

    } else {

        if($diferencia->s == 1) { $segundo = 'segundo'; }

        $texto = $diferencia->format("%s $segundo");
    }

    return $texto;
}

$datos = [
    'totalLibros' => $totalLibros,
    'totalUsuarios' => $totalUsuarios,
    'prestamosActivos' => $totalPrestamosActivos,
    'prestamosVencidos' => $totalPrestamosVencidos,
    'nuevoPrestamo' => $nuevoPrestamo,
    'nuevoLibro' => $nuevoLibro,
    'libroDevuelto' => $libroDevuelto
];

ob_clean();
echo json_encode($datos);
$con->close();
?>