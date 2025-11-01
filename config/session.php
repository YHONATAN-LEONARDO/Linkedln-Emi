<?php
/**
 * Manejo de sesiones seguras
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función para verificar si hay sesión activa
function verificarSesion() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: /login.php");
        exit();
    }
}

// Función para verificar rol específico
function verificarRol($rolRequerido) {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== $rolRequerido) {
        header("Location: /acceso_denegado.php");
        exit();
    }
}

// Función para cerrar sesión
function cerrarSesion() {
    session_unset();
    session_destroy();
    header("Location: /login.php");
    exit();
}
?>