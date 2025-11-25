<?php
// notificaciones.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php'; // si tu session.php ya hace session_start, puedes quitar el session_start de abajo

session_start();

// Validar usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /views/usuario/login.php');
    exit;
}

$usuario_id    = (int)$_SESSION['usuario_id'];
$miNombre      = $_SESSION['nombre'] ?? 'Alguien';
$mensajeAccion = '';
$tipoMensaje   = 'ok';

/* ===========================================================
   1) ACCIONES SOBRE SOLICITUDES DE AMISTAD (aceptar / rechazar)
   =========================================================== */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['accion_solicitud'], $_POST['solicitante_id'])
) {
    $accionSolicitud = $_POST['accion_solicitud'];   // 'aceptar' o 'rechazar'
    $solicitanteId   = (int)$_POST['solicitante_id'];

    try {
        if ($accionSolicitud === 'aceptar') {
            // Aceptar solicitud pendiente
            $upd = $conn->prepare("
                UPDATE solicitudes_amistad
                SET estado = 'aceptada', respondido_en = GETDATE()
                WHERE solicitante_id = :sol
                  AND destinatario_id = :yo
                  AND estado = 'pendiente'
            ");
            $upd->execute([
                ':sol' => $solicitanteId,
                ':yo'  => $usuario_id
            ]);

            if ($upd->rowCount() > 0) {
                // Notificar al que envió la solicitud
                $notif = $conn->prepare("
                    INSERT INTO notificaciones (usuario_id, titulo, mensaje)
                    VALUES (:dest, :titulo, :mensaje)
                ");
                $notif->execute([
                    ':dest'    => $solicitanteId,
                    ':titulo'  => 'Solicitud aceptada',
                    ':mensaje' => $miNombre . ' aceptó tu solicitud de amistad.'
                ]);

                $mensajeAccion = 'Solicitud aceptada. Ahora están conectados.';
                $tipoMensaje   = 'ok';
            } else {
                $mensajeAccion = 'No se encontró una solicitud pendiente de este usuario.';
                $tipoMensaje   = 'error';
            }
        } elseif ($accionSolicitud === 'rechazar') {
            // Rechazar solicitud pendiente
            $upd = $conn->prepare("
                UPDATE solicitudes_amistad
                SET estado = 'rechazada', respondido_en = GETDATE()
                WHERE solicitante_id = :sol
                  AND destinatario_id = :yo
                  AND estado = 'pendiente'
            ");
            $upd->execute([
                ':sol' => $solicitanteId,
                ':yo'  => $usuario_id
            ]);

            if ($upd->rowCount() > 0) {
                $mensajeAccion = 'Solicitud rechazada.';
                $tipoMensaje   = 'ok';
            } else {
                $mensajeAccion = 'No se encontró una solicitud pendiente de este usuario.';
                $tipoMensaje   = 'error';
            }
        }
    } catch (PDOException $e) {
        $mensajeAccion = 'Ocurrió un error al procesar la solicitud de amistad.';
        $tipoMensaje   = 'error';
    }
}

/* ================================================
   2) ACCIONES SOBRE NOTIFICACIONES (leídas / todas)
   ================================================ */
// Marcar todas como leídas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marcar_todas'])) {
    $upd = $conn->prepare("UPDATE notificaciones SET leido = 1 WHERE usuario_id = :uid");
    $upd->execute([':uid' => $usuario_id]);
    $mensajeAccion = "Todas tus notificaciones fueron marcadas como leídas.";
    $tipoMensaje   = 'ok';
}

// (Opcional) marcar una sola como leída
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marcar_una'], $_POST['notif_id'])) {
    $notif_id = (int)$_POST['notif_id'];
    $upd = $conn->prepare("
        UPDATE notificaciones
        SET leido = 1
        WHERE id = :id AND usuario_id = :uid
    ");
    $upd->execute([
        ':id'  => $notif_id,
        ':uid' => $usuario_id
    ]);
    $mensajeAccion = "Notificación actualizada.";
    $tipoMensaje   = 'ok';
}

/* ===================================
   3) CONSULTAR NOTIFICACIONES NORMALES
   =================================== */
$stmt = $conn->prepare("
    SELECT id, titulo, mensaje, leido, creado_en
    FROM notificaciones
    WHERE usuario_id = :uid
    ORDER BY creado_en DESC
");
$stmt->execute([':uid' => $usuario_id]);
$notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==========================================
   4) CONSULTAR SOLICITUDES DE AMISTAD PENDIENTES
   (las que OTROS te mandaron a ti)
   ========================================== */
$stmtSol = $conn->prepare("
    SELECT 
        sa.solicitante_id,
        sa.creado_en,
        u.nombre,
        u.apellidos,
        u.titulo_perfil,
        u.carrera,
        u.ubicacion_ciudad,
        u.ubicacion_pais,
        u.foto
    FROM solicitudes_amistad sa
    INNER JOIN usuarios u ON u.id = sa.solicitante_id
    WHERE sa.destinatario_id = :yo
      AND sa.estado = 'pendiente'
    ORDER BY sa.creado_en DESC
");
$stmtSol->execute([':yo' => $usuario_id]);
$solicitudesPendientes = $stmtSol->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   5) Helper para mostrar "Hace X ..."
   =============================== */
function tiempoRelativo($fecha)
{
    if (!$fecha) return '';
    $ts = strtotime($fecha);
    if ($ts === false) return $fecha;

    $diff = time() - $ts;
    if ($diff < 60) return 'Hace unos segundos';
    $min = floor($diff / 60);
    if ($min < 60) return "Hace {$min} min";
    $horas = floor($min / 60);
    if ($horas < 24) return "Hace {$horas} h";
    $dias = floor($horas / 24);
    if ($dias === 1) return "Ayer";
    if ($dias < 7) return "Hace {$dias} días";
    return date('d/m/Y H:i', $ts);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - LinkedIn EMI</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
</head>

<style>
    /* ===========================
   NOTIFICACIONES - LINKEDIN EMI
   =========================== */

    :root {
        --color-bg: #f3f2ef;
        --color-card: #ffffff;
        --color-border: #d6d6d6;
        --color-text: #1f1f1f;
        --color-text-sec: #6f6f6f;
        --color-primary: #0a66c2;
        --color-primary-soft: #e8f3ff;
        --color-success: #2e7d32;
        --color-error: #c62828;
        --color-badge: #ffb300;
        --radius-card: 10px;
        --shadow-soft: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    /* Layout general */

    body {
        background: var(--color-bg);
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        color: var(--color-text);
    }

    main {
        max-width: 960px;
        margin: 90px auto 40px;
        /* deja espacio para el header fijo si lo usas */
        padding: 0 16px 40px;
    }

    /* Títulos */

    main h1 {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 0.4rem;
    }

    .subtitle {
        font-size: 0.95rem;
        color: var(--color-text-sec);
        margin-bottom: 1.5rem;
    }

    /* Mensaje de acción (éxito / error) */

    .mensaje-accion {
        padding: 0.9rem 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: var(--shadow-soft);
        border-left: 4px solid transparent;
    }

    .mensaje-accion.ok {
        background: #e8f5e9;
        color: var(--color-success);
        border-color: var(--color-success);
    }

    .mensaje-accion.error {
        background: #ffebee;
        color: var(--color-error);
        border-color: var(--color-error);
    }

    /* ===============================
   BLOQUE SOLICITUDES DE CONEXIÓN
   =============================== */

    .solicitudes-box {
        background: var(--color-card);
        border-radius: var(--radius-card);
        padding: 1.25rem 1.4rem;
        box-shadow: var(--shadow-soft);
        border: 1px solid var(--color-border);
        margin-bottom: 1.5rem;
    }

    .solicitudes-box h2 {
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }

    .solicitud-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.8rem 0;
        border-top: 1px solid #eee;
    }

    .solicitud-item:first-of-type {
        border-top: none;
        padding-top: 0;
    }

    .solicitud-item:last-of-type {
        padding-bottom: 0;
    }

    .solicitud-foto img {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e0e0e0;
    }

    .solicitud-info {
        flex: 1;
        font-size: 0.9rem;
    }

    .solicitud-info strong {
        display: inline-block;
        margin-bottom: 0.15rem;
    }

    .solicitud-info span {
        color: var(--color-text-sec);
        font-size: 0.9rem;
    }

    .solicitud-info small {
        color: #9e9e9e;
    }

    .solicitud-acciones {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    /* Botones genéricos */

    button {
        font-family: inherit;
        border: none;
        cursor: pointer;
    }

    /* Botones solicitudes */

    .btn-aceptar,
    .btn-rechazar {
        padding: 0.4rem 0.9rem;
        font-size: 0.85rem;
        border-radius: 999px;
        transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease,
            border-color 0.15s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    .btn-aceptar {
        background: var(--color-primary);
        color: #fff;
    }

    .btn-aceptar:hover {
        background: #004182;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
    }

    .btn-rechazar {
        background: transparent;
        color: var(--color-text-sec);
        border: 1px solid #bdbdbd;
    }

    .btn-rechazar:hover {
        background: #f5f5f5;
    }

    /* =====================
   ACCIONES / RESUMEN
   ===================== */

    .acciones-bar {
        margin-top: 0.5rem;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        font-size: 0.85rem;
        color: var(--color-text-sec);
    }

    .btn-accion {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.45rem 0.9rem;
        border-radius: 999px;
        background: #e3f2fd;
        color: #0d47a1;
        font-size: 0.85rem;
        font-weight: 500;
        border: 1px solid #bbdefb;
        transition: background 0.15s ease, transform 0.1s ease, box-shadow 0.15s ease;
    }

    .btn-accion ion-icon {
        font-size: 1rem;
    }

    .btn-accion:hover {
        background: #bbdefb;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
        transform: translateY(-1px);
    }

    /* =====================
   LISTA DE NOTIFICACIONES
   ===================== */

    .cards-container {
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
    }

    /* Card de notificación */

    .card {
        display: flex;
        gap: 0.9rem;
        background: var(--color-card);
        border-radius: var(--radius-card);
        padding: 0.85rem 1rem;
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-soft);
    }

    /* Diferencia entre leída y no leída */

    .card.unread {
        border-left: 4px solid var(--color-primary);
        background: var(--color-primary-soft);
    }

    .card.read {
        border-left: 4px solid transparent;
    }

    /* Icono */

    .card-icon {
        display: flex;
        align-items: flex-start;
        padding-top: 0.1rem;
    }

    .card-icon ion-icon {
        font-size: 2rem;
        color: var(--color-primary);
    }

    /* Contenido */

    .card-content {
        flex: 1;
        font-size: 0.9rem;
    }

    .card-header-line {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.3rem;
    }

    .card-title {
        font-size: 0.98rem;
        font-weight: 600;
        margin: 0;
    }

    /* Badge "Nuevo" */

    .badge-nuevo {
        padding: 0.1rem 0.55rem;
        border-radius: 999px;
        background: var(--color-badge);
        color: #000;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .card-content p {
        margin: 0.1rem 0 0.35rem;
        line-height: 1.4;
        color: var(--color-text-sec);
    }

    /* Footer de la card */

    .card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        font-size: 0.8rem;
        color: #9e9e9e;
    }

    .card-footer small {
        white-space: nowrap;
    }

    /* Botón mini "marcar como leída" */

    .btn-mini {
        padding: 0.25rem 0.7rem;
        border-radius: 999px;
        border: 1px solid #bdbdbd;
        background: #fafafa;
        font-size: 0.78rem;
        color: var(--color-text-sec);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .btn-mini:hover {
        background: #f0f0f0;
        border-color: #9e9e9e;
        color: #424242;
    }

    /* =====================
   EMPTY STATE
   ===================== */

    .empty-state {
        margin-top: 1.5rem;
        background: var(--color-card);
        border-radius: var(--radius-card);
        padding: 1.4rem 1.6rem;
        border: 1px dashed #cfcfcf;
        text-align: center;
        font-size: 0.95rem;
        color: var(--color-text-sec);
    }

    .empty-state strong {
        display: block;
        margin-bottom: 0.4rem;
        font-size: 1rem;
        color: var(--color-text);
    }

    /* =====================
   RESPONSIVE
   ===================== */

    @media (max-width: 768px) {
        main {
            margin-top: 80px;
        }

        .solicitud-item {
            align-items: flex-start;
            flex-direction: row;
        }

        .solicitud-acciones {
            flex-direction: row;
            margin-left: auto;
        }

        .solicitud-acciones form {
            flex: 1;
        }

        .solicitud-acciones .btn-aceptar,
        .solicitud-acciones .btn-rechazar {
            width: 100%;
        }

        .card {
            padding: 0.8rem 0.85rem;
        }

        .card-icon ion-icon {
            font-size: 1.7rem;
        }

        .acciones-bar {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 480px) {
        .solicitud-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .solicitud-acciones {
            width: 100%;
            margin-left: 0;
        }

        .acciones-bar {
            align-items: stretch;
        }

        .acciones-bar form {
            width: 100%;
        }

        .btn-accion {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<body>

    <?php include 'views/cabeza/header.php'; ?>

    <main>
        <h1>Notificaciones</h1>
        <p class="subtitle">
            Aquí verás las respuestas de tus postulaciones, solicitudes de conexión y avisos importantes de la plataforma.
        </p>

        <?php if ($mensajeAccion): ?>
            <div class="mensaje-accion <?= htmlspecialchars($tipoMensaje, ENT_QUOTES, 'UTF-8'); ?>">
                <?= htmlspecialchars($mensajeAccion, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- 🔔 BLOQUE DE SOLICITUDES DE CONEXIÓN PENDIENTES -->
        <?php if (!empty($solicitudesPendientes)): ?>
            <section class="solicitudes-box">
                <h2>Solicitudes de conexión</h2>
                <?php foreach ($solicitudesPendientes as $s): ?>
                    <?php
                    $foto   = $s['foto'] ?: '/public/img/main.png';
                    $nombre = trim($s['nombre'] . ' ' . $s['apellidos']);
                    $titulo = $s['titulo_perfil'] ?: ($s['carrera'] ?: 'Perfil sin título');
                    $cuando = tiempoRelativo($s['creado_en']);
                    ?>
                    <div class="solicitud-item">
                        <div class="solicitud-foto">
                            <img src="<?= htmlspecialchars($foto, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto de perfil">
                        </div>
                        <div class="solicitud-info">
                            <strong><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?></strong><br>
                            <span><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></span><br>
                            <small><?= htmlspecialchars($cuando, ENT_QUOTES, 'UTF-8'); ?></small>
                        </div>
                        <div class="solicitud-acciones">
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="solicitante_id" value="<?= (int)$s['solicitante_id']; ?>">
                                <button type="submit" name="accion_solicitud" value="aceptar" class="btn-aceptar">
                                    Aceptar
                                </button>
                            </form>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="solicitante_id" value="<?= (int)$s['solicitante_id']; ?>">
                                <button type="submit" name="accion_solicitud" value="rechazar" class="btn-rechazar">
                                    Rechazar
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <div class="acciones-bar">
            <small><?= count($notificaciones) ?> notificación(es)</small>
            <?php if (!empty($notificaciones)): ?>
                <form method="POST" style="margin:0;">
                    <button type="submit" name="marcar_todas" class="btn-accion">
                        <ion-icon name="checkmark-done-outline"></ion-icon>
                        Marcar todas como leídas
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (!empty($notificaciones)): ?>
            <div class="cards-container">
                <?php foreach ($notificaciones as $n): ?>
                    <?php
                    $clase  = $n['leido'] ? 'read' : 'unread';
                    $tiempo = tiempoRelativo($n['creado_en'] ?? '');
                    ?>
                    <div class="card <?= $clase ?>">
                        <div class="card-icon">
                            <ion-icon name="notifications-circle-outline"></ion-icon>
                        </div>
                        <div class="card-content">
                            <div class="card-header-line">
                                <h3 class="card-title">
                                    <?= htmlspecialchars($n['titulo'], ENT_QUOTES, 'UTF-8') ?>
                                </h3>
                                <?php if (!$n['leido']): ?>
                                    <span class="badge-nuevo">Nuevo</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($n['mensaje'])): ?>
                                <p><?= nl2br(htmlspecialchars($n['mensaje'], ENT_QUOTES, 'UTF-8')) ?></p>
                            <?php else: ?>
                                <p>Se ha generado una actualización en tu cuenta.</p>
                            <?php endif; ?>

                            <div class="card-footer">
                                <small><?= htmlspecialchars($tiempo, ENT_QUOTES, 'UTF-8') ?></small>

                                <?php if (!$n['leido']): ?>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="notif_id" value="<?= (int)$n['id'] ?>">
                                        <button type="submit" name="marcar_una" class="btn-mini">
                                            Marcar como leída
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <small>Leída</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <strong>No tienes notificaciones por ahora.</strong>
                Sigue postulando, conectando con empresas y actualizando tu perfil.
                Las novedades aparecerán aquí cuando algo importante suceda.
            </div>
        <?php endif; ?>
    </main>

    <?php include 'views/cabeza/footer.php'; ?>

    <!-- Iconos -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>