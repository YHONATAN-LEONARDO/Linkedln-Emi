<?php
require_once __DIR__ . '/../../config/database.php';  // PDO connection
require_once __DIR__ . '/../../config/funcion_db.php'; // Funciones CRUD

$mensaje = '';
$error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    $rol = $_POST['rol'];

    if ($nombre && $correo && $password && $rol) {
        // Validar que sea correo institucional @emi.edu.bo
        if (!str_ends_with($correo, '@emi.edu.bo')) {
            $error = "Debes usar un correo institucional @emi.edu.bo";
        } else {
            // Verificar si el correo ya existe
            $usuarioExistente = obtenerUsuarioPorCorreo($correo);

            if ($usuarioExistente) {
                $error = "El correo ya está registrado.";
            } else {
                // Mapear rol a id
                switch ($rol) {
                    case 'postulante':
                        $rol_id = 3;
                        break;
                    case 'empresa':
                        $rol_id = 2;
                        break;
                    default:
                        $rol_id = 3;
                }

                $registrado = registrarUsuario($nombre, $correo, $password, $rol_id);

                if ($registrado) {
                    // Redirigir a login
                    header('Location: /views/usuario/login.php');
                    exit();
                } else {
                    $error = "Ocurrió un error al registrar el usuario.";
                }
            }
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Usuario</title>
  <link rel="stylesheet" href="/public/css/normalize.css">
  <link rel="stylesheet" href="/public/css/styles.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="p-usu">
    <form class="form-registro" action="" method="POST">
      <h2>Registrar Usuario</h2>

      <?php if ($mensaje): ?>
        <p class="msg-exito"><?php echo $mensaje; ?></p>
      <?php endif; ?>
      <?php if ($error): ?>
        <p class="msg-error"><?php echo $error; ?></p>
      <?php endif; ?>

      <label for="nombre">Nombre completo</label>
      <input type="text" id="nombre" name="nombre" placeholder="Ej. Juan Pérez" required>

      <label for="correo">Correo institucional</label>
      <input type="email" id="correo" name="correo" placeholder="usuario@emi.edu.bo" required>

      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" placeholder="********" required>

      <label for="rol">Rol</label>
      <select id="rol" name="rol" required>
        <option value="">Seleccione un rol</option>
        <option value="postulante">Postulante</option>
        <option value="empresa">Reclutador</option>
      </select>

      <button type="submit" class="btn-azul">Registrar</button>
      <button type="button" class="btn-volver" onclick="window.history.back()">Volver</button>

      <p class="msg-login">¿Ya tienes una cuenta? <a href="/views/usuario/login.php">Inicia sesión</a></p>
    </form>
  </div>
</body>
</html>
