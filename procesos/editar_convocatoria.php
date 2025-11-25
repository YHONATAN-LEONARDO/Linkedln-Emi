<?php
// /procesos/editar_convocatoria.php
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
$titulo          = trim($_POST['titulo'] ?? '');
$descripcion     = trim($_POST['descripcion'] ?? '');

if ($convocatoria_id <= 0 || $titulo === '' || $descripcion === '') {
    header('Location: /convocatoria.php');
    exit;
}

// Rutas docs
$docsPath = __DIR__ . '/../public/docs/';
if (!is_dir($docsPath)) {
    mkdir($docsPath, 0755, true);
}

// Traer convocatoria (para validar que existe y obtener documento actual)
$stmt = $conn->prepare("
    SELECT id, usuario_id, documento_adj
    FROM ofertas
    WHERE id = :id
");
$stmt->execute([':id' => $convocatoria_id]);
$oferta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$oferta) {
    header('Location: /convocatoria.php');
    exit;
}

// (Opcional) Validar que quien edita sea el creador o admin
// if ($oferta['usuario_id'] != $usuario_id) {
//     header('Location: /convocatoria.php');
//     exit;
// }

$nuevoDocumento = $oferta['documento_adj'];

// ¿Nuevo archivo?
if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['documento']['name'], PATHINFO_EXTENSION);
    $safeName = 'conv_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
    $destino = $docsPath . $safeName;

    if (move_uploaded_file($_FILES['documento']['tmp_name'], $destino)) {
        $nuevoDocumento = $safeName;

        // Opcional: borrar antiguo
        if (!empty($oferta['documento_adj'])) {
            $old = $docsPath . $oferta['documento_adj'];
            if (is_file($old)) {
                @unlink($old);
            }
        }
    }
}

try {
    $sql = "
        UPDATE ofertas
        SET titulo = :titulo,
            descripcion = :descripcion,
            documento_adj = :documento_adj,
            actualizado_en = GETDATE()
        WHERE id = :id
    ";

    $stmtUp = $conn->prepare($sql);
    $stmtUp->execute([
        ':titulo'        => $titulo,
        ':descripcion'   => $descripcion,
        ':documento_adj' => $nuevoDocumento,
        ':id'            => $convocatoria_id
    ]);

    header('Location: /convocatoria.php');
    exit;

} catch (PDOException $e) {
    echo "Error al editar convocatoria: " . $e->getMessage();
    exit;
}
