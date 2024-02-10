<?php
require_once 'menu.php';
$rol = isset($_SESSION['tipoUsuario']) ? $_SESSION['tipoUsuario'] : '';

if ((!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true  && $rol !== 'administrador')) {
    // Si no está logado y no es admin
    header('Location: menu.php');
    exit(); 
}

include_once 'conecta.php';

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    // Recoger los datos del formulario
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
    $fechai_validez = isset($_POST['fechai_validez']) ? $_POST['fechai_validez'] : '';
    $fechaf_validez = isset($_POST['fechaf_validez']) ? $_POST['fechaf_validez'] : '';


    // Insertar los datos en la base de datos
    $sql = "INSERT INTO premios (ddescrip, fechai_validez, fechaf_validez) VALUES (?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(1, $descripcion, PDO::PARAM_STR);
    $stmt->bindParam(2, $fechai_validez, PDO::PARAM_STR);
    $stmt->bindParam(3, $fechaf_validez, PDO::PARAM_STR);
 

    if ($stmt->execute()) {
        header("Location: panel_administracion.php");
    } else {
        echo '<p style="color: red;">Error al crear el premio.</p>';
        echo '<p style="color: red;">' . $stmt->errorInfo()[2] . '</p>';
    }
}

// Cerrar la conexión
require_once 'desconecta.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
        }

        input,
        textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 16px;
            box-sizing: border-box;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
    <title>Crear Premio</title>
</head>

<body>
    <div class="container">
        <h2>Crear Nuevo Premio</h2>
        <form action="crear_premio.php" method="post">

            <label for="descripcion">Descripción del Producto:</label>
            <textarea id="descripcion" name="descripcion" required></textarea>

            <label for="fechai_validez">Fecha de Inicio de Validez del Premio:</label>
            <input type="date" id="peso" name="fechai_validez" required>

            <label for="fechaf_validez">Fecha Final de Validez del Premio:</label>
            <input type="date" id="precio" name="fechaf_validez" required>

            <button type="submit" name="submit">Crear Producto</button>
        </form>
    </div>
</body>

</html>