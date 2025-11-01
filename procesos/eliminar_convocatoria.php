<?php
// eliminar_convocatoria.php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /convocatoria.php");
    exit;
}
$id = intval($_POST['convocatoria_id'] ?? 0);

try {
    // opcional: eliminar documento físico si existe (consulta previa)
    $q = $conn->prepare("SELECT documento_adj FROM ofertas WHERE id=:id");
    $q->execute([':id'=>$id]);
    $row = $q->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['documento_adj'])) {
        $path = __DIR__ . '/../public/docs/' . $row['documento_adj'];
        if (file_exists($path)) unlink($path);
    }
    // eliminar oferta
    $stmt = $conn->prepare("DELETE FROM ofertas WHERE id=:id");
    $stmt->execute([':id'=>$id]);

    header("Location: /convocatoria.php?msg=eliminado");
    exit;
} catch (PDOException $e) {
    die("Error al eliminar convocatoria: " . $e->getMessage());
}
?>
