<?php
require_once __DIR__ . '/../../config/database.php';  // Conexión PDO
require_once __DIR__ . '/../../config/funcion_db.php'; // Funciones CRUD
require_once __DIR__ . '/../../config/session.php';    // Manejo de sesiones

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];

    if ($correo && $password) {

        // ATAJO ADMIN
        if ($correo === 'emi@emi.edu.bo' && $password === 'emi') {
            $_SESSION['usuario_id'] = 1;
            $_SESSION['rol'] = 1;
            $_SESSION['nombre'] = 'Admin';
            header('Location: /admin.php');
            exit();
        }

        // Login normal
        $usuario = validarLogin($correo, $password);

        if ($usuario) {
            // Crear sesión segura
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['rol'] = $usuario['rol_id'];
            $_SESSION['nombre'] = $usuario['nombre'];

            // Registrar actividad
            registrarActividad($usuario['id'], 'login', 'Inicio de sesión', obtenerIpCliente());

            // Redirigir según rol
            switch ($usuario['rol_id']) {
                case 1: // admin
                    header('Location: /admin.php');
                    break;
                case 2: // empresa
                    header('Location: /convocatoria.php');
                    break;
                case 3: // postulante
                    header('Location: /index.php');
                    break;
                default:
                    header('Location: /index.php');
            }
            exit();
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }

    } else {
        $error = 'Todos los campos son obligatorios.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión</title>
  <link rel="stylesheet" href="/public/css/normalize.css">
  <link rel="stylesheet" href="/public/css/styles.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="p-usu">
  <form class="form-login" action="" method="POST">
    <h2>Iniciar Sesión</h2>

    <?php if ($error): ?>
      <p class="msg-error"><?php echo $error; ?></p>
    <?php endif; ?>

    <label for="correo">Correo institucional</label>
    <input type="email" id="correo" name="correo" placeholder="usuario@emi.edu.bo" required>

    <label for="password">Contraseña</label>
    <input type="password" id="password" name="password" placeholder="********" required>

    <button type="submit" class="btn-azul">Ingresar</button>
    <button type="button" class="btn-volver" onclick="window.history.back()">Volver</button>

    <p class="msg-registro">¿No tienes cuenta? <a href="/views/usuario/registro.php">Regístrate aquí</a></p>
  </form>
</div>

</body>
</html>
