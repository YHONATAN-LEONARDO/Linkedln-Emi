<?php
// postulaciones.php
session_start();
require_once __DIR__ . '/config/database.php';

// Validar usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /views/usuario/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Función para mostrar "hace X tiempo"
function tiempoRelativo($fecha)
{
    if (!$fecha) return '';
    $segundos = time() - strtotime($fecha);

    if ($segundos < 60) {
        return 'hace unos segundos';
    } elseif ($segundos < 3600) {
        $m = intval($segundos / 60);
        return "hace {$m} min";
    } elseif ($segundos < 86400) {
        $h = intval($segundos / 3600);
        return "hace {$h} h";
    } elseif ($segundos < 604800) {
        $d = intval($segundos / 86400);
        return "hace {$d} días";
    } else {
        $s = intval($segundos / 604800);
        return "hace {$s} sem";
    }
}

// Obtener postulaciones del usuario
$stmt = $conn->prepare("
    SELECT 
        p.id,
        p.estado,
        p.creado_en,
        o.titulo AS oferta_titulo,
        o.ubicacion,
        o.modalidad,
        o.tipo_jornada
    FROM postulaciones p
    JOIN ofertas o ON p.oferta_id = o.id
    WHERE p.usuario_id = :usuario_id
    ORDER BY p.creado_en DESC
");
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$postulaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Postulaciones - LinkedIn EMI</title>

    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">

    <style>
        body {
            font-family: "Montserrat", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            padding: 0;
            background: #f3f4f6;
        }

        main.postulacion-container {
            width: 92%;
            max-width: 960px;
            margin: 7rem auto 3rem;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }

        .postulacion-header {
            text-align: center;
            margin-bottom: .5rem;
        }

        .postulacion-header h1 {
            font-size: 2.2rem;
            margin: 0 0 .4rem;
            color: #0f172a;
        }

        .postulacion-header p {
            margin: 0;
            font-size: 1rem;
            color: #6b7280;
        }

        .resumen-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: .8rem;
            font-size: .95rem;
            color: #4b5563;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .resumen-bar strong {
            color: #0f172a;
        }

        .resumen-badge {
            padding: .25rem .7rem;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: .85rem;
            font-weight: 600;
        }

        .cards-wrapper {
            margin-top: .8rem;
            display: flex;
            flex-direction: column;
            gap: .9rem;
        }

        .postulacion-card {
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            padding: 1rem 1.1rem;
            border-radius: 1rem;
            background-color: #ffffff;
            gap: 1rem;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.10);
            border: 1px solid #e5e7eb;
            transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
        }

        .postulacion-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.16);
            border-color: #0ea5e9;
        }

        .postulacion-info {
            flex: 1;
            min-width: 0;
        }

        .postulacion-info p {
            margin: .2rem 0;
            font-size: 1rem;
            color: #111827;
        }

        .postulacion-titulo {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: .15rem;
        }

        .postulacion-sub {
            font-size: .95rem;
            color: #6b7280;
        }

        .postulacion-meta {
            margin-top: .25rem;
            font-size: .9rem;
            color: #4b5563;
        }

        .estado-pill {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            padding: .22rem .8rem;
            border-radius: 999px;
            font-size: .85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .estado-en_revision {
            background: #fef3c7;
            color: #92400e;
        }

        .estado-aceptado {
            background: #dcfce7;
            color: #166534;
        }

        .estado-rechazado {
            background: #fee2e2;
            color: #b91c1c;
        }

        .estado-otro {
            background: #e5e7eb;
            color: #374151;
        }

        .postulacion-actions {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
            gap: .5rem;
            min-width: 160px;
        }

        .postulacion-tiempo {
            font-size: .9rem;
            color: #9ca3af;
        }

        .postulacion-actions form {
            margin: 0;
        }

        .btn-cancelar {
            padding: .7rem 1.2rem;
            font-size: 1rem;
            border: none;
            border-radius: .7rem;
            background-color: #ef4444;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
            transition: background .18s, transform .08s, box-shadow .18s;
        }

        .btn-cancelar:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(248, 113, 113, 0.4);
        }

        .btn-cancelar:active {
            transform: translateY(0);
            box-shadow: none;
        }

        .no-postulaciones {
            margin-top: 1.2rem;
            padding: 1.3rem 1.1rem;
            text-align: center;
            font-size: 1rem;
            border-radius: 1rem;
            background: #f9fafb;
            border: 1px dashed #d1d5db;
            color: #6b7280;
        }

        .no-postulaciones strong {
            display: block;
            margin-bottom: .3rem;
            font-size: 1.05rem;
            color: #111827;
        }

        .motivation-box {
            margin-top: 1.2rem;
            padding: 1rem 1.1rem;
            border-radius: 1rem;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            font-size: .95rem;
            color: #1f2937;
        }

        .motivation-box h2 {
            margin: 0 0 .4rem;
            font-size: 1.2rem;
            color: #1d4ed8;
        }

        .motivation-box p {
            margin: .2rem 0;
        }

        .motivation-box ul {
            margin: .4rem 0 0 1.2rem;
            padding: 0;
            font-size: .9rem;
        }

        .motivation-box li {
            margin-bottom: .2rem;
        }

        /* Letra más grande en móviles */
        @media (max-width: 768px) {
            main.postulacion-container {
                margin-top: 6rem;
            }

            .postulacion-header h1 {
                font-size: 2.3rem;
            }

            .postulacion-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .postulacion-actions {
                width: 100%;
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                min-width: 0;
            }

            .btn-cancelar {
                width: auto;
                font-size: 1.05rem;
            }
        }

        @media (max-width: 480px) {
            main.postulacion-container {
                margin-top: 5.5rem;
            }

            .postulacion-header h1 {
                font-size: 2.4rem;
            }

            .postulacion-titulo {
                font-size: 1.3rem;
            }

            .postulacion-info p,
            .no-postulaciones,
            .motivation-box {
                font-size: 1.02rem;
            }
        }
        /* Aumentar tamaño SOLO dentro de <main> */
        .boy {
            font-size: 18px;
            /* ≈ 20px */
        }

        /* Ajuste automático para pantallas medianas */
        @media (max-width: 768px) {
            main {
                font-size: 1.35rem;
                /* ≈ 22px */
            }
        }

        /* Aún más grande en celulares pequeños */
        @media (max-width: 480px) {
            main {
                font-size: 1.45rem;
                /* ≈ 23px */
            }
        }
    </style>
