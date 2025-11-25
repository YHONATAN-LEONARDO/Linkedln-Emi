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
  <title>Registrarte en LinkedIn EMI</title>

  <link rel="stylesheet" href="/public/css/normalize.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --azul-principal: #0066cc;
      --azul-oscuro: #004c99;
      --azul-suave: #4791ff;
      --fondo-base: #f4f2ee;
      --rojo-error: #dc2626;
      --verde-ok: #16a34a;
    }

    * {
      box-sizing: border-box;
    }

    html {
      font-size: 62.5%;
    }

    body {
      font-family: "Montserrat", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;

      background: #f3f3f4 url("/public/img/page.png") no-repeat top center fixed;

      /* ---- MENOS ZOOM ---- */
      background-size: 80%;
      /* <--- AJUSTA AQUÍ */
      background-position: center 40px;
      /* baja la imagen un poco */

      color: #111827;
      min-height: 100vh;
      margin: 0;

      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }


    a {
      text-decoration: none;
      color: inherit;
    }

    /* CONTENEDOR PRINCIPAL (CARD) */
    .registro-wrapper {
      width: 100%;
      max-width: 96rem;
      background: rgba(255, 255, 255, 0.96);
      border-radius: 1.8rem;
      box-shadow: 0 1.2rem 3rem rgba(0, 0, 0, 0.18);
      display: grid;
      grid-template-columns: 1.2fr 1fr;
      overflow: hidden;
      backdrop-filter: blur(6px);
    }

    /* LADO IZQUIERDO: MENSAJE MOTIVADOR */
    .registro-info {
      padding: 3rem 3.5rem;
      background: linear-gradient(135deg, #ffffff 0%, #e3f2fd 30%, #f4f2ee 100%);
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 1.8rem;
    }

    .registro-logo {
      display: flex;
      align-items: center;
      gap: 1.2rem;
      margin-bottom: 0.5rem;
    }

    .registro-logo img {
      width: 4.2rem;
      height: auto;
      object-fit: contain;
    }

    .registro-logo span {
      font-size: 1.6rem;
      font-weight: 700;
      color: var(--azul-principal);
    }

    .registro-kicker {
      font-size: 1.3rem;
      font-weight: 600;
      color: var(--azul-oscuro);
      text-transform: uppercase;
      letter-spacing: 0.12em;
    }

    .registro-info h1 {
      font-size: 2.6rem;
      line-height: 1.25;
      color: #111827;
      margin: 0;
    }

    .registro-info h1 span {
      color: var(--azul-principal);
    }

    .registro-info p {
      font-size: 1.45rem;
      line-height: 1.6;
      color: #374151;
      margin: 0;
    }

    .registro-highlights {
      margin-top: 1.4rem;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
      font-size: 1.3rem;
    }

    .registro-pill {
      background-color: #ffffff;
      border-radius: 999px;
      padding: 0.8rem 1.2rem;
      border: 0.1rem solid #e5e7eb;
      display: flex;
      align-items: center;
      gap: 0.6rem;
      box-shadow: 0 0.4rem 1rem rgba(0, 0, 0, 0.04);
      font-size: 1.25rem;
      color: #111827;
    }

    .registro-pill-dot {
      width: 0.9rem;
      height: 0.9rem;
      border-radius: 50%;
      background-color: var(--azul-principal);
    }

    .registro-footer-text {
      margin-top: 1.6rem;
      font-size: 1.25rem;
      color: #6b7280;
    }

    /* FORMULARIO (LADO DERECHO) */
    .registro-form-container {
      padding: 3rem 3.5rem;
      background-color: rgba(255, 255, 255, 0.98);
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .registro-form-container h2 {
      font-size: 2rem;
      margin-bottom: 0.5rem;
      color: #111827;
    }

    .registro-subtitle {
      font-size: 1.3rem;
      color: #6b7280;
      margin-bottom: 2rem;
    }

    form.form-registro {
      display: flex;
      flex-direction: column;
      gap: 1.3rem;
    }

    label {
      font-size: 1.25rem;
      font-weight: 600;
      color: #111827;
      margin-bottom: 0.3rem;
      display: block;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    select {
      width: 100%;
      padding: 0.9rem 1rem;
      border-radius: 0.7rem;
      border: 0.12rem solid #d1d5db;
      font-size: 1.35rem;
      outline: none;
      transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
      background-color: #f9fafb;
    }

    input:focus,
    select:focus {
      border-color: var(--azul-principal);
      box-shadow: 0 0 0 0.15rem rgba(0, 102, 204, 0.18);
      background-color: #ffffff;
    }

    .campo {
      display: flex;
      flex-direction: column;
    }

    .help-text {
      font-size: 1.1rem;
      color: #6b7280;
      margin-top: 0.2rem;
    }

    .msg-error,
    .msg-exito {
      padding: 0.8rem 1rem;
      border-radius: 0.7rem;
      font-size: 1.3rem;
      margin-bottom: 0.8rem;
    }

    .msg-error {
      background-color: #fee2e2;
      color: #b91c1c;
      border: 0.1rem solid #fecaca;
    }

    .msg-exito {
      background-color: #dcfce7;
      color: #166534;
      border: 0.1rem solid #bbf7d0;
    }

    .btns {
      display: flex;
      gap: 1rem;
      margin-top: 0.5rem;
    }

    .btn-azul,
    .btn-volver {
      flex: 1;
      padding: 0.9rem 1.4rem;
      font-size: 1.4rem;
      border-radius: 0.8rem;
      border: none;
      cursor: pointer;
      transition: background-color 0.25s ease, box-shadow 0.25s ease, transform 0.1s ease;
      font-weight: 600;
    }

    .btn-azul {
      background-color: var(--azul-principal);
      color: #ffffff;
      box-shadow: 0 0.7rem 1.6rem rgba(0, 0, 0, 0.15);
    }

    .btn-azul:hover {
      background-color: var(--azul-oscuro);
      transform: translateY(-0.05rem);
    }

    .btn-volver {
      background-color: #e5e7eb;
      color: #111827;
    }

    .btn-volver:hover {
      background-color: #d1d5db;
      transform: translateY(-0.05rem);
    }

    .msg-login {
      margin-top: 1.4rem;
      font-size: 1.3rem;
      text-align: center;
      color: #4b5563;
    }

    .msg-login a {
      color: var(--azul-principal);
      font-weight: 600;
    }

    .msg-login a:hover {
      text-decoration: underline;
    }

    /* RESPONSIVE */
    @media (max-width: 900px) {
      .registro-wrapper {
        grid-template-columns: 1fr;
      }

      .registro-info {
        padding: 2.2rem 2.4rem;
        text-align: center;
      }

      .registro-logo {
        justify-content: center;
      }

      .registro-highlights {
        grid-template-columns: 1fr;
      }

      .registro-form-container {
        padding: 2.4rem 2.2rem 2.6rem 2.2rem;
      }
    }

    @media (max-width: 600px) {
      body {
        padding: 1.2rem;
        background-attachment: scroll;
      }

      .registro-wrapper {
        border-radius: 1.4rem;
      }

      .btns {
        flex-direction: column;
      }
    }
  </style>
</head>

<body>

  <div class="registro-wrapper">
    <!-- LADO IZQUIERDO: MENSAJE MOTIVADOR -->
    <section class="registro-info">
      <div class="registro-logo">
        <img src="/public/img/main.png" alt="Logo LinkedIn EMI">
        <span>LinkedIn EMI</span>
      </div>

      <p class="registro-kicker">Comunidad profesional EMI</p>

      <h1>
        Regístrate para ser parte de algo
        <span>más grande que un currículum.</span>
      </h1>

      <p>
        Aquí no solo subes tu CV: construyes tu perfil profesional,
        te conectas con empresas aliadas y te acercas a las oportunidades
        que están hechas para la comunidad EMI.
      </p>

      <p>
        Da el primer paso, crea tu cuenta y comienza a mostrar lo que sabes,
        lo que estudias y lo que quieres lograr.
      </p>

      <div class="registro-highlights">
        <div class="registro-pill">
          <span class="registro-pill-dot"></span>
          <span>Convocatorias exclusivas para la EMI.</span>
        </div>
        <div class="registro-pill">
          <span class="registro-pill-dot"></span>
          <span>Conecta con reclutadores y empresas reales.</span>
        </div>
        <div class="registro-pill">
          <span class="registro-pill-dot"></span>
          <span>Comparte tus proyectos y logros.</span>
        </div>
        <div class="registro-pill">
          <span class="registro-pill-dot"></span>
          <span>Haz visible tu talento dentro y fuera de la EMI.</span>
        </div>
      </div>

      <p class="registro-footer-text">
        Todo empieza con tu registro. El siguiente paso ya es parte de tu historia profesional.
      </p>
    </section>

    <!-- LADO DERECHO: FORMULARIO -->
    <section class="registro-form-container">
      <h2>Crea tu cuenta</h2>
      <p class="registro-subtitle">
        Usa tu correo institucional para formar parte de la red profesional de la EMI.
      </p>

      <?php if ($mensaje): ?>
        <p class="msg-exito"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></p>
      <?php endif; ?>

      <?php if ($error): ?>
        <p class="msg-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
      <?php endif; ?>

      <form class="form-registro" action="" method="POST">
        <div class="campo">
          <label for="nombre">Nombre completo</label>
          <input
            type="text"
            id="nombre"
            name="nombre"
            placeholder="Ej. Juan Pérez"
            required
            value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8') : ''; ?>">
        </div>

        <div class="campo">
          <label for="correo">Correo institucional</label>
          <input
            type="email"
            id="correo"
            name="correo"
            placeholder="usuario@emi.edu.bo"
            required
            value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo'], ENT_QUOTES, 'UTF-8') : ''; ?>">
          <p class="help-text">Solo se aceptan correos con dominio @emi.edu.bo</p>
        </div>

        <div class="campo">
          <label for="password">Contraseña</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="********"
            required>
          <p class="help-text">Elige una contraseña segura que solo tú conozcas.</p>
        </div>

        <div class="campo">
          <label for="rol">Rol en la plataforma</label>
          <select id="rol" name="rol" required>
            <option value="">Seleccione un rol</option>
            <option value="postulante" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'postulante') ? 'selected' : ''; ?>>Postulante</option>
            <option value="empresa" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'empresa') ? 'selected' : ''; ?>>Reclutador / Empresa</option>
          </select>
        </div>

        <div class="btns">
          <button type="submit" class="btn-azul">Registrarme</button>
          <button type="button" class="btn-volver" onclick="window.history.back()">Volver</button>
        </div>

        <p class="msg-login">
          ¿Ya tienes una cuenta?
          <a href="/views/usuario/login.php">Inicia sesión aquí</a>
        </p>
      </form>
    </section>
  </div>

</body>

</html>