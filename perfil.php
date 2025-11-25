<?php
// perfil.php
session_start();
require_once 'config/database.php'; // Debe definir $conn como PDO

// Validar usuario logueado (igual que en empleo.php)
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /views/usuario/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuario no encontrado.");
}

// Obtener publicaciones del usuario
$stmt2 = $conn->prepare("SELECT * FROM publicaciones WHERE usuario_id = :id ORDER BY creado_en DESC");
$stmt2->bindParam(':id', $usuario_id, PDO::PARAM_INT);
$stmt2->execute();
$publicaciones = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Foto y CV con valores por defecto seguros
$fotoPerfil = !empty($usuario['foto']) ? $usuario['foto'] : '/public/img/image.png';
$cvRuta     = !empty($usuario['cv'])   ? $usuario['cv']   : null;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - LinkedIn EMI</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">

    <style>
        :root {
            --bg-page: #e5e7eb;
            --bg-card: #ffffff;
            --bg-soft: #f9fafb;
            --border-soft: #e5e7eb;
            --border-strong: #d1d5db;
            --primary: #0ea5e9;
            --primary-dark: #0284c7;
            --tag-bg: #eff6ff;
            --tag-text: #1d4ed8;
            --text-main: #111827;
            --text-muted: #6b7280;
            --text-soft: #9ca3af;
            --shadow-soft: 0 4px 16px rgba(15, 23, 42, 0.08);
            --shadow-strong: 0 6px 20px rgba(15, 23, 42, 0.16);
            --radius-card: 1.1rem;
            --radius-pill: 999px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "Montserrat", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 16px;
            color: var(--text-main);
            background-color: var(--bg-page);
        }

        img {
            max-width: 100%;
            display: block;
        }

        .perfil-main {
            margin-top: 8rem;
            padding: 5.5rem 1rem 3rem;
        }

        .perfil-container {
            max-width: 1100px;
            margin: 0 auto;
            background-color: var(--bg-card);
            border-radius: 1.3rem;
            box-shadow: var(--shadow-strong);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.8rem;
        }

        /* GRID PRINCIPAL */
        .perfil-principal {
            display: grid;
            grid-template-columns: minmax(0, 1.8fr) minmax(260px, 1fr);
            gap: 1.8rem;
            align-items: flex-start;
        }

        /* CABECERA PERFIL */
        .perfil-header {
            background-color: var(--bg-soft);
            border-radius: var(--radius-card);
            border: 1px solid var(--border-soft);
            padding: 1.4rem 1.6rem;
            display: flex;
            gap: 1.4rem;
            align-items: flex-start;
        }

        .perfil-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
            flex-shrink: 0;
        }

        .perfil-info {
            flex: 1;
            min-width: 0;
        }

        .perfil-info h1 {
            margin: 0 0 0.4rem;
            font-size: 1.8rem;
            font-weight: 700;
            color: #0f172a;
            word-wrap: break-word;
        }

        .perfil-titulo {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.7rem;
        }

        .perfil-meta {
            margin: 0 0 0.25rem;
            font-size: 0.95rem;
            color: #4b5563;
        }

        .perfil-meta strong {
            color: var(--text-main);
            font-weight: 600;
        }

        .perfil-tags {
            margin-top: 0.6rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }

        .perfil-tag {
            background-color: var(--tag-bg);
            color: var(--tag-text);
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-pill);
            font-size: 0.88rem;
            font-weight: 600;
        }

        .perfil-resumen {
            margin-top: 0.9rem;
            font-size: 0.97rem;
            color: #374151;
            line-height: 1.7;
        }

        /* ACCIONES + CV */
        .acciones-perfil {
            background-color: var(--bg-soft);
            border-radius: var(--radius-card);
            border: 1px solid var(--border-soft);
            padding: 1.4rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            min-width: 0;
        }

        .acciones-perfil h2 {
            margin: 0 0 0.7rem;
            font-size: 1.2rem;
            color: var(--text-main);
        }

        .acciones-perfil a {
            text-decoration: none;
        }

        .acciones-perfil button {
            width: 100%;
            padding: 0.7rem 1rem;
            border-radius: 0.7rem;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 0.35rem;
            background-color: var(--primary);
            color: #ffffff;
            transition: background-color 0.15s ease, transform 0.1s ease, box-shadow 0.15s ease;
        }

        .acciones-perfil button.secondary {
            background-color: #e5e7eb;
            color: var(--text-main);
        }

        .acciones-perfil button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28);
        }

        .acciones-perfil button.secondary:hover {
            background-color: #d1d5db;
            box-shadow: none;
        }

        .cv-block {
            margin-top: 0.9rem;
            padding-top: 0.8rem;
            border-top: 1px solid var(--border-soft);
        }

        .cv-block h3 {
            margin: 0 0 0.5rem;
            font-size: 1.02rem;
            color: var(--text-main);
        }

        .cv-frame {
            width: 100%;
            height: 400px;
            border: 1px solid var(--border-strong);
            border-radius: 0.75rem;
        }

        .cv-link {
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        .cv-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        .cv-link a:hover {
            text-decoration: underline;
        }

        /* ACTIVIDAD / PUBLICACIONES */
        .actividad {
            background-color: var(--bg-soft);
            border-radius: var(--radius-card);
            border: 1px solid var(--border-soft);
            padding: 1.5rem 1.6rem 1.7rem;
        }

        .actividad h2 {
            margin: 0 0 0.4rem;
            font-size: 1.45rem;
            color: #0f172a;
        }

        .actividad-sub {
            margin: 0 0 1rem;
            font-size: 0.95rem;
            color: var(--text-muted);
        }

        .publicacion {
            border-top: 1px solid var(--border-soft);
            padding-top: 1rem;
            margin-top: 1rem;
        }

        .publicacion:first-of-type {
            border-top: none;
            padding-top: 0;
            margin-top: 0;
        }

        .publicacion p {
            margin: 0 0 0.5rem;
            font-size: 0.97rem;
            color: var(--text-main);
        }

        .img-publicacion {
            border-radius: 0.7rem;
            margin: 0.5rem 0;
        }

        .publicacion small {
            font-size: 0.85rem;
            color: var(--text-soft);
        }

        .sin-publicaciones {
            margin-top: 0.6rem;
            font-size: 0.97rem;
            color: var(--text-muted);
        }

        /* ===========================
           RESPONSIVE MOBILE FIRST
           =========================== */

        /* TABLET Y MENOS */
        @media (max-width: 900px) {
            .perfil-principal {
                grid-template-columns: 1fr;
            }

            .perfil-header {
                padding: 1.3rem 1.25rem;
            }

            .acciones-perfil {
                padding: 1.3rem 1.25rem;
            }

            .cv-frame {
                height: 360px;
            }
        }

        /* CELULAR */
        @media (max-width: 768px) {
            .perfil-main {
                padding: 5rem 0.75rem 2.5rem;
            }

            .perfil-container {
                padding: 1.2rem 1rem 1.4rem;
                border-radius: 1.1rem;
                box-shadow: 0 4px 12px rgba(15, 23, 42, 0.14);
            }

            .perfil-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 1rem;
            }

            .perfil-img {
                width: 110px;
                height: 110px;
            }

            .perfil-info h1 {
                font-size: 1.6rem;
            }

            .perfil-titulo {
                font-size: 0.98rem;
            }

            .perfil-meta {
                font-size: 0.93rem;
            }

            .perfil-tags {
                justify-content: center;
            }

            .perfil-resumen {
                font-size: 0.95rem;
                text-align: left;
            }

            .acciones-perfil button {
                font-size: 0.94rem;
                padding: 0.7rem 0.9rem;
            }

            .actividad {
                padding: 1.4rem 1.25rem 1.5rem;
            }

            .actividad h2 {
                font-size: 1.35rem;
            }

            .actividad-sub,
            .publicacion p,
            .sin-publicaciones {
                font-size: 0.94rem;
            }
        }

        /* CELULAR PEQUEÑO */
        @media (max-width: 480px) {
            .perfil-main {
                padding: 4.7rem 0.6rem 2.3rem;
            }

            .perfil-container {
                padding: 1.1rem 0.85rem 1.3rem;
            }

            .perfil-img {
                width: 100px;
                height: 100px;
            }

            .perfil-info h1 {
                font-size: 1.55rem;
            }

            .perfil-meta,
            .perfil-resumen,
            .publicacion p {
                font-size: 0.93rem;
            }

            .cv-frame {
                height: 320px;
            }

            .acciones-perfil h2 {
                font-size: 1.15rem;
            }

            .acciones-perfil button {
                font-size: 0.93rem;
            }
        }
        main{
            margin-top: 6rem;
        }
    </style>
