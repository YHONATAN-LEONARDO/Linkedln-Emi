<?php
// editar.php
session_start();
require_once 'config/database.php';

$usuario_id = $_SESSION['usuario_id'] ?? 1; // fallback

// Crear carpetas si no existen
if (!file_exists('uploads/fotos')) mkdir('uploads/fotos', 0777, true);
if (!file_exists('uploads/cv')) mkdir('uploads/cv', 0777, true);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $educacion = $_POST['educacion'] ?? '';
    $ubicacion = $_POST['ubicacion'] ?? '';
    $correo = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $fecha_nacimiento = $_POST['nacimiento'] ?? '';

    // Manejo de foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $foto_nombre = 'uploads/fotos/' . time() . '_' . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], $foto_nombre);
    } else {
        $foto_nombre = null;
    }

    // Manejo de CV
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $cv_nombre = 'uploads/cv/' . time() . '_' . basename($_FILES['cv']['name']);
        move_uploaded_file($_FILES['cv']['tmp_name'], $cv_nombre);
    } else {
        $cv_nombre = null;
    }

    // Construir query dinámico según archivos subidos
    $query = "UPDATE usuarios SET nombre = :nombre, educacion = :educacion, ubicacion = :ubicacion, correo = :correo, telefono = :telefono, fecha_nacimiento = :fecha_nacimiento";

    if ($foto_nombre) $query .= ", foto = :foto";
    if ($cv_nombre) $query .= ", cv = :cv";

    $query .= ", actualizado_en = GETDATE() WHERE id = :id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':educacion', $educacion);
    $stmt->bindParam(':ubicacion', $ubicacion);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
    $stmt->bindParam(':id', $usuario_id);

    if ($foto_nombre) $stmt->bindParam(':foto', $foto_nombre);
    if ($cv_nombre) $stmt->bindParam(':cv', $cv_nombre);

    $stmt->execute();

    header('Location: perfil.php'); // Redirige al perfil
    exit;
}

// Obtener datos actuales del usuario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->bindParam(':id', $usuario_id);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Perfil - LinkedIn Emi</title>
<link rel="stylesheet" href="/public/css/normalize.css">
<link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>

<?php include 'views/cabeza/header.php'; ?>

<section class="editar-perfil-sec">
    <h1 class="editar-perfil-titulo">Editar Información Personal</h1>

    <form action="editar.php" method="post" enctype="multipart/form-data" class="editar-perfil-form">
        <label for="nombre">Nombre completo:</label><br>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>"><br><br>

        <label for="educacion">Educación:</label><br>
        <input type="text" id="educacion" name="educacion" value="<?php echo htmlspecialchars($usuario['educacion']); ?>"><br><br>

        <label for="ubicacion">Ubicación:</label><br>
        <input type="text" id="ubicacion" name="ubicacion" value="<?php echo htmlspecialchars($usuario['ubicacion']); ?>"><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['correo']); ?>"><br><br>

        <label for="telefono">Teléfono:</label><br>
        <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>"><br><br>

        <label for="nacimiento">Fecha de nacimiento:</label><br>
        <input type="date" id="nacimiento" name="nacimiento" value="<?php echo $usuario['fecha_nacimiento']; ?>"><br><br>

        <label for="foto">Cambiar foto de perfil:</label><br>
        <input type="file" id="foto" name="foto"><br>
        <?php if(!empty($usuario['foto'])): ?>
            <small>Foto actual: <img src="<?php echo $usuario['foto']; ?>" alt="Foto perfil" style="height:50px;"></small><br>
        <?php endif; ?>
        <br>

        <label for="cv">Subir CV:</label><br>
        <input type="file" id="cv" name="cv"><br>
        <?php if(!empty($usuario['cv'])): ?>
            <small>CV actual: <a href="<?php echo $usuario['cv']; ?>" target="_blank">Ver CV</a></small><br>
        <?php endif; ?>
        <br>

        <button type="submit" class="btn-guardar">Guardar cambios</button>
        <a href="perfil.php" class="btn-volver">Volver</a>
    </form>
</section>

<?php include 'views/cabeza/footer.php'; ?>

</body>
</html>
