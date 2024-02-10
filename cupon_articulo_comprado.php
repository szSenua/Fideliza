<?php
    
    require_once 'conecta.php';
    require_once 'menu.php';
    require_once 'funciones.php';

    $rol = isset($_SESSION['tipoUsuario']) ? $_SESSION['tipoUsuario'] : '';
    
    if ((!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true  && $rol !== 'administrador')) {
        // Si no está logado y no es admin
        header('Location: menu.php');
        exit(); 
    }

    $clientes = array();


    foreach (obtenerClientes($con) as $cliente) {
        $clientes[] = $cliente;
    }

    /*echo '<pre>';
    var_dump($clientes);
    echo '</pre>';*/
    

    function obtenerArticuloMasCompradoCliente($con) {
        // Declaro una variable global dentro de la función
        global $clientes;
    
        // Por cada cliente, obtén el artículo más comprado
        foreach ($clientes as $cliente) {
            $sql = "SELECT  itemcompras.articuloid, articulos.anombre, articulos.amarca, compras.clienteid 
            FROM compras, itemcompras, articulos
            WHERE compras.compraid = itemcompras.compraid
            AND compras.clienteid = ?
            AND itemcompras.articuloid = articulos.articuloid
            GROUP BY articuloid 
            ORDER BY SUM(unidades) DESC 
            LIMIT 1";
    
            $stmt = $con->prepare($sql);
            $stmt->bindParam(1, $cliente['clienteid'], PDO::PARAM_INT);
            $stmt->execute();
    
            // Obtener el artículo más comprado para este cliente
            $articulo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Devuelve el artículo más comprado para este cliente
            yield $articulo;
        }
    }
    
    

    $articulos = obtenerArticuloMasCompradoCliente($con);

    function generarCupones($con){
        global $articulos, $cuponesNuevos, $cuponesExistentes, $cuponesNoAsignados;
    
        $cuponesNuevos = array();
        $cuponesExistentes = array();
        $cuponesNoAsignados = array();
    
        // Iterar sobre el generador para mostrar los resultados
        foreach ($articulos as $articulo) {
            // Verificar si $articulo es un array antes de acceder a sus índices puede ser que un cliente no tenga compras y tira error
            if (is_array($articulo)) {
                //echo "Artículo: {$articulo['articuloid']} comprado por: {$articulo['clienteid']}<br>";
    
                //Tomar la fecha actual para los nuevos cupones y la validez será de 7 días
                $hoy = new DateTime();
                $fechai_validez = new DateTime(); 
                $fecha_actual = $hoy->format('Y-m-d');
    
                $fechai_validez->modify('+7 days');
                $fechaf_validez = $fechai_validez->format('Y-m-d');
    
                //Comprobar que no existe el cupón ya
                $descripcion = "25% descuento en " . $articulo['anombre'] . " " . $articulo['amarca'];
                $sintaxisDesc = mb_strtolower($descripcion, 'UTF-8');
    
                $sqlComprueba = "SELECT premioid, ddescrip FROM premios where ddescrip = ?";
                $stmt = $con->prepare($sqlComprueba);
                $stmt->bindParam(1, $sintaxisDesc, PDO::PARAM_STR);
                $stmt->execute();
    
                $premio = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if(!$premio){
                    $sqlInsert = "INSERT INTO premios (ddescrip) VALUES ( ? )";
                    $stmt = $con->prepare($sqlInsert);
            
                    $stmt->bindParam(1, $descripcion, PDO::PARAM_STR);
                    $stmt->execute();
    
                    //Obtengo el id del cupón insertado
                    $premioid = $con->lastInsertId();
    
                    //Asigno el cupón al cliente
                    $sqlAsigna = "INSERT INTO cupones(clienteid, premioid, fechai_validez, fechaf_validez) VALUES ( ?, ?, ?, ? )";
                    $stmt = $con->prepare($sqlAsigna);
                    $stmt->bindParam(1, $articulo['clienteid'], PDO::PARAM_INT);
                    $stmt->bindParam(2, $premioid, PDO::PARAM_INT);
                    $stmt->bindParam(3, $fecha_actual, PDO::PARAM_STR);
                    $stmt->bindParam(4, $fechaf_validez, PDO::PARAM_STR);
    
                    // Ejecutar la inserción
                    $stmt->execute();
    
                    // Ahora, consultamos el cupón recién insertado
                    $sqlConsultaCupon = "SELECT cupones.premioid, clientes.cnombre AS nombre_cliente, premios.ddescrip AS descripcion_premio, 
                                        cupones.fechai_validez, cupones.fechaf_validez 
                                        FROM cupones 
                                        INNER JOIN clientes ON cupones.clienteid = clientes.clienteid 
                                        INNER JOIN premios ON cupones.premioid = premios.premioid 
                                        WHERE premios.premioid = LAST_INSERT_ID()";
                    $stmtConsulta = $con->prepare($sqlConsultaCupon);
                    $stmtConsulta->execute();
    
                    $cuponNuevo = $stmtConsulta->fetch(PDO::FETCH_ASSOC);
    
                    // Agregamos el cupón nuevo al array de cupones nuevos
                    $cuponesNuevos [] = $cuponNuevo;
    
                } else {
                    $idpremio = $premio['premioid'];
                    //Y si existe compruebas que no lo tenga ya el cliente
                    $sqlExiste = "SELECT * from cupones WHERE premioid = ? AND clienteid = ?";    
                    $stmt = $con->prepare($sqlExiste);
                    $stmt->bindParam(1, $idpremio, PDO::PARAM_INT);
                    $stmt->bindParam(2, $articulo['clienteid'], PDO::PARAM_INT);
                        
                    $stmt->execute();
    
                    $premioAsignado = $stmt->fetch(PDO::FETCH_ASSOC);
    
                    if (!$premioAsignado){
                    
                        //Asigno el cupón al cliente
                        $sqlAsigna = "INSERT INTO cupones(clienteid, premioid, fechai_validez, fechaf_validez) VALUES ( ?, ?, ?, ? )";
                        $stmt = $con->prepare($sqlAsigna);
                        $stmt->bindParam(1, $articulo['clienteid'], PDO::PARAM_INT);
                        $stmt->bindParam(2, $idpremio, PDO::PARAM_INT);
                        $stmt->bindParam(3, $fecha_actual, PDO::PARAM_STR);
                        $stmt->bindParam(4, $fechaf_validez, PDO::PARAM_STR);
    
                        // Ejecutar la inserción
                        $stmt->execute();
    
                        // Ahora, consultamos el cupón recién insertado
                        $sqlConsultaCupon = "SELECT cupones.premioid, clientes.cnombre AS nombre_cliente, premios.ddescrip AS descripcion_premio, 
                                            cupones.fechai_validez, cupones.fechaf_validez 
                                            FROM cupones 
                                            INNER JOIN clientes ON cupones.clienteid = clientes.clienteid 
                                            INNER JOIN premios ON cupones.premioid = premios.premioid 
                                            WHERE premios.premioid = LAST_INSERT_ID()";
                        $stmtConsulta = $con->prepare($sqlConsultaCupon);
                        $stmtConsulta->execute();
    
                        $cuponExistente = $stmtConsulta->fetch(PDO::FETCH_ASSOC);
    
                        // Agregamos el cupón existente al array de cupones existentes
                        $cuponesExistentes [] = $cuponExistente;
                    } else {
                        // Si el cupón ya está asignado al cliente, obtenemos el nombre del cliente y la descripción del premio
                        $sqlInfoCupon = "SELECT c.cnombre AS nombre_cliente, p.ddescrip AS descripcion_premio
                                         FROM clientes c
                                         INNER JOIN cupones cu ON c.clienteid = cu.clienteid
                                         INNER JOIN premios p ON cu.premioid = p.premioid
                                         WHERE cu.premioid = ? AND cu.clienteid = ?";
                        $stmtInfoCupon = $con->prepare($sqlInfoCupon);
                        $stmtInfoCupon->bindParam(1, $idpremio, PDO::PARAM_INT);
                        $stmtInfoCupon->bindParam(2, $articulo['clienteid'], PDO::PARAM_INT);
                        $stmtInfoCupon->execute();
                        $cuponInfo = $stmtInfoCupon->fetch(PDO::FETCH_ASSOC);
                    
                        // Mostramos el mensaje con el nombre del cliente y la descripción del premio
                        echo "<h2>El cupón con id " . $idpremio ." ya está asignado al cliente " . $cuponInfo['nombre_cliente'] . " - Premio: " . $cuponInfo['descripcion_premio'] . "</h2>";
                    }
                } 
            }
        } 
    
        return array(
            'cuponesNuevos' => $cuponesNuevos,
            'cuponesExistentes' => $cuponesExistentes
        );
    }
    

    $cupones = generarCupones($con);
 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupones generados</title>
