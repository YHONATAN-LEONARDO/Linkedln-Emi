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
    <form class="form-registro" action="/registro" method="POST">
      <h2>Registrar Usuario</h2>

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
        <!-- admin mas pero eso no en la base de datos estara el admin por que no seria seguro  que se registren como admin  -->
      </select>

      <button type="submit" class="btn-azul">Registrar</button>
      <button type="button" class="btn-volver" onclick="window.history.back()">Volver</button>

      <p class="msg-login">¿Ya tienes una cuenta? <a href="/views/usuario/login.php">Inicia sesión</a></p>
    </form>
  </div>

</body>
</html>
