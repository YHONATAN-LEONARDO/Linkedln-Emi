<?php
// red.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

session_start();

// 1. Validar usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /views/usuario/login.php');
    exit();
}

$usuario_id = (int)$_SESSION['usuario_id'];
$mensajeAccion = '';
$tipoMensaje = 'ok';

// 2. Obtener mi nombre para usar en notificaciones
$stmtMe = $conn->prepare("SELECT nombre FROM usuarios WHERE id = :id");
$stmtMe->execute([':id' => $usuario_id]);
$miNombre = $stmtMe->fetchColumn() ?: 'Alguien';

// 3. Manejar acciones: enviar, aceptar, rechazar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['otro_id'])) {
    $accion = $_POST['accion'];
    $otro_id = (int)$_POST['otro_id'];

    if ($otro_id === $usuario_id) {
        $mensajeAccion = 'No puedes enviarte solicitud a ti mismo.';
        $tipoMensaje = 'error';
    } else {
        try {
            if ($accion === 'enviar') {

                // ✅ ARREGLO: usar ? en lugar de parámetros repetidos con nombre
                $existe = $conn->prepare("
                    SELECT id, estado, solicitante_id, destinatario_id
                    FROM solicitudes_amistad
                    WHERE 
                        (solicitante_id = ? AND destinatario_id = ?)
                        OR
                        (solicitante_id = ? AND destinatario_id = ?)
                ");
                $existe->execute([
                    $usuario_id, // yo → él
                    $otro_id,
                    $otro_id,    // él → yo
                    $usuario_id
                ]);
                $row = $existe->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    if ($row['estado'] === 'pendiente') {
                        if ((int)$row['solicitante_id'] === $usuario_id) {
                            $mensajeAccion = 'Ya enviaste una solicitud a esta persona. Está pendiente.';
                        } else {
                            $mensajeAccion = 'Esta persona ya te envió una solicitud. Revísala en la red.';
                        }
                    } elseif ($row['estado'] === 'aceptada') {
                        $mensajeAccion = 'Ya están conectados.';
                    } else { // rechazada u otro
                        $mensajeAccion = 'Ya hubo una solicitud antes (estado: ' . $row['estado'] . ').';
                    }
                    $tipoMensaje = 'error';
                } else {
                    // Crear solicitud
                    $ins = $conn->prepare("
                        INSERT INTO solicitudes_amistad (solicitante_id, destinatario_id)
                        VALUES (:yo, :otro)
                    ");
                    $ins->execute([
                        ':yo'   => $usuario_id,
                        ':otro' => $otro_id
                    ]);

                    // Notificación para el destinatario
                    $notif = $conn->prepare("
                        INSERT INTO notificaciones (usuario_id, titulo, mensaje)
                        VALUES (:dest, :titulo, :mensaje)
                    ");
                    $notif->execute([
                        ':dest'    => $otro_id,
                        ':titulo'  => 'Nueva solicitud de conexión',
                        ':mensaje' => $miNombre . ' te ha enviado una solicitud de amistad.'
                    ]);

                    $mensajeAccion = 'Solicitud de conexión enviada.';
                    $tipoMensaje = 'ok';
                }
            } elseif ($accion === 'aceptar') {

                // Aceptar solicitud que ÉL me envió
                $upd = $conn->prepare("
                    UPDATE solicitudes_amistad
                    SET estado = 'aceptada', respondido_en = GETDATE()
                    WHERE solicitante_id = :otro
                      AND destinatario_id = :yo
                      AND estado = 'pendiente'
                ");
                $upd->execute([
                    ':yo'   => $usuario_id,
                    ':otro' => $otro_id
                ]);

                if ($upd->rowCount() > 0) {
                    // Notificación para quien envió
                    $notif = $conn->prepare("
                        INSERT INTO notificaciones (usuario_id, titulo, mensaje)
                        VALUES (:dest, :titulo, :mensaje)
                    ");
                    $notif->execute([
                        ':dest'    => $otro_id,
                        ':titulo'  => 'Solicitud aceptada',
                        ':mensaje' => $miNombre . ' aceptó tu solicitud de amistad.'
                    ]);

                    $mensajeAccion = 'Solicitud aceptada. Ahora están conectados.';
                    $tipoMensaje = 'ok';
                } else {
                    $mensajeAccion = 'No se encontró una solicitud pendiente de esta persona.';
                    $tipoMensaje = 'error';
                }
            } elseif ($accion === 'rechazar') {

                // Rechazar solicitud que ÉL me envió
                $upd = $conn->prepare("
                    UPDATE solicitudes_amistad
                    SET estado = 'rechazada', respondido_en = GETDATE()
                    WHERE solicitante_id = :otro
                      AND destinatario_id = :yo
                      AND estado = 'pendiente'
                ");
                $upd->execute([
                    ':yo'   => $usuario_id,
                    ':otro' => $otro_id
                ]);

                if ($upd->rowCount() > 0) {
                    $mensajeAccion = 'Solicitud rechazada.';
                    $tipoMensaje = 'ok';
                } else {
                    $mensajeAccion = 'No se encontró una solicitud pendiente de esta persona.';
                    $tipoMensaje = 'error';
                }
            }
        } catch (PDOException $e) {
            // Si es error de constraint (duplicada, FK, etc.)
            if ($e->getCode() === '23000') {
                $mensajeAccion = 'Ya existe una solicitud o conexión con esta persona.';
            } else {
                // Si quieres ver el error real mientras pruebas, descomenta la siguiente línea:
                // $mensajeAccion = 'Error DB: ' . $e->getMessage();
                $mensajeAccion = 'Ocurrió un error con la base de datos.';
            }
            $tipoMensaje = 'error';
        }
    }
}

// 4. Listar otros usuarios (SOLO POSTULANTES, rol_id = 3) + estado de relación
$sql = "
SELECT 
    u.id,
    u.nombre,
    u.apellidos,
    u.titulo_perfil,
    u.carrera,
    u.ubicacion_ciudad,
    u.ubicacion_pais,
    u.foto,
    sa.id AS solicitud_id,
    sa.estado,
    sa.solicitante_id,
    sa.destinatario_id
FROM usuarios u
LEFT JOIN solicitudes_amistad sa
    ON (
            (sa.solicitante_id = ? AND sa.destinatario_id = u.id)
         OR (sa.solicitante_id = u.id AND sa.destinatario_id = ?)
       )
WHERE u.id <> ?
  AND u.rol_id = 3   -- SOLO POSTULANTES
ORDER BY u.nombre;
";

$stmt = $conn->prepare($sql);
$stmt->execute([$usuario_id, $usuario_id, $usuario_id]);

$personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Red de contactos - LinkedIn EMI</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            font-family: "Montserrat", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f3f4f6;
            margin: 0;
            color: #111827;
        }

        main {
            max-width: 1000px;
            margin: 8rem auto 3rem;
            padding: 0 1.5rem;
        }

        h1 {
            font-size: 2.4rem;
            margin-bottom: .5rem;
        }

        .subtitle {
            color: #6b7280;
            margin-bottom: 1.5rem;
        }

        .mensaje-accion {
            margin-bottom: 1.3rem;
            padding: .9rem 1.1rem;
            border-radius: .8rem;
            font-size: 1.4rem;
        }

        .mensaje-accion.ok {
            background: #ecfdf3;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .mensaje-accion.error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .lista-personas {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .card-persona {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            padding: 1.1rem 1.3rem;
            background: #ffffff;
            border-radius: 1.1rem;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
        }

        .card-persona-img img {
            width: 60px;
            height: 60px;
            border-radius: 999px;
            object-fit: cover;
        }

        .card-persona-info {
            flex: 1;
        }

        .card-persona-info strong {
            font-size: 1.5rem;
        }

        .card-persona-info .perfil {
            font-size: 1.3rem;
            color: #4b5563;
        }

        .card-persona-info .extra {
            font-size: 1.2rem;
            color: #9ca3af;
        }

        .estado {
            font-size: 1.2rem;
            margin-top: .3rem;
        }

        .estado span {
            font-weight: 600;
        }

        .acciones {
            text-align: right;
            min-width: 160px;
        }

        .btn {
            border: none;
            cursor: pointer;
            border-radius: 999px;
            padding: .45rem 1rem;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: .3rem;
        }

        .btn-primario {
            background: #0ea5e9;
            color: #fff;
        }

        .btn-primario:hover {
            background: #0284c7;
        }

        .btn-secundario {
            background: #e5e7eb;
            color: #111827;
        }

        .btn-secundario:hover {
            background: #d1d5db;
        }

        .pill-estado {
            display: inline-block;
            padding: .2rem .7rem;
            border-radius: 999px;
            font-size: 1.1rem;
            background: #eff6ff;
            color: #1d4ed8;
            margin-bottom: .4rem;
        }

        .pill-amigos {
            background: #dcfce7;
            color: #15803d;
        }

        .pill-rechazada {
            background: #fee2e2;
            color: #b91c1c;
        }

        .empty-state {
            margin-top: 1.5rem;
            padding: 1.4rem 1.3rem;
            border-radius: 1rem;
            border: 1px dashed #d1d5db;
            background: #f9fafb;
            text-align: center;
            color: #6b7280;
        }

        @media (max-width: 700px) {
            main {
                margin-top: 6rem;
            }

            .card-persona {
                flex-direction: column;
                align-items: flex-start;
            }

            .acciones {
                width: 100%;
                text-align: left;
            }
        }
    </style>
</head>


<body>

    <?php include __DIR__ . '/views/cabeza/header.php'; ?>

    <main style="margin-top: 14rem;">
        <h1>Red de contactos</h1>
        <p class="subtitle">
            Conecta con otros estudiantes y egresados de la EMI. Envía solicitudes y construye tu red profesional.
        </p>

        <?php if ($mensajeAccion): ?>
            <div class="mensaje-accion <?= htmlspecialchars($tipoMensaje, ENT_QUOTES, 'UTF-8'); ?>">
                <?= htmlspecialchars($mensajeAccion, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($personas)): ?>
            <div class="lista-personas">
                <?php foreach ($personas as $p): ?>
                    <?php
                    $estado = $p['estado'] ?? null;
                    $esSolicitanteYo = ($p['solicitante_id'] == $usuario_id);
                    $esDestinatarioYo = ($p['destinatario_id'] == $usuario_id);

                    $textoEstado = 'Sin conexión';
                    $clasePill = '';
                    $accionesHtml = '';

                    if ($estado === 'aceptada') {
                        $textoEstado = 'Conectados';
                        $clasePill = 'pill-amigos';
                    } elseif ($estado === 'pendiente') {
                        if ($esSolicitanteYo) {
                            $textoEstado = 'Solicitud enviada (pendiente)';
                            $clasePill = 'pill-estado';
                        } elseif ($esDestinatarioYo) {
                            $textoEstado = 'Te envió una solicitud';
                            $clasePill = 'pill-estado';
                            ob_start();
                    ?>
                            <form method="POST" style="margin:0 0 .3rem 0;">
                                <input type="hidden" name="otro_id" value="<?= (int)$p['id']; ?>">
                                <button type="submit" name="accion" value="aceptar" class="btn btn-primario">
                                    Aceptar
                                </button>
                            </form>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="otro_id" value="<?= (int)$p['id']; ?>">
                                <button type="submit" name="accion" value="rechazar" class="btn btn-secundario">
                                    Rechazar
                                </button>
                            </form>
                        <?php
                            $accionesHtml = ob_get_clean();
                        }
                    } elseif ($estado === 'rechazada') {
                        $textoEstado = 'Solicitud rechazada';
                        $clasePill = 'pill-rechazada';
                    }

                    // Si no hay relación -> botón para enviar solicitud
                    if ($estado === null) {
                        ob_start();
                        ?>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="otro_id" value="<?= (int)$p['id']; ?>">
                            <button type="submit" name="accion" value="enviar" class="btn btn-primario">
                                Conectar
                            </button>
                        </form>
                    <?php
                        $accionesHtml = ob_get_clean();
                    }

                    $foto = $p['foto'] ?: '/public/img/main.png';
                    $nombreCompleto = trim($p['nombre'] . ' ' . $p['apellidos']);
                    $titulo = $p['titulo_perfil'] ?: ($p['carrera'] ?: 'Perfil sin título');
                    $ubicacion = trim(($p['ubicacion_ciudad'] ?: '') . ', ' . ($p['ubicacion_pais'] ?: ''));
                    ?>
                    <article class="card-persona">
                        <div class="card-persona-img">
                            <img src="<?= htmlspecialchars($foto, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto de perfil">
                        </div>
                        <div class="card-persona-info">
                            <strong><?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8'); ?></strong><br>
                            <span class="perfil"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></span><br>
                            <?php if ($ubicacion !== ','): ?>
                                <span class="extra"><?= htmlspecialchars($ubicacion, ENT_QUOTES, 'UTF-8'); ?></span><br>
                            <?php endif; ?>
                            <div class="estado">
                                <span class="pill-estado <?= $clasePill; ?>">
                                    <?= htmlspecialchars($textoEstado, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="acciones">
                            <?= $accionesHtml; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                No se encontraron otros usuarios por ahora. Cuando haya más registros, aparecerán aquí para que puedas conectar.
            </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/views/cabeza/footer.php'; ?>

</body>

</html>