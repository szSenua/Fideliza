

<?php

require_once 'conecta.php';

//Función para obtener el tipo de usuario


function obtenerInfoUsuario($email, $contrasena) {
    global $con;

    // Consultar el tipo de usuario en la tabla administradores
    $queryAdmin = "SELECT email, contrasena FROM administradores WHERE email=:email AND contrasena=:contrasena";
    $stmtAdmin = $con->prepare($queryAdmin);
    $stmtAdmin->bindParam(':email', $email);
    $stmtAdmin->bindParam(':contrasena', $contrasena);
    $stmtAdmin->execute();

    // Si es un administrador, devolver información del administrador
    if ($stmtAdmin->rowCount() > 0) {
        $adminData = $stmtAdmin->fetch(PDO::FETCH_ASSOC);
        // Cerrar conexión
        require_once 'desconecta.php';
        return array(
            'tipo' => 'administrador',
            'email' => $adminData['email'],
            'nombre' => $adminData['nombre']
        );
    }

    // Consultar el tipo de usuario en la tabla clientes
    $queryCliente = "SELECT cemail, cnombre, cclave, clienteid FROM clientes WHERE cemail=:email AND cclave=:contrasena";
    $stmtCliente = $con->prepare($queryCliente);
    $stmtCliente->bindParam(':email', $email);
    $stmtCliente->bindParam(':contrasena', $contrasena);
    $stmtCliente->execute();

    // Si es un cliente, devolver información del cliente
    if ($stmtCliente->rowCount() > 0) {
        $clienteData = $stmtCliente->fetch(PDO::FETCH_ASSOC);
        // Cerrar conexión
        require_once 'desconecta.php';
        return array(
            'tipo' => 'cliente',
            'email' => $clienteData['cemail'],
            'nombre' => $clienteData['cnombre'],
            'clienteid' => $clienteData['clienteid']
        );
    }

    // Si no se encuentra en ninguna tabla, devolver null
    require_once 'desconecta.php';
    return null;
}




function pintaLoginconParam($email, $contrasena, $errores) {
    echo '
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title></title>
</head>
<body>
    ';


    echo '<div class="login"><form action="login.php" method="post" class="form">';
    
    // Mostrar errores solo si la variable $errores no está vacía
    if (!empty($errores)) {
        echo '<div class="alert alert-danger" role="alert">
            <ul>';
        foreach ($errores as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '</ul></div>';
    }

    echo '
        <h2>Bienvenid@ al login</h2>
        <input type="text" name="email" value="' . htmlspecialchars($email) . '" placeholder="email">
        <input type="password" name="contrasena" value="' . htmlspecialchars($contrasena) . '" placeholder="Contraseña">
        <input type="submit" value="Enviar" class="submit">
        <a href="registro.php">Registrarse</a>
      </form>
    </div>
    </body>
    </html>';
}





//función para validar el correo

function validarCorreo($correo) {
    return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
}

//Función para validar un teléfono fijo o móvil
function validarTelefono($telefono) {
    // Eliminar cualquier caracter que no sea dígito
    $numero = preg_replace("/[^0-9]/", "", $telefono);

    // Comprobar si el número tiene un formato válido para teléfonos españoles
    if (preg_match("/^(34)?[6789]\d{8}$/", $numero)) {
        return true;
    }

    return false;
}


function obtenerClientes($conexion){

    //Obtengo los clientes
    $sqlClientes = "SELECT clienteid FROM clientes";
    $stmt = $conexion->prepare($sqlClientes);
    $stmt->execute();

    while ($cliente = $stmt->fetch(PDO::FETCH_ASSOC)) {
        yield $cliente;
    }
}

function obtenerPremios($conexion) {
    $hoy = new DateTime();
    $fecha_actual = $hoy->format('Y-m-d');
    
    $sql = "SELECT * FROM premios where fechaf_validez >= ? ORDER BY premioID";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(1, $fecha_actual);
    $stmt->execute();

    while ($premio = $stmt->fetch(PDO::FETCH_ASSOC)) {
        yield $premio;
    }
}



?>
</body>
</html>

