<?php
// evaluar_postulantes.php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /convocatoria.php");
    exit;
}

$oferta_id = intval($_POST['oferta_id'] ?? 0);
$calificaciones = $_POST['calificacion'] ?? []; // array [postulacion_id => calificacion]

try {
    foreach ($calificaciones as $post_id => $cal) {
        $stmt = $conn->prepare("UPDATE postulaciones SET calificacion=:calificacion WHERE id=:id");
        $stmt->execute([':calificacion'=>intval($cal), ':id'=>intval($post_id)]);
    }
    header("Location: /convocatoria.php?msg=calificaciones_guardadas");
    exit;
} catch (PDOException $e) {
    die("Error al guardar calificaciones: " . $e->getMessage());
}
?>
