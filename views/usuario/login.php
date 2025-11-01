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
    <form class="form-login" action="/login" method="POST">
      <h2>Iniciar Sesión</h2>

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
