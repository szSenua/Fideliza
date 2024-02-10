<?php
session_start();

$rol = isset($_SESSION['tipoUsuario']) ? $_SESSION['tipoUsuario'] : '';

if ((!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true  && $rol !== 'administrador')) {
    // Si no está logado y no es admin
    header('Location: menu.php');
    exit(); 
}

include_once 'conecta.php';

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Obtener el ID del producto a eliminar
    $fechaf_validez = $_POST['fechaf_validez'];
    $idPremio = $_POST['id'];
    $fechaActual = new DateTime();
    $fecha_proporcionada = new DateTime($fechaf_validez);
    

    if($fecha_proporcionada<$fechaActual){

    // Eliminar el producto de la base de datos
    $sql = "DELETE FROM premios WHERE premioid = :id";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':id', $idPremio, PDO::PARAM_INT);

        if ($stmt->execute()) {
            header("Location: panel_administracion.php");
        } else {
            echo '<p style="color: red;">Error al eliminar el premio.</p>';
            echo '<p style="color: red;">' . $stmt->errorInfo()[2] . '</p>';
        }
    } else {
        $error = "No se ha podido eliminar el premio seleccionado porque no ha expirado su fecha de validez";
        header("Location: panel_administracion.php?error=" . $error);
    }
}

// Cerrar la conexión
require_once 'desconecta.php';
?>