</head>

<body class="boy" >

    <?php include __DIR__ . '/views/cabeza/header.php'; ?>

    <main class="postulacion-container">
        <div class="postulacion-header">
            <h1>Mis Postulaciones</h1>
            <p>Haz seguimiento a las ofertas a las que te has postulado.</p>

            <div class="resumen-bar">
                <span><strong><?= count($postulaciones) ?></strong> postulaciones encontradas</span>
                <?php if (count($postulaciones) > 0): ?>
                    <span class="resumen-badge">Sigue intentando: cada postulación suma experiencia </span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (count($postulaciones) > 0): ?>
            <div class="cards-wrapper">
                <?php foreach ($postulaciones as $post): ?>
                    <?php
                    $estado = strtolower($post['estado'] ?? 'en_revision');
                    if ($estado === 'en_revision') {
                        $estadoClase = 'estado-en_revision';
                        $estadoTexto = 'En revisión';
                    } elseif ($estado === 'aceptado' || $estado === 'aceptada') {
                        $estadoClase = 'estado-aceptado';
                        $estadoTexto = 'Aceptada';
                    } elseif ($estado === 'rechazado' || $estado === 'rechazada') {
                        $estadoClase = 'estado-rechazado';
                        $estadoTexto = 'Rechazada';
                    } else {
                        $estadoClase = 'estado-otro';
                        $estadoTexto = ucfirst($estado);
                    }
                    ?>
                    <div class="postulacion-card">
                        <div class="postulacion-info">
                            <p class="postulacion-titulo">
                                <?= htmlspecialchars($post['oferta_titulo']) ?>
                            </p>
                            <p class="postulacion-sub">
                                <?= htmlspecialchars($post['ubicacion'] ?: 'Ubicación no especificada') ?>
                                <?php if (!empty($post['modalidad'])): ?>
                                    · <?= htmlspecialchars(ucfirst($post['modalidad'])) ?>
                                <?php endif; ?>
                                <?php if (!empty($post['tipo_jornada'])): ?>
                                    · Jornada <?= htmlspecialchars(ucfirst($post['tipo_jornada'])) ?>
                                <?php endif; ?>
                            </p>

                            <p class="postulacion-meta">
                                Estado:
                                <span class="estado-pill <?= $estadoClase ?>">
                                    <?= $estadoTexto ?>
                                </span>
                            </p>
                        </div>

                        <div class="postulacion-actions">
                            <span class="postulacion-tiempo">
                                <?= tiempoRelativo($post['creado_en']) ?>
                            </span>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="motivation-box">
                <h2>Sigue construyendo tu camino profesional</h2>
                <p>
                    Ver tus postulaciones es el primer paso para mejorar tu estrategia. Cada intento te da información:
                    qué ofertas se mueven más, qué perfiles buscan y cómo puedes destacar.
                </p>
                <ul>
                    <li>Revisa si tu CV está alineado con lo que pide la vacante.</li>
                    <li>Actualiza tu perfil de LinkedIn EMI con proyectos, cursos y certificaciones.</li>
                    <li>Si te rechazan, no es el final: ajusta y vuelve a intentar.</li>
                </ul>
            </div>
        <?php else: ?>
            <div class="no-postulaciones">
                <strong>Aún no tienes postulaciones.</strong>
                Empieza a explorar las convocatorias, postúlate a las que se ajusten a tu perfil
                y verás tus oportunidades aparecer aquí.
            </div>

            <div class="motivation-box">
                <h2>Primer paso: ¡postúlate!</h2>
                <p>
                    Ningún camino profesional empieza con un “no hice nada”. El siguiente clic puede ser el que te lleve
                    a tu primera práctica, trabajo o proyecto importante.
                </p>
                <p>
                    Ve a la sección de <strong>Convocatorias</strong>, elige una oferta que se vea interesante
                    y da el paso. ✨
                </p>
            </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/views/cabeza/footer.php'; ?>

</body>

</html>