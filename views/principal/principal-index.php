<?php
// views/principal/principal-index.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

// Si no hay sesión, mandar a portada/login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /portada.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

/* ============================
   DATOS DEL USUARIO LOGUEADO
   ============================ */
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    session_destroy();
    header('Location: /views/usuario/login.php');
    exit;
}

$nombre      = $usuario['nombre']     ?? '';
$apellidos   = $usuario['apellidos']  ?? '';
$nombreCompleto = trim($nombre . ' ' . $apellidos);
if ($nombreCompleto === '') {
    $nombreCompleto = 'Usuario EMI';
}

$tituloPerfil = $usuario['titulo_perfil'] ?? '';
$carrera      = $usuario['carrera']       ?? '';

$ciudad       = $usuario['ubicacion_ciudad'] ?? '';
$pais         = $usuario['ubicacion_pais']   ?? '';
$ubicacion    = trim($ciudad . ($ciudad && $pais ? ', ' : '') . $pais);

$fotoPerfil = !empty($usuario['foto'])
    ? htmlspecialchars($usuario['foto'], ENT_QUOTES, 'UTF-8')
    : '/public/img/image.png';

$miembroDesde = !empty($usuario['creado_en'])
    ? date('d/m/Y', strtotime($usuario['creado_en']))
    : '—';

// Rol legible
$rolLegible = 'Postulante';
switch ($usuario['rol_id'] ?? 3) {
    case 1:
        $rolLegible = 'Administrador';
        break;
    case 2:
        $rolLegible = 'Empresa / Reclutador';
        break;
}

/* ============================
   ESTADÍSTICAS SIMPLES
   ============================ */

// Total de publicaciones en toda la plataforma
$totalPublicacionesStmt = $conn->query("SELECT COUNT(*) AS total FROM publicaciones");
$totalPublicacionesRow  = $totalPublicacionesStmt->fetch(PDO::FETCH_ASSOC);
$totalPublicaciones     = (int)($totalPublicacionesRow['total'] ?? 0);

// Publicaciones del usuario logueado
$misPublicacionesStmt = $conn->prepare("SELECT COUNT(*) AS total FROM publicaciones WHERE usuario_id = :id");
$misPublicacionesStmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
$misPublicacionesStmt->execute();
$misPublicacionesRow  = $misPublicacionesStmt->fetch(PDO::FETCH_ASSOC);
$misPublicaciones     = (int)($misPublicacionesRow['total'] ?? 0);

/* ============================
   FEED DE PUBLICACIONES (TODOS)
   ============================ */
