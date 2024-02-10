<?php
require_once 'conecta.php';
require_once 'menu.php';


// Verifica si el usuario está logueado y no es cliente
if ((!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true  && $rol !== 'cliente')) {
    // Si no está logado y no es cliente
    header('Location: menu.php');
    exit();
}


function misCupones($con){
    global $id;

    $hoy = new DateTime();
    $fecha_actual = $hoy->format('Y-m-d');

    $sql = "SELECT ddescrip, cupones.fechai_validez, cupones.fechaf_validez 
    FROM cupones, premios
    WHERE cupones.premioid = premios.premioid
    AND cupones.clienteid = ?
    AND cupones.fechaf_validez >= ? 
    ORDER BY cupones.premioid";

    $stmt = $con->prepare($sql);
    $stmt->bindParam(1, $id, PDO::PARAM_STR);
    $stmt->bindParam(2, $fecha_actual, PDO::PARAM_STR);

    $stmt->execute();

    while($cupones = $stmt->fetch(PDO::FETCH_ASSOC)){
        yield $cupones;
    }

}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Cupones</title>
    <style>
        h2{
            text-align: center;
        }
       

        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center; 
        }

        .card {
            border: 1px solid #342042;
            border-radius: 5px;
            padding: 10px;
            margin: 10px;
            width: 300px;
            text-align: center; 
        }
    </style>
</head>
<body>
    <h2>Mis Cupones</h2>
    <div class="card-container">
        <?php
        foreach (misCupones($con) as $cupon) {
            ?>
            <div class="card">
                <h3><?php echo $cupon['ddescrip']; ?></h3>
                <p><strong>Inicio de validez:</strong> <?php echo $cupon['fechai_validez']; ?></p>
                <p><strong>Fin de validez:</strong> <?php echo $cupon['fechaf_validez']; ?></p>
            </div>
            <?php
        }
        ?>
    </div>
</body>
</html>

<?php

require_once 'desconecta.php';

?>
