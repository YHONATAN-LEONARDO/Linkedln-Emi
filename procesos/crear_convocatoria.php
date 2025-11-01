<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$usuario_id = $_SESSION['usuario_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /convocatoria.php");
    exit;
}

// Recibir datos
$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$categoria_id = $_POST['categoria_id'] ?? null;
$subcategoria_id = $_POST['subcategoria_id'] ?? null;
$ubicacion = $_POST['ubicacion'] ?? null;
$tipo_jornada = $_POST['tipo_jornada'] ?? null;
$modalidad = $_POST['modalidad'] ?? null;
$experiencia_min = $_POST['experiencia_min'] ?? null;
$salario_min = $_POST['salario_min'] ?? null;
$salario_max = $_POST['salario_max'] ?? null;
$beneficios = $_POST['beneficios'] ?? null;
$contacto_reclutador = $_POST['contacto_reclutador'] ?? null;

$documentoNombre = null;
$imagenEmpresa = null;

// Subir documento
if (!empty($_FILES['documento']['name']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
    $uploadsDir = __DIR__ . '/../public/docs/';
    if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

    $ext = pathinfo($_FILES['documento']['name'], PATHINFO_EXTENSION);
    $documentoNombre = 'doc_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    move_uploaded_file($_FILES['documento']['tmp_name'], $uploadsDir . $documentoNombre);
}

// Subir imagen de empresa
if (!empty($_FILES['imagen_empresa']['name']) && $_FILES['imagen_empresa']['error'] === UPLOAD_ERR_OK) {
    $uploadsDir = __DIR__ . '/../public/img/';
    if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

    $ext = pathinfo($_FILES['imagen_empresa']['name'], PATHINFO_EXTENSION);
    $imagenEmpresa = 'img_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    move_uploaded_file($_FILES['imagen_empresa']['tmp_name'], $uploadsDir . $imagenEmpresa);
}

// Insertar en la base de datos
try {
    $sql = "INSERT INTO ofertas
        (usuario_id, categoria_id, subcategoria_id, titulo, descripcion, ubicacion, tipo_jornada, modalidad,
         experiencia_min, salario_min, salario_max, beneficios, documento_adj, contacto_reclutador, imagen_empresa,
         estado, publicado_en, actualizado_en)
        VALUES
        (:usuario_id, :categoria_id, :subcategoria_id, :titulo, :descripcion, :ubicacion, :tipo_jornada, :modalidad,
         :experiencia_min, :salario_min, :salario_max, :beneficios, :documento_adj, :contacto_reclutador, :imagen_empresa,
         'en_revision', GETDATE(), GETDATE())";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':categoria_id' => $categoria_id,
        ':subcategoria_id' => $subcategoria_id,
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':ubicacion' => $ubicacion,
        ':tipo_jornada' => $tipo_jornada,
        ':modalidad' => $modalidad,
        ':experiencia_min' => $experiencia_min,
        ':salario_min' => $salario_min,
        ':salario_max' => $salario_max,
        ':beneficios' => $beneficios,
        ':documento_adj' => $documentoNombre,
        ':contacto_reclutador' => $contacto_reclutador,
        ':imagen_empresa' => $imagenEmpresa
    ]);

    header("Location: /convocatoria.php?msg=creado");
    exit;
} catch (PDOException $e) {
    die("Error al crear convocatoria: " . $e->getMessage());
}
?>