</head>

<body>
    <?php include 'views/cabeza/header.php'; ?>

    <main class="perfil-main"  >
        <div class="perfil-container">
            <!-- BLOQUE PRINCIPAL DEL PERFIL -->
            <section class="perfil-principal op">
                <!-- INFO DE PERFIL -->
                <div class="perfil-header">
                    <img
                        src="<?php echo htmlspecialchars($fotoPerfil); ?>"
                        alt="Foto de perfil"
                        class="perfil-img">

                    <div class="perfil-info">
                        <h1>
                            <?php echo htmlspecialchars($usuario['nombre'] . ' ' . ($usuario['apellidos'] ?? '')); ?>
                        </h1>

                        <?php if (!empty($usuario['titulo_perfil'])): ?>
                            <div class="perfil-titulo">
                                <?php echo htmlspecialchars($usuario['titulo_perfil']); ?>
                            </div>
                        <?php endif; ?>

                        <p class="perfil-meta">
                            <strong>Carrera:</strong>
                            <?php echo htmlspecialchars($usuario['carrera'] ?? 'No especificada'); ?>
                        </p>

                        <p class="perfil-meta">
                            <strong>Ubicación:</strong>
                            <?php
                            $ciudad = $usuario['ubicacion_ciudad'] ?? '';
                            $pais   = $usuario['ubicacion_pais'] ?? '';
                            $ubi    = trim($ciudad . ', ' . $pais, ' ,');
                            echo htmlspecialchars($ubi !== '' ? $ubi : 'Sin ubicación registrada');
                            ?>
                        </p>

                        <p class="perfil-meta">
                            <strong>Email:</strong>
                            <?php echo htmlspecialchars($usuario['correo']); ?>
                        </p>

                        <?php if (!empty($usuario['telefono'])): ?>
                            <p class="perfil-meta">
                                <strong>Teléfono:</strong>
                                <?php echo htmlspecialchars($usuario['telefono']); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($usuario['fecha_nacimiento'])): ?>
                            <p class="perfil-meta">
                                <strong>Fecha de nacimiento:</strong>
                                <?php echo htmlspecialchars($usuario['fecha_nacimiento']); ?>
                            </p>
                        <?php endif; ?>

                        <div class="perfil-tags">
                            <?php if (!empty($usuario['codigo_emi'])): ?>
                                <span class="perfil-tag">
                                    Código EMI: <?php echo htmlspecialchars($usuario['codigo_emi']); ?>
                                </span>
                            <?php endif; ?>

                            <?php if (!empty($usuario['anio_egreso'])): ?>
                                <span class="perfil-tag">
                                    Egreso: <?php echo htmlspecialchars($usuario['anio_egreso']); ?>
                                </span>
                            <?php endif; ?>

                            <?php if (!empty($usuario['semestre_actual'])): ?>
                                <span class="perfil-tag">
                                    Semestre: <?php echo htmlspecialchars($usuario['semestre_actual']); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($usuario['resumen'])): ?>
                            <div class="perfil-resumen">
                                <?php echo nl2br(htmlspecialchars($usuario['resumen'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ACCIONES Y CV -->
                <aside class="acciones-perfil">
                    <h2>Acciones rápidas</h2>

                    <a href="editar.php">
                        <button type="button">
                            Editar información
                        </button>
                    </a>

                    <a href="/">
                        <button type="button" class="secondary">
                            Ver publicaciones
                        </button>
                    </a>

                    <a href="postulacion.php">
                        <button type="button" class="secondary">
                            Mis postulaciones
                        </button>
                    </a>

                    <div class="cv-block">
                        <?php if ($cvRuta): ?>
                            <h3>Mi CV</h3>
                            <iframe
                                src="<?php echo htmlspecialchars($cvRuta); ?>"
                                class="cv-frame"></iframe>

                            <p class="cv-link">
                                <a
                                    href="<?php echo htmlspecialchars($cvRuta); ?>"
                                    target="_blank">
                                    Abrir CV en otra pestaña
                                </a>
                            </p>
                        <?php else: ?>
                            <h3>CV no subido</h3>
                            <p style="font-size: 0.9rem; color: var(--text-muted);">
                                Aún no has subido tu CV. Súbelo en la sección de edición de perfil
                                para aumentar tus oportunidades.
                            </p>
                        <?php endif; ?>
                    </div>
                </aside>
            </section>

            <!-- ACTIVIDAD RECIENTE -->
            <section class="actividad op">
                <h2>Actividad reciente</h2>
                <p class="actividad-sub">
                    Aquí verás las publicaciones que compartes como estudiante o egresado de la EMI.
                </p>

                <?php if (count($publicaciones) > 0): ?>
                    <?php foreach ($publicaciones as $pub): ?>
                        <article class="publicacion">
                            <p><?php echo nl2br(htmlspecialchars($pub['contenido'])); ?></p>

                            <?php if (!empty($pub['imagen'])): ?>
                                <img
                                    src="<?php echo htmlspecialchars($pub['imagen']); ?>"
                                    alt="Imagen publicación"
                                    class="img-publicacion">
                            <?php endif; ?>

                            <small>
                                Publicado:
                                <?php
                                $fecha = $pub['creado_en'] ? date('d/m/Y H:i', strtotime($pub['creado_en'])) : '';
                                echo htmlspecialchars($fecha);
                                ?>
                            </small>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="sin-publicaciones">
                        Aún no has realizado publicaciones. Comparte proyectos, logros o experiencias
                        para fortalecer tu marca profesional.
                    </p>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <?php include 'views/cabeza/footer.php'; ?>
</body>

</html>