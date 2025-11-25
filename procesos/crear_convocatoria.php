<?php
// procesos/crear_convocatoria.php

session_start();

// Conexión a la BD (PDO en config/database.php)
require_once __DIR__ . '/../config/database.php';

// (Opcional) manejo de sesión / roles
if (file_exists(__DIR__ . '/../config/session.php')) {
    require_once __DIR__ . '/../config/session.php';
    if (function_exists('verificarSesion')) {
        verificarSesion(); // aquí podrías validar que sea rol "empresa" o "admin"
    }
}

// Verificar usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /views/usuario/login.php');
    exit;
}

$usuario_id = (int) $_SESSION['usuario_id'];

// Rutas físicas donde se guardarán los archivos
// Estamos en /procesos, subimos un nivel y entramos a /public/...
$docsPath = __DIR__ . '/../public/docs/';
$imgPath  = __DIR__ . '/../public/img/';

// Crear carpetas si no existen
if (!is_dir($docsPath)) {
    mkdir($docsPath, 0755, true);
}
if (!is_dir($imgPath)) {
    mkdir($imgPath, 0755, true);
}

// Solo aceptar peticiones POST desde el formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /convocatoria.php');
    exit;
}

/* =========================================================
   1) Recibir datos del formulario
   ========================================================= */

$titulo          = trim($_POST['titulo']          ?? '');
$descripcion     = trim($_POST['descripcion']     ?? '');
$categoria_id    = $_POST['categoria_id']        ?? null;
$subcategoria_id = $_POST['subcategoria_id']     ?? null;
$ubicacion       = trim($_POST['ubicacion']       ?? '');
$tipo_jornada    = trim($_POST['tipo_jornada']    ?? '');
$modalidad       = trim($_POST['modalidad']       ?? '');
$experiencia_min = $_POST['experiencia_min']     ?? null;
$salario_min     = $_POST['salario_min']         ?? null;
$salario_max     = $_POST['salario_max']         ?? null;
$beneficios      = trim($_POST['beneficios']      ?? '');
$contacto        = trim($_POST['contacto_reclutador'] ?? '');

// Validación básica
if ($titulo === '' || $descripcion === '') {
    header('Location: /convocatoria.php?error=datos_obligatorios');
    exit;
}

/* =========================================================
   2) Manejo de archivos subidos (documento + logo empresa)
   ========================================================= */

$documento_adj  = null; // nombre que irá en la columna documento_adj
$imagen_empresa = null; // nombre que irá en la columna imagen_empresa

// --- DOCUMENTO (PDF / DOC / DOCX) ---
if (!empty($_FILES['documento']['name']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
    $nombreOriginal = $_FILES['documento']['name'];
    // limpiar el nombre (sin espacios raros ni caracteres extraños)
    $nombreLimpio   = preg_replace('/[^A-Za-z0-9._-]/', '_', $nombreOriginal);
    $nombreDoc      = time() . '_' . $nombreLimpio;

    $rutaDestinoDoc = $docsPath . $nombreDoc;

    if (move_uploaded_file($_FILES['documento']['tmp_name'], $rutaDestinoDoc)) {
        // en la BD solo guardamos el nombre, NO la ruta completa
        $documento_adj = $nombreDoc;
    }
}

// --- IMAGEN (logo empresa) ---
if (!empty($_FILES['imagen_empresa']['name']) && $_FILES['imagen_empresa']['error'] === UPLOAD_ERR_OK) {
    $nombreOriginal = $_FILES['imagen_empresa']['name'];
    $nombreLimpio   = preg_replace('/[^A-Za-z0-9._-]/', '_', $nombreOriginal);
    $nombreImg      = time() . '_' . $nombreLimpio;

    $rutaDestinoImg = $imgPath . $nombreImg;

    if (move_uploaded_file($_FILES['imagen_empresa']['tmp_name'], $rutaDestinoImg)) {
        // igual: solo guardamos el nombre
        $imagen_empresa = $nombreImg;
    }
}

/* =========================================================
   3) Insertar en la tabla OFERTAS
   ========================================================= */

try {
    $sql = "
        INSERT INTO ofertas (
            usuario_id,
            categoria_id,
            subcategoria_id,
            titulo,
            descripcion,
            ubicacion,
            tipo_jornada,
            modalidad,
            experiencia_min,
            salario_min,
            salario_max,
            beneficios,
            documento_adj,
            estado,
            publicado_en,
            actualizado_en,
            contacto_reclutador,
            imagen_empresa
        )
        VALUES (
            :usuario_id,
            :categoria_id,
            :subcategoria_id,
            :titulo,
            :descripcion,
            :ubicacion,
            :tipo_jornada,
            :modalidad,
            :experiencia_min,
            :salario_min,
            :salario_max,
            :beneficios,
            :documento_adj,
            'activa',      -- estado por defecto (coincide con tus semillas)
            GETDATE(),     -- publicado_en
            GETDATE(),     -- actualizado_en
            :contacto_reclutador,
            :imagen_empresa
        )
    ";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':usuario_id'        => $usuario_id,
        ':categoria_id'      => $categoria_id ?: null,
        ':subcategoria_id'   => $subcategoria_id ?: null,
        ':titulo'            => $titulo,
        ':descripcion'       => $descripcion,
        ':ubicacion'         => $ubicacion !== '' ? $ubicacion : null,
        ':tipo_jornada'      => $tipo_jornada !== '' ? $tipo_jornada : null,
        ':modalidad'         => $modalidad !== '' ? $modalidad : null,
        ':experiencia_min'   => ($experiencia_min === '' ? null : $experiencia_min),
        ':salario_min'       => ($salario_min === '' ? null : $salario_min),
        ':salario_max'       => ($salario_max === '' ? null : $salario_max),
        ':beneficios'        => $beneficios !== '' ? $beneficios : null,
        ':documento_adj'     => $documento_adj,
        ':contacto_reclutador' => $contacto !== '' ? $contacto : null,
        ':imagen_empresa'    => $imagen_empresa  // en la vista usas fallback 'main.png' si es null
    ]);

    // Vuelve a la pantalla de convocatorias
    header('Location: /convocatoria.php?ok=1');
    exit;
} catch (PDOException $e) {
    // En producción mejor loguear el error en un archivo
    die("Error al crear convocatoria: " . $e->getMessage());
}
