<?php
// c-pu.php
session_start();
require_once '../../config/database.php'; // Ajusta según tu estructura

$usuario_id = $_SESSION['usuario_id'] ?? 1; // Fallback al admin
$error = '';
$exito = '';

// Crear carpeta uploads/publicaciones si no existe
$uploads_dir = '/uploads/publicaciones/';
$server_uploads_dir = $_SERVER['DOCUMENT_ROOT'] . $uploads_dir;
if (!is_dir($server_uploads_dir)) {
    mkdir($server_uploads_dir, 0777, true);
}

// Procesar formulario al enviar publicación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenido = trim($_POST['contenido'] ?? '');
    $imagen_nombre = null;

    // Validar contenido
    if (empty($contenido)) {
        $error = "El contenido no puede estar vacío.";
    } else {
        // Manejar imagen (opcional)
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $archivo_tmp = $_FILES['imagen']['tmp_name'];
            $archivo_nombre = basename($_FILES['imagen']['name']);
            $extension = strtolower(pathinfo($archivo_nombre, PATHINFO_EXTENSION));
            $ext_permitidas = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($extension, $ext_permitidas)) {
                $error = "Solo se permiten imágenes JPG, PNG o GIF.";
            } else {
                $imagen_nombre = $uploads_dir . time() . '_' . $archivo_nombre;
                move_uploaded_file($archivo_tmp, $_SERVER['DOCUMENT_ROOT'] . $imagen_nombre);
            }
        }

        if (!$error) {
            // Insertar publicación en la base de datos
            $stmt = $conn->prepare("
                INSERT INTO publicaciones (usuario_id, contenido, imagen, creado_en)
                VALUES (:usuario_id, :contenido, :imagen, GETDATE())
            ");
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':contenido', $contenido);
            $stmt->bindParam(':imagen', $imagen_nombre);
            $stmt->execute();

            $exito = "Publicación creada correctamente.";
            // Limpiar formulario
            $contenido = '';
            $imagen_nombre = null;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Publicación - LinkedIn EMI</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>

<?php include '../../views/cabeza/header.php'; ?>

<main class="publicar-u mar">
    <section class="crear-publicacion">
        <h1>Crear Nueva Publicación</h1>

        <?php if($error): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php elseif($exito): ?>
            <p style="color:green;"><?php echo $exito; ?></p>
        <?php endif; ?>

        <form action="c-pu.php" method="POST" enctype="multipart/form-data">
            <label for="contenido">Contenido de la publicación:</label><br>
            <textarea id="contenido" name="contenido" rows="5" cols="50" placeholder="Escribe aquí tu publicación..."><?php echo htmlspecialchars($contenido ?? ''); ?></textarea><br><br>
            
            <label for="imagen">Subir Imagen (opcional):</label><br>
            <input type="file" id="imagen" name="imagen" accept="image/*"><br><br>

            <button type="submit">Publicar</button>
            <a href="/index.php"><button type="button">Cancelar</button></a>
        </form>
    </section>
</main>

<?php include '../../views/cabeza/footer.php'; ?>

</body>
</html>
