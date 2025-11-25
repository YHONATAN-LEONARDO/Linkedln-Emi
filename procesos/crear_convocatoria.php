<?php
// /procesos/cerrar_convocatoria.php
session_start();

require_once __DIR__ . '/../config/database.php';
if (file_exists(__DIR__ . '/../config/session.php')) {
    require_once __DIR__ . '/../config/session.php';
}

if (function_exists('verificarSesion')) {
    verificarSesion();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /views/usuario/login.php');
    exit;
}

$usuario_id = (int)$_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /convocatoria.php');
    exit;
}

$convocatoria_id = isset($_POST['convocatoria_id']) ? (int)$_POST['convocatoria_id'] : 0;

if ($convocatoria_id <= 0) {
    header('Location: /convocatoria.php');
    exit;
}

try {
    // (Opcional) filtrar también por usuario_id para que solo el dueño la cierre
    $sql = "
        UPDATE ofertas
        SET estado = 'cerrada',
            actualizado_en = GETDATE()
        WHERE id = :id
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $convocatoria_id]);

    header('Location: /convocatoria.php');
    exit;
} catch (PDOException $e) {
    echo "Error al cerrar convocatoria: " . $e->getMessage();
    exit;
}
