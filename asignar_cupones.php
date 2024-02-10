<?php
require_once 'funciones.php';
require_once 'menu.php';
$rol = isset($_SESSION['tipoUsuario']) ? $_SESSION['tipoUsuario'] : '';

if ((!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true  && $rol !== 'administrador')) {
    // Si no está logado y no es admin
    header('Location: menu.php');
    exit(); 
}

require_once 'conecta.php';
$clientes = array();
$premios = array();




$nuevosCupones = array(); // Almacena los nuevos cupones
$seRealizaronInserciones = false;

// Almacenar los resultados del yield en un array antes de la inserción
foreach (obtenerClientes($con) as $cliente) {
    $clientes[] = $cliente;
}

foreach (obtenerPremios($con) as $premio) {
    $premios[] = $premio;
}

foreach ($premios as $premio){
    foreach ($clientes as $cliente){
        $sqlBusca = "SELECT premioid from cupones where clienteid = ? and premioid = ?";
        $stmt = $con->prepare($sqlBusca);
        $stmt->bindParam(1, $cliente['clienteid']);
        $stmt->bindParam(2, $premio['premioid']);
        $stmt->execute();

        $filas_afectadas = $stmt->rowCount();


        if ($filas_afectadas === 0){

            //Tomar la fecha actual para los nuevos cupones y la validez será de 7 días
            $fechai_validez = new DateTime(); 
            $fecha_actual = $hoy->format('Y-m-d');

            $fechai_validez->modify('+7 days');
            $fechaf_validez = $fechai_validez->format('Y-m-d');

            $seRealizaronInserciones = true;
            $sql = "INSERT INTO cupones (clienteid, premioid, fechai_validez, fechaf_validez) VALUES (?, ?, ?, ?)";
            $stmt = $con->prepare($sql);

            $stmt->bindParam(1, $cliente['clienteid']);
            $stmt->bindParam(2, $premio['premioid']);
            $stmt->bindParam(3, $fecha_actual);
            $stmt->bindParam(4, $fechaf_validez);

            $stmt->execute();

            // Obtengo el cuponid del cupón recién insertado
            $cuponid = $con->lastInsertId();
            
            // Guardo el id de los cupones recién insertados    
            $nuevosCupones[] = $cuponid;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupones asignados</title>
</head>
<body>
    <h2>Cupones Asignados</h2>
    
    <?php
   if (!$seRealizaronInserciones) {
    echo "<h3>No hay nuevas inserciones</h3>";
        } else {
        echo "<ul>";
        foreach ($nuevosCupones as $cuponid) {
            $sqlSeleccionarAsignado = "SELECT clientes.cnombre, clientes.capellido, premios.ddescrip 
                                       FROM clientes, premios, cupones
                                       WHERE cupones.clienteid = clientes.clienteid 
                                       AND cupones.premioid = ?";
            $stmtSeleccionarAsignado = $con->prepare($sqlSeleccionarAsignado);
            $stmtSeleccionarAsignado->bindParam(1, $cuponid);
            $stmtSeleccionarAsignado->execute();

            while ($asignado = $stmtSeleccionarAsignado->fetch(PDO::FETCH_ASSOC)) {
                echo "<li>" . $asignado['cnombre'] . " " . $asignado['capellido'] . " - " . $asignado['ddescrip']  . "</li>";
            }
        }
        echo "</ul>";
    }
    ?>
</body>
</html>

<?php
require_once 'desconecta.php';


?>
