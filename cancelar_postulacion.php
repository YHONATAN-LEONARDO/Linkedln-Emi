<?php
// cancelar_postulacion.php
session_start();
require_once 'config/database.php'; // Ajusta la ruta si es necesario

// Validar usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /views/usuario/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Verificar que se reciba el ID de la postulacion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['postulacion_id'])) {
    $postulacion_id = intval($_POST['postulacion_id']);

    // Verificar que la postulacion pertenezca al usuario
    $stmt = $conn->prepare("SELECT * FROM postulaciones WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->execute([
        ':id' => $postulacion_id,
        ':usuario_id' => $usuario_id
    ]);

    if ($stmt->rowCount() > 0) {
        // Borrar la postulacion
        $delete = $conn->prepare("DELETE FROM postulaciones WHERE id = :id");
        $delete->execute([':id' => $postulacion_id]);

        $_SESSION['mensaje'] = "Postulación cancelada correctamente.";
    } else {
        $_SESSION['mensaje'] = "No se pudo cancelar la postulación.";
    }
}

// Redirigir de vuelta a la página de postulaciones
header('Location: postulaciones.php');
exit;
