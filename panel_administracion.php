<?php
require_once 'conecta.php';
require_once 'menu.php';

$rol = isset($_SESSION['tipoUsuario']) ? $_SESSION['tipoUsuario'] : '';

// Verifica si el usuario está logueado y no es un administrador
if ((!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true  && $rol !== 'administrador')) {
    // Si no está logado y no es administrador
    header('Location: menu.php');
    exit();
}

if (isset($_GET['error'])) {
    $error = $_GET['error'];

    echo "<div id='errores'>$error</div>";

}

function obtenerPremios($conexion) {
    $hoy = new DateTime();
    $fecha_actual = $hoy->format('Y-m-d');
    
    $sql = "SELECT * FROM premios where fechaf_validez >= ? ORDER BY premioID";
    
    $stmt = $conexion->prepare($sql);
    $stmt -> bindParam(1, $fecha_actual);
    
    $stmt->execute();

    // Usar fetch para obtener resultados uno a uno
    while ($premio = $stmt->fetch(PDO::FETCH_ASSOC)) {
        yield $premio;
    }
}

function obtenerCuponesDeCliente($conexion) {
    //hacer un inner join
    $sql = "SELECT clientes.clienteid, clientes.cnombre, clientes.capellido, premios.premioid, premios.ddescrip, cupones.fechai_validez, cupones.fechaf_validez
    from clientes, premios, cupones
    where premios.premioid = cupones.premioid
    AND clientes.clienteid = cupones.clienteid";  
    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    // Utilizar yield from para extraer los valores del generador
    while ($cupon = $stmt->fetch(PDO::FETCH_ASSOC)) {
        yield $cupon;
    }
}

$cupones = obtenerCuponesDeCliente($con);
$premios = obtenerPremios($con);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        #panel-container{
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
            padding: 2em;
        }
        table {
            width: 96%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
           
            border: 1px solid #342042;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        td{
            background: #fff;
        }

        th {
            background-color: #342042;
            color: white;
        }

        .btn-accion {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-crear {
            background-color: #4CAF50;
            color: white;
        }

        .btn-actualizar {
            background-color: #357EDD;
            color: white;
        }

        .btn-eliminar {
            background-color: #FF3030;
            color: white;
        }

        .btn-anulado {
            background-color: #ccc;
            color: white;
        }

        img{
            width: 65px;
            height: 100px;
        }

        #errores {
            color: #FF3030;
        }
    </style>
    <title>Panel de Administración</title>
</head>

<body>
    <h2>Panel de Administración - Premios</h2>

    <!-- Botón para crear un nuevo premio -->
    <form action="crear_premio.php" method="post">
        <a href="crear_premio.php"><button class="btn-accion btn-crear">Crear Nuevo Premio</button></a>
    </form>

    <div id="panel-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Descripcion</th>
                    <th>Fecha Inicio Validez</th>
                    <th>Fecha Fin Validez</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($premios as $premio) : ?>
                    <tr>
                        <td><?= $premio['premioid']; ?></td>
                        <td><?= $premio['ddescrip']; ?></td>
                        <td><?= $premio['fechai_validez']; ?></td>
                        <td><?= $premio['fechaf_validez']; ?></td>
                        <td>
                            <!-- Botones de acciones (eliminar) -->
                            <form action="eliminar_premio.php" method="post" onsubmit="return confirm('¿Estás seguro de que deseas borrar este premio?');" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $premio['premioid']; ?>">
                                <input type="hidden" name="fechaf_validez" value="<?= $premio['fechaf_validez']; ?>">
                                <button type="submit" class="btn-accion btn-eliminar">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h2>Panel de Administración - Cupones de clientes</h2>

    <div id="panel-container">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Cupon</th>
                    <th>Fecha Inicio Validez</th>
                    <th>Fecha Fin Validez</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cupones as $cupon) : ?>
    <tr>
        <td><?= $cupon['cnombre']; ?></td>
        <td><?= $cupon['capellido']; ?></td>
        <td><?= $cupon['ddescrip']; ?></td>
        <td><?= $cupon['fechai_validez']; ?></td>
        <td><?= $cupon['fechaf_validez']; ?></td>
        <td>
            <?php if ($cupon['fechai_validez'] === '' && $cupon['fechaf_validez'] === '') : ?>
                <button class="btn-accion btn-anulado" disabled>Anulado</button>
            <?php elseif ($cupon['fechai_validez'] === '0000-00-00' || $cupon['fechaf_validez'] === '0000-00-00') : ?>
                <button class="btn-accion btn-anulado" disabled>Anulado</button>
            <?php else : ?>
                <form action="anular_cupon.php" method="post" onsubmit="return confirm('¿Estás seguro de que deseas anular este cupón?');" style="display: inline;">
                    <input type="hidden" name="id" value="<?= $cupon['premioid']; ?>">
                    <input type="hidden" name="fechaf_validez" value="<?= $cupon['fechaf_validez']; ?>">
                    <input type="hidden" name="fechai_validez" value="<?= $cupon['fechai_validez']; ?>">
                    <input type="hidden" name="clienteid" value="<?= $cupon['clienteid']; ?>">
                    <button type="submit" class="btn-accion btn-eliminar">Anular</button>
                </form>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    // Cerrar la conexión
    require_once 'desconecta.php';
    ?>

</body>

</html>
