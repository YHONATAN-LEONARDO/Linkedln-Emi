<?php
require_once __DIR__ . '/../../config/database.php';   // Conexión PDO
require_once __DIR__ . '/../../config/funcion_db.php'; // Funciones CRUD
require_once __DIR__ . '/../../config/session.php';    // Manejo de sesiones

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo   = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($correo && $password) {

        // ATAJO ADMIN
        if ($correo === 'emi@emi.edu.bo' && $password === 'emi') {
            $_SESSION['usuario_id'] = 1;
            $_SESSION['rol']        = 1;
            $_SESSION['nombre']     = 'Admin';
            header('Location: /admin.php');
            exit();
        }

        // Login normal
        $usuario = validarLogin($correo, $password);

        if ($usuario) {
            // Crear sesión segura
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['rol']        = $usuario['rol_id'];
            $_SESSION['nombre']     = $usuario['nombre'];

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
    <title>Iniciar sesión en LinkedIn EMI</title>

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
        }

        * {
            box-sizing: border-box;
        }

        html {
            font-size: 62.5%;
        }

        body {
            font-family: "Montserrat", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;

            /* MISMO FONDO QUE REGISTRO, MENOS ZOOM */
            background: #f3f3f4 url("/public/img/page.png") no-repeat top center fixed;
            background-size: 80%;
            background-position: center 40px;

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
        .login-wrapper {
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

        /* LADO IZQUIERDO: MENSAJE INSPIRADOR */
        .login-info {
            padding: 3rem 3.5rem;
            background: linear-gradient(135deg, #ffffff 0%, #e3f2fd 30%, #f4f2ee 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 1.8rem;
        }

        .login-logo {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .login-logo img {
            width: 4.2rem;
            height: auto;
            object-fit: contain;
        }

        .login-logo span {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--azul-principal);
        }

        .login-kicker {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--azul-oscuro);
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .login-info h1 {
            font-size: 2.6rem;
            line-height: 1.25;
            color: #111827;
            margin: 0;
        }

        .login-info h1 span {
            color: var(--azul-principal);
        }

        .login-info p {
            font-size: 1.45rem;
            line-height: 1.6;
            color: #374151;
            margin: 0;
        }

        .login-highlights {
            margin-top: 1.4rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            font-size: 1.3rem;
        }

        .login-pill {
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

        .login-pill-dot {
            width: 0.9rem;
            height: 0.9rem;
            border-radius: 50%;
            background-color: var(--azul-principal);
        }

        .login-footer-text {
            margin-top: 1.6rem;
            font-size: 1.25rem;
            color: #6b7280;
        }

        /* FORMULARIO (LADO DERECHO) */
        .login-form-container {
            padding: 3rem 3.5rem;
            background-color: rgba(255, 255, 255, 0.98);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form-container h2 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #111827;
        }

        .login-subtitle {
            font-size: 1.3rem;
            color: #6b7280;
            margin-bottom: 2rem;
        }

        form.form-login {
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

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.9rem 1rem;
            border-radius: 0.7rem;
            border: 0.12rem solid #d1d5db;
            font-size: 1.35rem;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
            background-color: #f9fafb;
        }

        input:focus {
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

        .msg-error {
            padding: 0.8rem 1rem;
            border-radius: 0.7rem;
            font-size: 1.3rem;
            margin-bottom: 0.8rem;
            background-color: #fee2e2;
            color: #b91c1c;
            border: 0.1rem solid #fecaca;
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

        .msg-registro {
            margin-top: 1.4rem;
            font-size: 1.3rem;
            text-align: center;
            color: #4b5563;
        }

        .msg-registro a {
            color: var(--azul-principal);
            font-weight: 600;
        }

        .msg-registro a:hover {
            text-decoration: underline;
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .login-wrapper {
                grid-template-columns: 1fr;
            }

            .login-info {
                padding: 2.2rem 2.4rem;
                text-align: center;
            }

            .login-logo {
                justify-content: center;
            }

            .login-highlights {
                grid-template-columns: 1fr;
            }

            .login-form-container {
                padding: 2.4rem 2.2rem 2.6rem 2.2rem;
            }
        }

        @media (max-width: 600px) {
            body {
                padding: 1.2rem;
                background-attachment: scroll;
            }

            .login-wrapper {
                border-radius: 1.4rem;
            }

            .btns {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">
        <!-- LADO IZQUIERDO: MENSAJE INSPIRADOR -->
        <section class="login-info">
            <div class="login-logo">
                <img src="/public/img/main.png" alt="Logo LinkedIn EMI">
                <span>LinkedIn EMI</span>
            </div>

            <p class="login-kicker">Vuelve a tu espacio profesional</p>

            <h1>
                Aquí retomas lo que ya
                <span>empezaste a construir.</span>
            </h1>

            <p>
                Inicia sesión para seguir impulsando tu perfil profesional,
                postular a nuevas convocatorias y mantenerte conectado con la comunidad EMI.
            </p>

            <p>
                Cada inicio de sesión es una oportunidad para actualizar tu perfil,
                mostrar tus proyectos y acercarte a las empresas que buscan tu talento.
            </p>

            <div class="login-highlights">
                <div class="login-pill">
                    <span class="login-pill-dot"></span>
                    <span>Revisa las últimas convocatorias publicadas.</span>
                </div>
                <div class="login-pill">
                    <span class="login-pill-dot"></span>
                    <span>Actualiza tu experiencia y habilidades.</span>
                </div>
                <div class="login-pill">
                    <span class="login-pill-dot"></span>
                    <span>Conecta con reclutadores EMI y aliados.</span>
                </div>
                <div class="login-pill">
                    <span class="login-pill-dot"></span>
                    <span>Haz seguimiento a tus postulaciones.</span>
                </div>
            </div>

            <p class="login-footer-text">
                Tu futuro profesional no se pausa: continúa desde donde lo dejaste iniciando sesión.
            </p>
        </section>

        <!-- LADO DERECHO: FORMULARIO LOGIN -->
        <section class="login-form-container">
            <h2>Iniciar sesión</h2>
            <p class="login-subtitle">
                Usa tu correo institucional para acceder a tu cuenta de LinkedIn EMI.
            </p>

            <?php if ($error): ?>
                <p class="msg-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <form class="form-login" action="" method="POST">
                <div class="campo">
                    <label for="correo">Correo institucional</label>
                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        placeholder="usuario@emi.edu.bo"
                        required
                        value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    <p class="help-text">Recuerda usar tu correo con dominio @emi.edu.bo</p>
                </div>

                <div class="campo">
                    <label for="password">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="********"
                        required>
                    <p class="help-text">Es la misma contraseña que usaste al registrarte.</p>
                </div>

                <div class="btns">
                    <button type="submit" class="btn-azul">Ingresar</button>
                    <button type="button" class="btn-volver" onclick="window.history.back()">Volver</button>
                </div>

                <p class="msg-registro">
                    ¿Aún no tienes una cuenta?
                    <a href="/views/usuario/registro.php">Regístrate aquí</a>
                </p>
            </form>
        </section>
    </div>

</body>

</html>