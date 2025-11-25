<?php
// info.php

// 1) Sesión
require_once __DIR__ . '/config/session.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2) Conexión BD
require_once __DIR__ . '/config/database.php';

// 3) Verificar usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /portada.php');
    exit;
}

$usuarioLogueadoId = (int) $_SESSION['usuario_id'];

// 4) ID del perfil a ver: ?id=... o yo mismo
$perfilId = isset($_GET['id']) ? (int) $_GET['id'] : $usuarioLogueadoId;
if ($perfilId <= 0) {
    $perfilId = $usuarioLogueadoId;
}

// 5) Obtener datos del usuario que se va a mostrar
$stmt = $conn->prepare("
    SELECT 
        id,
        rol_id,
        nombre,
        apellidos,
        correo,
        telefono,
        fecha_nacimiento,
        ubicacion_ciudad,
        ubicacion_pais,
        codigo_emi,
        carrera,
        semestre_actual,
        anio_egreso,
        titulo_perfil,
        resumen,
        experiencia_actual,
        habilidades,
        intereses,
        url_linkedin,
        url_github,
        url_portafolio,
        foto,
        cv,
        estado,
        creado_en
    FROM usuarios
    WHERE id = :id
");
$stmt->bindParam(':id', $perfilId, PDO::PARAM_INT);
$stmt->execute();
$usuarioPerfil = $stmt->fetch(PDO::FETCH_ASSOC);

$usuarioEncontrado = $usuarioPerfil !== false;

// 6) Armar variables solo si existe
$nombreCompleto   = 'Usuario EMI';
$fotoPerfil       = '/public/img/image.png';
$ubicacion        = '';
$miembroDesde     = '—';
$tituloPagina     = 'Usuario no encontrado - LinkedIn EMI';

if ($usuarioEncontrado) {
    $nombre      = $usuarioPerfil['nombre']    ?? '';
    $apellidos   = $usuarioPerfil['apellidos'] ?? '';
    $nombreCompleto = trim($nombre . ' ' . $apellidos);
    if ($nombreCompleto === '') {
        $nombreCompleto = 'Usuario EMI';
    }

    $fotoPerfil = !empty($usuarioPerfil['foto'])
        ? htmlspecialchars($usuarioPerfil['foto'], ENT_QUOTES, 'UTF-8')
        : '/public/img/image.png';

    $ciudad = $usuarioPerfil['ubicacion_ciudad'] ?? '';
    $pais   = $usuarioPerfil['ubicacion_pais']   ?? '';
    $ubicacion = trim($ciudad . ($ciudad && $pais ? ', ' : '') . $pais);

    $miembroDesde = !empty($usuarioPerfil['creado_en'])
        ? date('d/m/Y', strtotime($usuarioPerfil['creado_en']))
        : '—';

    $tituloPagina = $nombreCompleto . ' - LinkedIn EMI';
}

// 7) Últimas publicaciones de este usuario (TOP 5 para SQL Server)
$publicaciones = [];
if ($usuarioEncontrado) {
    $stmtPub = $conn->prepare("
        SELECT TOP 5
            contenido,
            imagen,
            creado_en
        FROM publicaciones
        WHERE usuario_id = :id
        ORDER BY creado_en DESC
    ");
    $stmtPub->bindParam(':id', $perfilId, PDO::PARAM_INT);
    $stmtPub->execute();
    $publicaciones = $stmtPub->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8'); ?></title>

    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        body {
            font-family: "Montserrat", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f3f4f6;
        }

        .perfil-contenedor {
            max-width: 1100px;
            margin: 6rem auto 3rem;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
            gap: 1.5rem;
        }

        .perfil-card,
        .perfil-card-sec {
            background: #fff;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        }

        .perfil-header {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .perfil-header img {
            width: 92px;
            height: 92px;
            border-radius: 999px;
            object-fit: cover;
            border: 2px solid #e5e7eb;
            background: #f9fafb;
        }

        .perfil-header h1 {
            margin: 0;
            font-size: 1.4rem;
        }

        .perfil-header p {
            margin: 0.15rem 0;
            font-size: .9rem;
            color: #4b5563;
        }

        .perfil-badges {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            margin-top: .4rem;
        }

        .perfil-badge {
            font-size: .75rem;
            padding: .15rem .55rem;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .perfil-seccion-titulo {
            font-size: .95rem;
            font-weight: 600;
            margin: 1rem 0 .4rem;
            color: #111827;
        }

        .perfil-seccion-texto {
            font-size: .9rem;
            color: #4b5563;
            margin: 0;
            white-space: pre-line;
        }

        .perfil-datos-lista {
            list-style: none;
            padding: 0;
            margin: .4rem 0 0;
        }

        .perfil-datos-lista li {
            display: flex;
            justify-content: space-between;
            gap: .7rem;
            font-size: .85rem;
            padding: .25rem 0;
            border-bottom: 1px dashed #e5e7eb;
        }

        .perfil-datos-label {
            color: #6b7280;
        }

        .perfil-datos-valor {
            text-align: right;
            color: #111827;
        }

        .perfil-links {
            display: flex;
            flex-direction: column;
            gap: .3rem;
            margin-top: .5rem;
        }

        .perfil-links a {
            font-size: .9rem;
            color: #2563eb;
            text-decoration: none;
        }

        .perfil-links a:hover {
            text-decoration: underline;
        }

        .perfil-publicaciones-lista {
            display: flex;
            flex-direction: column;
            gap: .75rem;
            margin-top: .5rem;
        }

        .perfil-pub-item {
            padding: .7rem .8rem;
            border-radius: .7rem;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .perfil-pub-item p {
            margin: 0 0 .35rem;
            font-size: .9rem;
            color: #111827;
        }

        .perfil-pub-meta {
            font-size: .75rem;
            color: #6b7280;
        }

        @media (max-width: 900px) {
            .perfil-contenedor {
                grid-template-columns: minmax(0, 1fr);
                margin-top: 5rem;
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/views/cabeza/header.php'; ?>

    <main class="perfil-contenedor" style="margin-top:14rem;" >

        <!-- Lado izquierdo: info principal -->
        <section class="perfil-card">
            <?php if (!$usuarioEncontrado): ?>
                <h1>Usuario no encontrado</h1>
                <p>El usuario que intentas ver no existe o ha sido dado de baja.</p>
            <?php else: ?>
                <header class="perfil-header">
                    <img src="<?php echo $fotoPerfil; ?>" alt="Foto de perfil">
                    <div>
                        <h1><?php echo htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8'); ?></h1>

                        <?php if (!empty($usuarioPerfil['titulo_perfil'])): ?>
                            <p><?php echo htmlspecialchars($usuarioPerfil['titulo_perfil'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php elseif (!empty($usuarioPerfil['carrera'])): ?>
                            <p><?php echo htmlspecialchars($usuarioPerfil['carrera'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>

                        <?php if ($ubicacion): ?>
                            <p><?php echo htmlspecialchars($ubicacion, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>

                        <div class="perfil-badges">
                            <?php if (!empty($usuarioPerfil['estado'])): ?>
                                <span class="perfil-badge">
                                    Estado: <?php echo htmlspecialchars($usuarioPerfil['estado'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endif; ?>
                            <span class="perfil-badge">
                                Miembro desde <?php echo htmlspecialchars($miembroDesde, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                    </div>
                </header>

                <?php if (!empty($usuarioPerfil['resumen'])): ?>
                    <h2 class="perfil-seccion-titulo">Resumen</h2>
                    <p class="perfil-seccion-texto">
                        <?php echo nl2br(htmlspecialchars($usuarioPerfil['resumen'], ENT_QUOTES, 'UTF-8')); ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($usuarioPerfil['experiencia_actual'])): ?>
                    <h2 class="perfil-seccion-titulo">Experiencia actual</h2>
                    <p class="perfil-seccion-texto">
                        <?php echo nl2br(htmlspecialchars($usuarioPerfil['experiencia_actual'], ENT_QUOTES, 'UTF-8')); ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($usuarioPerfil['habilidades'])): ?>
                    <h2 class="perfil-seccion-titulo">Habilidades</h2>
                    <p class="perfil-seccion-texto">
                        <?php echo nl2br(htmlspecialchars($usuarioPerfil['habilidades'], ENT_QUOTES, 'UTF-8')); ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($usuarioPerfil['intereses'])): ?>
                    <h2 class="perfil-seccion-titulo">Intereses</h2>
                    <p class="perfil-seccion-texto">
                        <?php echo nl2br(htmlspecialchars($usuarioPerfil['intereses'], ENT_QUOTES, 'UTF-8')); ?>
                    </p>
                <?php endif; ?>

            <?php endif; ?>
        </section>

        <!-- Lado derecho: datos académicos, contacto y publicaciones -->
        <section class="perfil-card-sec">
            <?php if ($usuarioEncontrado): ?>
                <h2 class="perfil-seccion-titulo">Datos académicos y de contacto</h2>
                <ul class="perfil-datos-lista">
                    <?php if (!empty($usuarioPerfil['correo'])): ?>
                        <li>
                            <span class="perfil-datos-label">Correo:</span>
                            <span class="perfil-datos-valor">
                                <?php echo htmlspecialchars($usuarioPerfil['correo'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </li>
                    <?php endif; ?>

                    <?php if (!empty($usuarioPerfil['telefono'])): ?>
                        <li>
                            <span class="perfil-datos-label">Teléfono:</span>
                            <span class="perfil-datos-valor">
                                <?php echo htmlspecialchars($usuarioPerfil['telefono'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </li>
                    <?php endif; ?>

                    <?php if (!empty($usuarioPerfil['codigo_emi'])): ?>
                        <li>
                            <span class="perfil-datos-label">Código EMI:</span>
                            <span class="perfil-datos-valor">
                                <?php echo htmlspecialchars($usuarioPerfil['codigo_emi'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </li>
                    <?php endif; ?>

                    <?php if (!empty($usuarioPerfil['carrera'])): ?>
                        <li>
                            <span class="perfil-datos-label">Carrera:</span>
                            <span class="perfil-datos-valor">
                                <?php echo htmlspecialchars($usuarioPerfil['carrera'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </li>
                    <?php endif; ?>

                    <?php if (!empty($usuarioPerfil['semestre_actual'])): ?>
                        <li>
                            <span class="perfil-datos-label">Semestre actual:</span>
                            <span class="perfil-datos-valor">
                                <?php echo (int)$usuarioPerfil['semestre_actual']; ?>
                            </span>
                        </li>
                    <?php endif; ?>

                    <?php if (!empty($usuarioPerfil['anio_egreso'])): ?>
                        <li>
                            <span class="perfil-datos-label">Año de egreso:</span>
                            <span class="perfil-datos-valor">
                                <?php echo (int)$usuarioPerfil['anio_egreso']; ?>
                            </span>
                        </li>
                    <?php endif; ?>
                </ul>

                <?php if (
                    !empty($usuarioPerfil['url_linkedin']) ||
                    !empty($usuarioPerfil['url_github'])   ||
                    !empty($usuarioPerfil['url_portafolio'])
                ): ?>
                    <h2 class="perfil-seccion-titulo">Enlaces</h2>
                    <div class="perfil-links">
                        <?php if (!empty($usuarioPerfil['url_linkedin'])): ?>
                            <a href="<?php echo htmlspecialchars($usuarioPerfil['url_linkedin'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                LinkedIn
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($usuarioPerfil['url_github'])): ?>
                            <a href="<?php echo htmlspecialchars($usuarioPerfil['url_github'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                GitHub
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($usuarioPerfil['url_portafolio'])): ?>
                            <a href="<?php echo htmlspecialchars($usuarioPerfil['url_portafolio'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                Portafolio
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <h2 class="perfil-seccion-titulo" style="margin-top: 1.3rem;">Últimas publicaciones</h2>
                <?php if (empty($publicaciones)): ?>
                    <p class="perfil-seccion-texto">Este usuario todavía no ha compartido publicaciones.</p>
                <?php else: ?>
                    <div class="perfil-publicaciones-lista">
                        <?php foreach ($publicaciones as $pub): ?>
                            <article class="perfil-pub-item">
                                <p>
                                    <?php echo nl2br(htmlspecialchars($pub['contenido'] ?? '', ENT_QUOTES, 'UTF-8')); ?>
                                </p>
                                <?php if (!empty($pub['imagen'])): ?>
                                    <img
                                        src="<?php echo htmlspecialchars($pub['imagen'], ENT_QUOTES, 'UTF-8'); ?>"
                                        alt="Imagen de la publicación">
                                <?php endif; ?>
                                <div class="perfil-pub-meta">
                                    <?php if (!empty($pub['creado_en'])): ?>
                                        Publicado el
                                        <?php echo htmlspecialchars($pub['creado_en'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <p>No se pudo cargar la información del usuario.</p>
            <?php endif; ?>
        </section>
    </main>

    <?php include __DIR__ . '/views/cabeza/footer.php'; ?>
</body>

</html>