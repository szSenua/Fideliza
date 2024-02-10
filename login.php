<?php
require_once 'conecta.php';
require_once 'funciones.php';


$email = '';
$contrasena = '';
$errores = array();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // Si no es una solicitud POST, mostrar el formulario
    pintaLoginconParam($email, $contrasena, $errores);
} else {
    // Verificar si el usuario y contraseña son válidos
    $email = $_POST['email'];
    $contrasena = $_POST['contrasena'];

    // Obtener información del usuario
    $usuarioData = obtenerInfoUsuario($email, $contrasena);

    // Verificar si el usuario es válido
    if ($usuarioData) {
        session_start();
        $_SESSION['email'] = $email;
        $_SESSION['nombre'] = $usuarioData['nombre'];
        $_SESSION['clienteid'] = $usuarioData['clienteid'];
        $_SESSION['tipoUsuario'] = $usuarioData['tipo'];
        $_SESSION['logged_in'] = true;

        header("Location: menu.php");
        exit();
    } else {
        // Si el usuario no es válido, mostrar el formulario de login con errores
        $errores[] = 'Usuario o contraseña incorrectos';
        pintaLoginconParam($email, $contrasena, $errores);
    }
}
?>