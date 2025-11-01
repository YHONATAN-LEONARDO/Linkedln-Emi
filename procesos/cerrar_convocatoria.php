<?php
// cerrar_convocatoria.php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /convocatoria.php");
    exit;
}
$id = intval($_POST['convocatoria_id'] ?? 0);
try {
    $stmt = $conn->prepare("UPDATE ofertas SET estado='cerrada', actualizado_en=GETDATE() WHERE id=:id");
    $stmt->execute([':id'=>$id]);
    header("Location: /convocatoria.php?msg=cerrada");
    exit;
} catch (PDOException $e) {
    die("Error al cerrar convocatoria: " . $e->getMessage());
}
?>