$stmtPublicaciones = $conn->prepare("
    SELECT 
        p.*,
        u.nombre           AS autor_nombre,
        u.apellidos        AS autor_apellidos,
        u.titulo_perfil    AS autor_titulo,
        u.carrera          AS autor_carrera,
        u.ubicacion_ciudad AS autor_ciudad,
        u.ubicacion_pais   AS autor_pais,
        u.foto             AS autor_foto
    FROM publicaciones p
    INNER JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.creado_en DESC
");
$stmtPublicaciones->execute();
$publicaciones = $stmtPublicaciones->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="principal contenedor">
    <!-- ===============================
         LADO IZQUIERDO: INFO DEL PERFIL
         =============================== -->
    <section class="lado-izquierdo">
        <!-- TARJETA DE PERFIL MINI -->
        <a href="/perfil.php">
            <article class="usuario-izquierdo izi">
                <div class="primero-izquierdo">
                    <div class="usuario-izquierdo-img">
                        <img class="img-cl" src="/public/img/image.png" alt="fondo de perfil">
                    </div>

                    <div class="usuario-izquierdo-datos">
                        <img
                            class="img-usuario im"
                            src="<?php echo $fotoPerfil; ?>"
                            alt="foto del usuario">

                        <p><strong><?php echo htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8'); ?></strong></p>

                        <?php if ($tituloPerfil): ?>
                            <p><?php echo htmlspecialchars($tituloPerfil, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php elseif ($carrera): ?>
                            <p><?php echo htmlspecialchars($carrera, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php else: ?>
                            <p>Estudiante / Egresado EMI</p>
                        <?php endif; ?>

                        <?php if ($ubicacion): ?>
                            <p class="bb"><?php echo htmlspecialchars($ubicacion, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>

                        <div class="inf1">
                            <img class="ulo" src="/public/img/fondo-usuario.png" alt="">
                            <p>Tu perfil es tu tarjeta de presentación en la comunidad EMI.</p>
                        </div>
                    </div>
                </div>
            </article>
        </a>

        <!-- ÚNICO WIDGET EXTRA: RESUMEN RÁPIDO -->
        <article class="izi info-extra-perfil">
            <p><strong>Datos Personales</strong></p>
            <p><small>Información esencial de tu cuenta.</small></p>

            <ul class="info-extra-lista">


                <li>
                    <span class="info-extra-label">Miembro desde:</span>
                    <span class="info-extra-valor">
                        <?php echo htmlspecialchars($miembroDesde, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </li>

                <li>
                    <span class="info-extra-label">Tus publicaciones:</span>
                    <span class="info-extra-valor">
                        <?php echo (int)$misPublicaciones; ?>
                    </span>
                </li>


            </ul>

            <!-- BLOQUE COMPLETO: consejo con estilo y frases que cambian -->

            <!-- HTML -->
            <div class="info-extra-tip" id="infoTip">
                <span class="info-extra-tip__label">Consejo para tu perfil</span>
                <p class="info-extra-tip__text" id="infoTipText">
                    Comparte tus proyectos y logros para que tu perfil tenga más visibilidad.
                </p>
            </div>

            <!-- CSS -->
            <style>
                .info-extra-tip {
                    max-width: 600px;
                    margin: 16px auto;
                    padding: 16px 20px;
                    border-radius: 12px;
                    background: #f3f6ff;
                    border: 1px solid #d5ddff;
                    display: flex;
                    flex-direction: column;
                    gap: 6px;
                    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
                    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                }

                .info-extra-tip__label {
                    font-size: 0.8rem;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.08em;
                    color: #4f46e5;
                }

                .info-extra-tip__text {
                    margin: 0;
                    font-size: 0.95rem;
                    line-height: 1.5;
                    color: #111827;
                }

                .info-extra-tip__text strong {
                    font-weight: 600;
                }

                /* Animación suave cuando cambia el texto */
                .info-extra-tip--fade {
                    animation: fadeTip 0.5s ease-in-out;
                }

                @keyframes fadeTip {
                    from {
                        opacity: 0;
                        transform: translateY(4px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            </style>

            <!-- JavaScript -->
            <script>
                const tips = [
                    "Comparte tus <strong>proyectos y logros</strong> para que tu perfil tenga más visibilidad.",
                    "Añade ejemplos de trabajos reales y enlaces a tu <strong>portafolio</strong>.",
                    "Incluye logros con <strong>números y resultados</strong> (ventas, usuarios, mejoras, etc.).",
                    "Mantén tu perfil <strong>actualizado</strong> con tus últimos proyectos y cursos.",
                    "Usa una foto profesional y una descripción clara de <strong>quién eres y qué haces</strong>.",
                    "Conecta tu perfil con <strong>LinkedIn, GitHub o Behance</strong> para mostrar más de tu trabajo."
                ];

                const tipTextEl = document.getElementById("infoTipText");
                const tipBoxEl = document.getElementById("infoTip");
                let tipIndex = 0;

                function changeTip() {
                    tipIndex = (tipIndex + 1) % tips.length;

                    // Reiniciar animación
                    tipBoxEl.classList.remove("info-extra-tip--fade");
                    void tipBoxEl.offsetWidth; // truco para reiniciar la animación

                    tipTextEl.innerHTML = tips[tipIndex];
                    tipBoxEl.classList.add("info-extra-tip--fade");
                }

                // Iniciar cambio de frases cuando la página cargue
                document.addEventListener("DOMContentLoaded", function() {
                    // Primer texto desde el array (por si quieres cambiarlo ahí)
                    tipTextEl.innerHTML = tips[0];
                    tipBoxEl.classList.add("info-extra-tip--fade");
                    // Cambia la frase cada 7 segundos
                    setInterval(changeTip, 7000);
                });
            </script>

        </article>

    </section>

    <!-- =================================
         LADO DERECHO: PUBLICAR + FEED GLOBAL
         ================================= -->
    <section class="lado-derecho">

        <!-- BLOQUE PARA CREAR PUBLICACIÓN -->
        <section class="publicar-inicio publicar-inicio-main">
            <img src="<?php echo $fotoPerfil; ?>" alt="Foto del usuario">
            <button
                type="button"
                id="btn-abrir-publicar">
                ¿Qué quieres compartir hoy con la comunidad EMI?
            </button>
        </section>

        <!-- LISTA DE PUBLICACIONES (TODOS LOS USUARIOS) -->
        <section class="publicaciones publicaciones-feed-principal">
            <?php if (empty($publicaciones)): ?>
                <article class="card card-feed-principal">
                    <div>
                        <img src="<?php echo $fotoPerfil; ?>" alt="Usuario">
                        <div>
                            <a href="/perfil.php">
                                <p><?php echo htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8'); ?></p>
                            </a>
                            <?php if ($tituloPerfil): ?>
                                <p><?php echo htmlspecialchars($tituloPerfil, ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php elseif ($carrera): ?>
                                <p><?php echo htmlspecialchars($carrera, ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                            <?php if ($ubicacion): ?>
                                <p><?php echo htmlspecialchars($ubicacion, ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <p>
                            Todavía no hay publicaciones en la plataforma.
                            Sé la primera persona en compartir un proyecto, un logro
                            o una experiencia de la EMI.
                        </p>
                    </div>
                </article>
            <?php else: ?>
                <?php foreach ($publicaciones as $publicacion): ?>
                    <?php
                    $autorNombre = trim(
                        ($publicacion['autor_nombre'] ?? '') . ' ' . ($publicacion['autor_apellidos'] ?? '')
                    );
                    if ($autorNombre === '') {
                        $autorNombre = 'Usuario EMI';
                    }

                    $autorTitulo = $publicacion['autor_titulo'] ?? '';
                    $autorCarrera = $publicacion['autor_carrera'] ?? '';

                    $autorCiudad = $publicacion['autor_ciudad'] ?? '';
                    $autorPais   = $publicacion['autor_pais']   ?? '';
                    $autorUbic   = trim($autorCiudad . ($autorCiudad && $autorPais ? ', ' : '') . $autorPais);

                    $autorFoto = !empty($publicacion['autor_foto'])
                        ? htmlspecialchars($publicacion['autor_foto'], ENT_QUOTES, 'UTF-8')
                        : '/public/img/image.png';
                    ?>
                    <article class="card card-feed-principal">
                        <!-- Header de la card -->
                        <div>
                            <img src="<?php echo $autorFoto; ?>" alt="Usuario">
                            <div>
                                <a href="/perfil.php">
                                    <p><?php echo htmlspecialchars($autorNombre, ENT_QUOTES, 'UTF-8'); ?></p>
                                </a>

                                <?php if ($autorTitulo): ?>
                                    <p><?php echo htmlspecialchars($autorTitulo, ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php elseif ($autorCarrera): ?>
                                    <p><?php echo htmlspecialchars($autorCarrera, ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>

                                <?php if (!empty($publicacion['creado_en'])): ?>
                                    <p><?php echo htmlspecialchars($publicacion['creado_en'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php elseif ($autorUbic): ?>
                                    <p><?php echo htmlspecialchars($autorUbic, ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div>
                            <p>
                                <?php echo nl2br(htmlspecialchars($publicacion['contenido'] ?? '', ENT_QUOTES, 'UTF-8')); ?>
                            </p>

                            <?php if (!empty($publicacion['imagen'])): ?>
                                <img
                                    src="<?php echo htmlspecialchars($publicacion['imagen'], ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="Imagen de la publicación">
                            <?php endif; ?>
                        </div>

                        <!-- Footer de interacciones (solo UI) -->
                        <div class="card-feed-principal-footer">
                            <div class="card-feed-principal-acciones">
                                <button type="button" class="card-feed-btn-like">
                                    <ion-icon name="heart-outline" class="card-feed-like-icon"></ion-icon>
                                    <span>Me gusta</span>
                                </button>
                                <button type="button" class="card-feed-btn-comentar">
                                    <ion-icon name="chatbubble-ellipses-outline"></ion-icon>
                                    <span>Comentar</span>
                                </button>
                                <button type="button" class="card-feed-btn-compartir">
                                    <ion-icon name="share-social-outline"></ion-icon>
                                    <span>Compartir</span>
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </section>
</main>

<script>
    // Abrir sección de publicar
    const btnPublicar = document.getElementById('btn-abrir-publicar');
    if (btnPublicar) {
        btnPublicar.addEventListener('click', () => {
            window.location.href = 'views/principal/c-pu.php';
        });
    }

    // Efecto "me gusta" (solo UI)
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('card-feed-like-icon')) {
            e.target.classList.toggle('is-liked');
        }
    });
</script>