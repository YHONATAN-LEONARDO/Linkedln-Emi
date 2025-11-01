<?php
// perfil.php
session_start();
require_once 'config/database.php'; // Debe definir $conn como PDO

// Suponemos que el usuario está logueado y su id está en la sesión
$usuario_id = $_SESSION['usuario_id'] ?? 1; // Fallback al admin

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener publicaciones del usuario
$stmt2 = $conn->prepare("SELECT * FROM publicaciones WHERE usuario_id = :id ORDER BY creado_en DESC");
$stmt2->bindParam(':id', $usuario_id, PDO::PARAM_INT);
$stmt2->execute();
$publicaciones = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil LinkedIn Emi</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
    <style>
        .cv-frame {
            width: 100%;
            height: 500px;
            border: 1px solid #ccc;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <?php include 'views/cabeza/header.php'; ?>

    <section class="perfil-principal op">
        <div class="perfil-header">
            <img src="<?php echo $usuario['foto'] ?? 'public/img/image.png'; ?>" alt="Foto de perfil" class="perfil-img">
            <h1><?php echo htmlspecialchars($usuario['nombre']); ?></h1>
            <p><strong>Educación:</strong> <?php echo htmlspecialchars($usuario['educacion']); ?></p>
            <p><strong>Ubicación:</strong> <?php echo htmlspecialchars($usuario['ubicacion']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['correo']); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($usuario['telefono']); ?></p>
            <p><strong>Fecha de nacimiento:</strong> <?php echo htmlspecialchars($usuario['fecha_nacimiento']); ?></p>

            <?php if(!empty($usuario['cv'])): ?>
                <h3>Mi CV:</h3>
                <iframe src="<?php echo $usuario['cv']; ?>" class="cv-frame"></iframe>
                <p><a href="<?php echo $usuario['cv']; ?>" target="_blank">Abrir CV en otra ventana</a></p>
            <?php else: ?>
                <p>No has subido un CV aún.</p>
            <?php endif; ?>
        </div>

        <div class="acciones-perfil">
            <a href="editar.php"><button>Editar información</button></a>
            <a href="/"><button>Ver publicaciones</button></a>
            <a href="postulacion.php"><button>Postulaciones</button></a>
        </div>
    </section>

    <section class="actividad op">
        <h2>Actividad reciente</h2>
        <?php if (count($publicaciones) > 0): ?>
            <?php foreach ($publicaciones as $pub): ?>
                <div class="publicacion">
                    <p><?php echo htmlspecialchars($pub['contenido']); ?></p>
                    <?php if (!empty($pub['imagen'])): ?>
                        <img src="<?php echo $pub['imagen']; ?>" alt="Imagen publicación" class="img-publicacion">
                    <?php endif; ?>
                    <small>Publicado: <?php echo date('d/m/Y H:i', strtotime($pub['creado_en'])); ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay publicaciones aún.</p>
        <?php endif; ?>
    </section>

    <?php include 'views/cabeza/footer.php'; ?>

</body>
</html>
