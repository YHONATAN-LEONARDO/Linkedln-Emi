<?php
// editar_convocatoria.php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /convocatoria.php");
    exit;
}

$id = intval($_POST['convocatoria_id'] ?? 0);
$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

// manejar archivo nuevo si vino
$documentoNombre = null;
if (!empty($_FILES['documento']['name']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
    $uploadsDir = __DIR__ . '/../public/docs/';
    if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
    $ext = pathinfo($_FILES['documento']['name'], PATHINFO_EXTENSION);
    $documentoNombre = 'doc_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dest = $uploadsDir . $documentoNombre;
    if (!move_uploaded_file($_FILES['documento']['tmp_name'], $dest)) {
        die("Error al mover el archivo subido.");
    }
}

try {
    if ($documentoNombre) {
        $sql = "UPDATE ofertas SET titulo=:titulo, descripcion=:descripcion, documento_adj=:documento_adj, actualizado_en=GETDATE() WHERE id=:id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':titulo'=>$titulo, ':descripcion'=>$descripcion, ':documento_adj'=>$documentoNombre, ':id'=>$id]);
    } else {
        $sql = "UPDATE ofertas SET titulo=:titulo, descripcion=:descripcion, actualizado_en=GETDATE() WHERE id=:id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':titulo'=>$titulo, ':descripcion'=>$descripcion, ':id'=>$id]);
    }
    header("Location: /convocatoria.php?msg=editado");
} catch (PDOException $e) {
    die("Error al editar convocatoria: " . $e->getMessage());
}
?>
