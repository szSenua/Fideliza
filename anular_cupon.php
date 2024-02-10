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
    $fechai_validez = $_POST['fechai_validez'];
    $idPremio = $_POST['id'];
    $idcliente = $_POST['clienteid'];
    

    // Actualizar los cupones y poner la fecha a null
    $sql = "UPDATE cupones
            SET fechai_validez = null, fechaf_validez = null
            WHERE premioid = :id
            AND clienteid = :clienteid";

    $stmt = $con->prepare($sql);
    $stmt->bindParam(':id', $idPremio, PDO::PARAM_INT);
    $stmt->bindParam(':clienteid', $idcliente, PDO::PARAM_INT);

        if ($stmt->execute()) {
            header("Location: panel_administracion.php");
        } else {
            echo '<p style="color: red;">Error al actualizar el cupón.</p>';
            echo '<p style="color: red;">' . $stmt->errorInfo()[2] . '</p>';
        }
    } else {
        
        header("Location: panel_administracion.php");
    }

// Cerrar la conexión
require_once 'desconecta.php';
?>