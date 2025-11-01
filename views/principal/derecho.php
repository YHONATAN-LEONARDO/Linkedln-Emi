<?php
// derecho.php
session_start();
require_once 'config/database.php'; // Ajusta según tu ruta real

// Validar usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /views/usuario/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del usuario actual (quien está logueado)
$stmtUser = $conn->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmtUser->bindParam(':id', $usuario_id);
$stmtUser->execute();
$usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

// ✅ Obtener TODAS las publicaciones con los datos del usuario que las creó
$stmt = $conn->prepare("
    SELECT p.*, u.nombre, u.foto, u.educacion
    FROM publicaciones p
    INNER JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.creado_en DESC
");
$stmt->execute();
$publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-p">

    <!-- Crear nueva publicación -->
    <div class="publicar">
        <div class="publicar-inicio izi">
            <img src="<?php echo !empty($usuario['foto']) ? $usuario['foto'] : '/public/img/image.png'; ?>" alt="Foto usuario">
            <a href="/views/principal/c-pu.php">
                <button>Crear Publicación</button>
            </a>
        </div>
    </div>

    <!-- Listado de publicaciones -->
    <div class="publicaciones">
        <?php if(count($publicaciones) > 0): ?>
            <?php foreach($publicaciones as $pub): ?>
                <div class="card">
                    <div class="card-header">
                        <img src="<?php echo !empty($pub['foto']) ? $pub['foto'] : '/public/img/image.png'; ?>" alt="Foto usuario">
                        <div class="card-user-info">
                            <a href="/perfil.php">
                                <p><?php echo htmlspecialchars($pub['nombre']); ?></p>
                            </a>
                            <p><?php echo htmlspecialchars($pub['educacion']); ?></p>
                            <p><?php echo date('d/m/Y H:i', strtotime($pub['creado_en'])); ?></p>
                        </div>
                    </div>

                    <div class="card-body">
                        <p><?php echo nl2br(htmlspecialchars($pub['contenido'])); ?></p>

                        <?php if(!empty($pub['imagen'])): ?>
                        <img class="img-card" src="<?php echo "public/img/" . $pub['imagen']; ?>" alt="Imagen publicación">


                        <?php endif; ?>
                    </div>

                    <div class="ultimo-card">
                        <div class="ul-f">
                            <p>21 <ion-icon name="heart-circle-outline"></ion-icon></p>
                            <p>2 Comentarios</p>
                        </div>

                        <div class="ul-f">
                            <button><ion-icon name="heart-outline"></ion-icon></button>
                            <button>Comentar <ion-icon name="chatbox-ellipses-outline"></ion-icon></button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay publicaciones aún.</p>
        <?php endif; ?>
    </div>

</div>

<!-- Ionicons -->
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