</head>
<body>
<h2>Cupones nuevos asignados</h2>
<ul>
<?php
$cuponesNuevos = $cupones['cuponesNuevos'];

if(is_array($cuponesNuevos) && sizeof($cuponesNuevos) > 0){
    foreach($cuponesNuevos as $cupon){
        echo "<li>Cupón ID: " . $cupon['premioid'] . " - Cliente: " .$cupon['nombre_cliente'] . " - Premio: " . $cupon['descripcion_premio'] . 
        " - Fecha de inicio de validez: " . $cupon['fechai_validez'] . " - Fecha de fin de validez: " . $cupon['fechaf_validez'] . "</li>";
    }
}else {
    echo "<h3>Ningún cupón nuevo ha sido asignado</h3>";
}
?>
</ul>

<h2>Cupones existentes asignados</h2>
<ul>
<?php

$cuponesExistentes = $cupones['cuponesExistentes'];

if(is_array($cuponesExistentes) && sizeof($cuponesExistentes) > 0){
    foreach($cuponesExistentes as $cuponExistente){
        echo "<li>Cupón ID: " . $cuponExistente['premioid'] . " - Cliente: " .$cuponExistente['nombre_cliente'] . " - Premio: " . $cuponExistente['descripcion_premio'] . 
        " - Fecha de inicio de validez: " . $cuponExistente['fechai_validez'] . " - Fecha de fin de validez: " . $cuponExistente['fechaf_validez'] . "</li>";
    }
} else {
    echo "<h3>Ningún cupón existente ha sido asignado</h3>";
}

?>
</ul>

</body>
</html>

<?php
require_once 'desconecta.php';


?>