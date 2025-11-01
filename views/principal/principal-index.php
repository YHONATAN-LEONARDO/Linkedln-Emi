<?php
// principal-index.php
session_start();
require_once 'config/database.php';

// Suponemos que el usuario está logueado
$usuario_id = $_SESSION['usuario_id'] ?? 1;

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->bindParam(':id', $usuario_id);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener publicaciones del usuario
$stmt2 = $conn->prepare("SELECT * FROM publicaciones WHERE usuario_id = :id ORDER BY creado_en DESC");
$stmt2->bindParam(':id', $usuario_id);
$stmt2->execute();
$publicaciones = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="principal">
    <section class="lado-izquierdo">
        <a href="/perfil.php">
            <div class="usuario-izquierdo izi">
                <div class="primero-izquierdo">

                    <div class="usuario-izquierdo-img">
                        <img class="img-cl" src="/public/img/image.png" alt="fondo de perfil">
                    </div>
                    <div class="usuario-izquierdo-datos">
                        <img class="img-usuario im" src="<?php echo $usuario['foto'] ?? '/public/img/image.png'; ?>" alt="foto del usuario">
                        <p><?php echo htmlspecialchars($usuario['nombre']); ?></p>
                        <p><?php echo htmlspecialchars($usuario['educacion']); ?></p>
                        <p class="bb"><?php echo htmlspecialchars($usuario['ubicacion']); ?></p>
                        <div class="inf1">
                            <img class="ulo" src="/public/img/fondo-usuario.png" alt="">
                            <p><?php echo htmlspecialchars($usuario['educacion']); ?></p>
                        </div>
                    </div>

                </div>
            </div>
        </a>

        <a class="izi ip" href="">
            <div>
                <p>Contacto</p>
                <p>Amplía tus amigos de la Emi</p>
            </div>
            <ion-icon name="person-add-outline"></ion-icon>
        </a>

        <p class="izi po">Conectando el talento y la innovación de la Escuela Militar de Ingeniería con el mundo profesional.</p>
    </section>

    <section class="lado-derecho">
        <?php include 'derecho.php'; ?>
    </section>
</main>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
