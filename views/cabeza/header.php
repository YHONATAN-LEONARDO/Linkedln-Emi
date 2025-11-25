
<?php
// Asumiendo que aquí YA tienes session_start() y $conn disponible
// (porque tus páginas ya incluyen config/database.php y session.php antes del header)

$usuarioIdHeader = $_SESSION['usuario_id'] ?? null;
$totalAlertas    = 0;

if ($usuarioIdHeader && isset($conn)) {
    try {
        // 1) Notificaciones no leídas
        $stmtNotif = $conn->prepare("
            SELECT COUNT(*) 
            FROM notificaciones
            WHERE usuario_id = :uid
              AND leido = 0
        ");
        $stmtNotif->execute([':uid' => $usuarioIdHeader]);
        $noLeidas = (int)$stmtNotif->fetchColumn();

        // 2) Solicitudes de amistad pendientes (que OTROS te enviaron)
        $stmtSol = $conn->prepare("
            SELECT COUNT(*)
            FROM solicitudes_amistad
            WHERE destinatario_id = :uid
              AND estado = 'pendiente'
        ");
        $stmtSol->execute([':uid' => $usuarioIdHeader]);
        $pendientes = (int)$stmtSol->fetchColumn();

        // Total de alertas
        $totalAlertas = $noLeidas + $pendientes;
    } catch (PDOException $e) {
        // Si falla algo, no revienta el header, solo no muestra bolita
        $totalAlertas = 0;
    }
}
?>

<?php
session_start();
$ruta = strtok($_SERVER['REQUEST_URI'], '?');

// Marcar ruta activa
function activo($path)
{
    global $ruta;
    return $ruta === $path ? 'activo' : '';
}
?>

<!-- HEADER SOLO EN PORTADA -->
<?php if ($ruta === '/' || $ruta === '/index.php'): ?>
    <header class="header-portada">

        <!-- IMAGEN PRINCIPAL DEL SLIDER -->
        <img id="sliderImagen" src="/public/img/ualp01.jpg" alt="Fondo portada">

        <!-- CONTENEDOR DE FRASES -->
        <div class="frases-slider" id="frasesSlider">
            <p id="fraseTexto" class="frase-texto"></p>
        </div>

    </header>
<?php endif; ?>


<!-- NAV -->
<nav class="nav-principal">

    <!-- LOGO -->
    <div class="nav-logo">
        <a href="/index.php">
            <img src="/public/img/main.png" alt="LinkedIn EMI">
        </a>
    </div>

    <!-- MENÚ HAMBURGUESA -->
    <div class="nav-hamburguesa" id="btnHamburguesa">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <!-- MENÚ COMPLETO -->
    <div class="nav-menu" id="menuPrincipal">

        <a href="/index.php" class="nav-item <?= activo('/') . activo('/index.php') ?>">
            <ion-icon name="home-outline"></ion-icon>
            <span>Inicio</span>
        </a>
        <a href="/red.php" class="nav-item <?= activo('/red.php') ?>">
            <ion-icon name="people-outline"></ion-icon>
            <span>Red de contactos</span>
        </a>

        <a href="/empleo.php" class="nav-item <?= activo('/empleo.php') ?>">
            <ion-icon name="newspaper-outline"></ion-icon>
            <span>Empleo</span>
        </a>

        <a href="/notificacion.php" class="nav-item <?= activo('/notificacion.php') ?>">
            <span class="nav-icon-wrap">
                <ion-icon name="notifications-outline"></ion-icon>

                <?php if (!empty($totalAlertas) && $totalAlertas > 0): ?>
                    <span class="badge-alerta">
                        <?= $totalAlertas > 9 ? '9+' : (int)$totalAlertas; ?>
                    </span>
                <?php endif; ?>
            </span>
            <span>Notificaciones</span>
        </a>



        <?php if (isset($_SESSION['usuario_id'])): ?>

            <a href="/perfil.php" class="nav-item <?= activo('/perfil.php') ?>">
                <ion-icon name="person-circle-outline"></ion-icon>
                <span>Mi Perfil</span>
            </a>

            <a href="/views/usuario/cerrar.php" class="nav-item salir">
                <ion-icon name="exit-outline"></ion-icon>
                <span>Cerrar sesión</span>
            </a>

        <?php else: ?>

            <a href="/views/usuario/login.php" class="nav-item">
                <ion-icon name="log-in-outline"></ion-icon>
                <span>Iniciar sesión</span>
            </a>

            <a href="/views/usuario/registro.php" class="nav-item">
                <ion-icon name="person-add-outline"></ion-icon>
                <span>Registrarse</span>
            </a>

        <?php endif; ?>
    </div>
</nav>


<!-- ICONOS -->
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>


<!-- =============== JS SLIDER + HAMBURGUESA =============== -->
<script>
    // 3 IMÁGENES (LAS QUE PEDISTE)
    const imagenes = [
        "/public/img/ualp01.jpg",
        "/public/img/ualp02.jpg",
        "/public/img/portada-derecho.jpg"
    ];

    // 10 FRASES INSPIRADORAS
    const frases = [
        "Hoy construyes tu futuro.",
        "Sigue avanzando, paso a paso.",
        "La EMI forma profesionales del mañana.",
        "Cada día es una oportunidad nueva.",
        "Comparte tu progreso con el mundo.",
        "Un perfil fuerte abre puertas.",
        "Tu camino profesional empieza aquí.",
        "No te detengas, estás creciendo.",
        "Esfuérzate hoy, destaca mañana.",
        "Haz que tu talento brille."
    ];

    // ELEMENTOS
    const sliderImagen = document.getElementById("sliderImagen");
    const fraseTexto = document.getElementById("fraseTexto");

    let index = 0;

    // Cambiar imagen + frase
    function cambiarSlider() {
        if (!sliderImagen || !fraseTexto) return;

        sliderImagen.style.opacity = 0;
        fraseTexto.style.opacity = 0;

        setTimeout(() => {
            sliderImagen.src = imagenes[index];
            fraseTexto.textContent = frases[index];

            sliderImagen.style.opacity = 1;
            fraseTexto.style.opacity = 1;

            index = (index + 1) % imagenes.length;
        }, 300);
    }

    // Cambia cada 5 segundos
    setInterval(cambiarSlider, 5000);

    // Primera carga
    cambiarSlider();


    // ===========================
    // MENÚ HAMBURGUESA MÓVIL
    // ===========================
    const btnHamburguesa = document.getElementById("btnHamburguesa");
    const menuPrincipal = document.getElementById("menuPrincipal");

    btnHamburguesa.addEventListener("click", () => {
        btnHamburguesa.classList.toggle("activo");
        menuPrincipal.classList.toggle("menu-activo");
    });
</script>

<style>
    /* Contenedor del ícono de notificación */
    .nav-icon-wrap {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Bolita roja de alerta */
    .badge-alerta {
        position: absolute;
        top: -4px;
        right: -6px;
        min-width: 16px;
        height: 16px;
        padding: 0 4px;
        background: #ef4444;
        /* rojo */
        color: #ffffff;
        border-radius: 999px;
        font-size: 10px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        box-shadow: 0 0 0 2px #f3f4f6;
        /* pequeño borde para que resalte sobre el fondo */
    }
</style>

