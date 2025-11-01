<?php
session_start(); // Iniciar sesión para poder acceder a $_SESSION
// Obtiene la ruta actual
$ruta = $_SERVER['REQUEST_URI'];
?>

<?php if ($ruta === '/' || $ruta === '/index.php'): ?>
<header>
    <img src="/public/img/fondo.jpg" alt="">
</header>
<?php endif; ?>

<nav>
    <div class="nav-1 navi">
        <a href="/index.php">
            <img class="nav-1-img" src="/public/img/main.png" alt="">
        </a>
        <!-- Buscador opcional -->
        <!--
        <div class="busqueda">
            <input class="nav-1-input" type="text">
            <ion-icon name="search-circle-outline"></ion-icon>
        </div>
        -->
    </div>

    <div class="nav-2 navi">
        <div class="contenedor-icono">
            <a href="/" class="contenedor-icono">
                <ion-icon name="home-outline"></ion-icon>
                Inicio
            </a>
        </div>
        <div class="contenedor-icono">
            <a href="empleo.php" class="contenedor-icono">
                <ion-icon name="newspaper-outline"></ion-icon>
                Empleo
            </a>
        </div>
        <div class="contenedor-icono">
            <a href="notificacion.php" class="contenedor-icono">
                <ion-icon name="notifications-outline"></ion-icon>
                Notificaciones
            </a>
        </div>
    </div>

    <div class="nav-3 navi">
        <?php if(isset($_SESSION['usuario_id'])): ?>
            <a href="/views/usuario/cerrar.php">
                <div class="contenedor-icono">
                    <ion-icon name="exit-outline"></ion-icon>
                    Cerrar Sesión
                </div>
            </a>
        <?php else: ?>
            <a href="/views/usuario/login.php">
                <div class="contenedor-icono">
                    <ion-icon name="log-in-outline"></ion-icon>
                    Iniciar sesión
                </div>
            </a>
            <a href="/views/usuario/registro.php">
                <div class="contenedor-icono">
                    <ion-icon name="person-add-outline"></ion-icon>
                    Registrarse
                </div>
            </a>
        <?php endif; ?>
    </div>
</nav>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
