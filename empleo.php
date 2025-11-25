<?php
require_once __DIR__ . '/config/database.php';
session_start();

// Validar usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /views/usuario/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = '';

// Procesar postulación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['oferta_id'])) {
    $oferta_id = intval($_POST['oferta_id']);

    // Verificar si ya postuló
    $check = $conn->prepare("
        SELECT 1 
        FROM postulaciones 
        WHERE usuario_id = :usuario_id 
          AND oferta_id = :oferta_id
    ");
    $check->execute([
        ':usuario_id' => $usuario_id,
        ':oferta_id'  => $oferta_id
    ]);

    if ($check->rowCount() == 0) {
        $stmt = $conn->prepare("
            INSERT INTO postulaciones (usuario_id, oferta_id, estado, creado_en) 
            VALUES (:usuario_id, :oferta_id, 'en_revision', GETDATE())
        ");
        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':oferta_id'  => $oferta_id
        ]);
        $mensaje = "Postulación realizada correctamente. ¡Mucho éxito!";
    } else {
        $mensaje = "Ya te postulaste a esta oferta.";
    }
}

// FILTROS
$ubicacion   = $_GET['ubicacion'] ?? '';
$jornada     = $_GET['jornada'] ?? [];
$experiencia = $_GET['experiencia'] ?? '';

// Construir query dinámico (solo parámetros nombrados)
$sql = "SELECT o.*, u.nombre AS empresa, u.foto AS logo_empresa
        FROM ofertas o
        JOIN usuarios u ON o.usuario_id = u.id
        WHERE 1=1";

$params = [];

if ($ubicacion !== '') {
    $sql .= " AND o.ubicacion LIKE :ubicacion";
    $params[':ubicacion'] = "%$ubicacion%";
}

if (!empty($jornada)) {
    $placeholders = [];
    foreach ($jornada as $i => $valor) {
        $ph = ":jornada{$i}";
        $placeholders[] = $ph;
        $params[$ph] = $valor;
    }
    $sql .= " AND o.tipo_jornada IN (" . implode(',', $placeholders) . ")";
}

if ($experiencia !== '') {
    $sql .= " AND o.experiencia_min <= :experiencia";
    $params[':experiencia'] = $experiencia;
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empleos - LinkedIn EMI</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">

</head>
<style>
    :root {
        --azul-principal: #007bff;
        --azul-oscuro: #005fcc;
        --azul-suave: #eff6ff;
        --gris-fondo: #f3f4f6;
        --gris-borde: #e5e7eb;
        --texto-principal: #111827;
        --texto-suave: #6b7280;
    }





    a {
        text-decoration: none;
        color: inherit;
    }

    main {
        max-width: 120rem;
        margin: 0 auto 3rem;
    }

    /* TITULO PRINCIPAL DE LA PÁGINA (SI LO USAS) */
    .titulo-empleo {
        margin: 2rem 1rem 0.5rem;
        font-size: 2rem;
        font-weight: 700;
        color: var(--texto-principal);
    }

    .subtitulo-empleo {
        margin: 0 1rem 1.5rem;
        font-size: 1.3rem;
        color: var(--texto-suave);
    }

    /* MENSAJE (AL POSTULAR) */
    .mensaje {
        margin: 1rem;
        padding: .8rem 1rem;
        border-radius: .6rem;
        font-weight: 500;
        background: #e6f6e6;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    /* FILTROS SUPERIORES */
    .filtros {
        max-width: 120rem;
        margin: 0 auto;
        padding: 1.2rem 1.4rem;
        background: #f9fafb;
        border-radius: .8rem;
        border: 1px solid var(--gris-borde);
        box-shadow: 0 4px 14px rgba(15, 23, 42, .05);
        margin-top: 1rem;
    }

    .filtros form {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 2rem;
        align-items: center;
    }

    .filtros label {
        font-size: 1.2rem;
        font-weight: 500;
        color: var(--texto-principal);
    }

    .filtros select,
    .filtros input[type="number"] {
        padding: .35rem .7rem;
        border-radius: .4rem;
        border: 1px solid #d1d5db;
        font-size: 1.3rem;
        background-color: #ffffff;
        outline: none;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .filtros select:focus,
    .filtros input[type="number"]:focus {
        border-color: var(--azul-principal);
        box-shadow: 0 0 0 2px rgba(59, 130, 246, .2);
    }

    fieldset {
        border: none;
        padding: 0;
        margin: 0;
    }

    fieldset legend {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--texto-principal);
        margin-bottom: .2rem;
    }

    fieldset label {
        font-weight: 400;
        margin-right: 1rem;
        font-size: 1.2rem;
        color: var(--texto-suave);
    }

    /* BOTÓN GENERAL */
    .btn-btn {
        padding: .6rem 1.4rem;
        background: var(--azul-principal);
        color: #fff;
        border: none;
        cursor: pointer;
        border-radius: .5rem;
        font-weight: 600;
        font-size: 1.3rem;
        box-shadow: 0 6px 16px rgba(37, 99, 235, .3);
        transition: background .2s ease, transform .1s ease, box-shadow .2s ease;
    }

    .btn-btn:hover {
        background: var(--azul-oscuro);
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(37, 99, 235, .35);
    }

    .btn-btn:active {
        transform: translateY(0);
        box-shadow: 0 4px 10px rgba(37, 99, 235, .25);
    }

    /* LAYOUT PRINCIPAL DE EMPLEOS */
    .empleo {
        margin-top: 5rem;
        display: flex;
        gap: 2rem;
        align-items: flex-start;
        margin: 0 auto;
    }

    .empleo-izquierdo,
    .empleo-derecho {
        flex: 1;
    }

    /* TARJETAS DE LA LISTA DE OFERTAS (IZQUIERDA) */
    .plo {
        cursor: pointer;
        border: 1px solid #ddd;
        padding: 1rem;
        margin-bottom: 1rem;
        display: flex;
        gap: 1rem;
        border-radius: .8rem;
        background: #ffffff;
        transition: box-shadow .2s ease, transform .1s ease, border-color .2s ease, background .2s ease;
    }

    .plo:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, .06);
        transform: translateY(-2px);
        border-color: var(--azul-principal);
        background: #f9fafb;
    }

    .plo img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: .6rem;
        border: 1px solid #e5e7eb;
        flex-shrink: 0;
    }

    .plo p {
        margin: 0;
        font-size: 1.3rem;
    }

    .plo p:first-child {
        font-weight: 600;
        margin-bottom: .2rem;
        color: var(--texto-principal);
    }

    .plo p:nth-child(2) {
        color: var(--texto-suave);
        font-size: 1.2rem;
    }

    .plo p:nth-child(3) {
        font-size: 1.2rem;
        color: #4b5563;
    }

    /* PANEL DERECHO: DETALLE DE OFERTA */
    .empleo-derecho {
        background: #ffffff;
        border-radius: 1rem;
        padding: 1.6rem 1.8rem;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .08);
        position: sticky;
        top: 1.5rem;
    }

    .detalle-header {
        display: flex;
        align-items: center;
        gap: 1.2rem;
        margin-bottom: 1.2rem;
    }

    .detalle-header img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: .8rem;
        border: 1px solid #e5e7eb;
        background-color: #f9fafb;
    }

    .detalle-header h2 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--texto-principal);
    }

    .detalle-subinfo {
        font-size: 1.2rem;
        color: var(--texto-suave);
        margin-top: .2rem;
    }

    .detalle-tags {
        margin: .5rem 0 1.2rem;
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
    }

    .tag {
        background: var(--azul-suave);
        color: #1d4ed8;
        border-radius: 999px;
        padding: .3rem .8rem;
        font-size: 1.1rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: .2rem;
    }

    .tag::before {
        content: "●";
        font-size: .7rem;
    }

    .detalle-boton {
        margin-bottom: 1.2rem;
    }

    .detalle-descripcion {
        margin-top: .8rem;
        font-size: 1.35rem;
        line-height: 1.7;
        color: #111827;
    }

    .detalle-descripcion h3 {
        font-size: 1.5rem;
        margin-bottom: .4rem;
    }

    /* TARJETA MOTIVACIONAL */
    .motivation-card {
        margin-top: 1.8rem;
        padding: 1.1rem 1.3rem;
        border-radius: 1rem;
        background: linear-gradient(135deg, #eff6ff 0%, #ecfeff 50%, #f0fdf4 100%);
        border: 1px solid #dbeafe;
        position: relative;
        overflow: hidden;
    }

    .motivation-card::before {
        content: "";
        position: absolute;
        inset: -40%;
        background: radial-gradient(circle at top left, rgba(59, 130, 246, 0.15), transparent 60%);
        opacity: 0.8;
        pointer-events: none;
    }

    .motivation-card>* {
        position: relative;
        z-index: 1;
    }

    .motivation-card h3 {
        margin: 0 0 .5rem;
        font-size: 1.5rem;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: .6rem;
    }

    .motivation-card h3::before {
        content: "🚀";
        font-size: 1.6rem;
    }

    .motivation-card p {
        margin: 0 0 .5rem;
        font-size: 1.3rem;
        color: #1f2933;
    }

    .motivation-card ul {
        margin: .4rem 0 0 1.4rem;
        padding: 0;
        font-size: 1.25rem;
        color: #111827;
    }

    .motivation-card li {
        margin-bottom: .2rem;
    }

    .motivation-small {
        margin-top: .8rem;
        font-size: 1.15rem;
        color: #6b7280;
    }

    @media (max-width: 900px) {
        .empleo {
            flex-direction: column;
        }

        .empleo-derecho {
            position: static;
            margin-top: 1rem;
        }

        .filtros form {
            align-items: flex-start;
        }
    }

    @media (max-width: 600px) {
        .filtros {
            padding: .9rem 1rem;
        }

        .empleo {
            margin: 0 .8rem 2rem;
        }

        .empleo-derecho {
            padding: 1.2rem 1.3rem;
        }

        .detalle-header h2 {
            font-size: 1.6rem;
        }
    }
</style>

<body>

    <?php include 'views/cabeza/header.php'; ?>

    <!-- ANULAR HEADER FIJO SOLO PARA ESTE ARCHIVO -->
    <style>
        .nav-principal {
            position: static !important;
            top: auto !important;
            left: auto !important;
            right: auto !important;
        }

        body {
            padding-top: 0 !important;
        }
    </style>

    <?php if ($mensaje): ?>
        <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="filtros">
        <form method="GET">
            <div>
                <label for="ubicacion">Ubicación:</label><br>
                <select id="ubicacion" name="ubicacion">
                    <option value="">Todas</option>
                    <option value="Bolivia" <?= $ubicacion == 'Bolivia' ? 'selected' : '' ?>>Bolivia</option>
                    <option value="Perú" <?= $ubicacion == 'Perú' ? 'selected' : '' ?>>Perú</option>
                    <option value="Chile" <?= $ubicacion == 'Chile' ? 'selected' : '' ?>>Chile</option>
                </select>
            </div>

            <fieldset>
                <legend>Jornada:</legend>
                <label><input type="checkbox" name="jornada[]" value="completa" <?= in_array('completa', $jornada) ? 'checked' : '' ?>> Completa</label>
                <label><input type="checkbox" name="jornada[]" value="media" <?= in_array('media', $jornada) ? 'checked' : '' ?>> Media</label>
                <label><input type="checkbox" name="jornada[]" value="remoto" <?= in_array('remoto', $jornada) ? 'checked' : '' ?>> Remoto</label>
            </fieldset>

            <div>
                <label for="experiencia">Años experiencia:</label><br>
                <input type="number" name="experiencia" id="experiencia" value="<?= htmlspecialchars($experiencia) ?>">
            </div>

            <div>
                <button class="btn-btn" type="submit">Filtrar</button>
            </div>
        </form>
    </div>

    <main class="empleo">
        <!-- LISTA IZQUIERDA -->
        <section class="empleo-izquierdo io">
            <?php if (!empty($ofertas)): ?>
                <?php foreach ($ofertas as $o): ?>
                    <div class="plo oferta-item"
                        data-id="<?= $o['id'] ?>"
                        data-titulo="<?= htmlspecialchars($o['titulo'], ENT_QUOTES) ?>"
                        data-ubicacion="<?= htmlspecialchars($o['ubicacion'], ENT_QUOTES) ?>"
                        data-modalidad="<?= htmlspecialchars($o['modalidad'], ENT_QUOTES) ?>"
                        data-jornada="<?= htmlspecialchars($o['tipo_jornada'], ENT_QUOTES) ?>"
                        data-empresa="<?= htmlspecialchars($o['empresa'], ENT_QUOTES) ?>"
                        data-logo="<?= $o['imagen_empresa'] ?: 'main.png' ?>"
                        data-descripcion="<?= htmlspecialchars($o['descripcion'], ENT_QUOTES) ?>">

                        <img src="/public/img/<?= $o['imagen_empresa'] ?: 'main.png'; ?>" alt="Logo empresa">

                        <div>
                            <p><?= htmlspecialchars($o['titulo']) ?></p>
                            <p><?= htmlspecialchars($o['empresa']) ?></p>
                            <p><?= htmlspecialchars($o['ubicacion']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay ofertas disponibles por el momento.</p>
            <?php endif; ?>
        </section>

        <!-- PANEL DERECHO: DETALLE + MOTIVACIÓN -->
        <section class="empleo-derecho io" id="detalle-oferta">
            <?php if (!empty($ofertas[0])):
                $o = $ofertas[0]; ?>
                <div class="detalle-header">
                    <img id="logo_empresa" src="/public/img/<?= $o['imagen_empresa'] ?: 'main.png' ?>" alt="Logo empresa">
                    <div>
                        <h2 id="titulo"><?= htmlspecialchars($o['titulo']) ?></h2>
                        <div class="detalle-subinfo">
                            <span id="empresa"><?= htmlspecialchars($o['empresa']) ?></span> ·
                            <span id="ubicacion"><?= htmlspecialchars($o['ubicacion']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="detalle-tags">
                    <span class="tag" id="modalidad_tag"><?= ucfirst($o['modalidad']) ?: 'Modalidad no especificada' ?></span>
                    <span class="tag" id="jornada_tag">Jornada <?= $o['tipo_jornada'] ? ucfirst($o['tipo_jornada']) : 'no especificada' ?></span>
                </div>

                <div class="detalle-boton">
                    <form method="POST">
                        <input type="hidden" name="oferta_id" id="oferta_id_hidden" value="<?= $o['id'] ?>">
                        <button type="submit" class="btn-btn">Postularse</button>
                    </form>
                </div>

                <div class="detalle-descripcion">
                    <h3>Descripción del puesto</h3>
                    <p id="descripcion"><?= nl2br(htmlspecialchars($o['descripcion'])) ?></p>
                </div>
            <?php else: ?>
                <h2>Explora las oportunidades</h2>
                <p>No hay ofertas disponibles en este momento, pero puedes seguir mejorando tu perfil para estar listo cuando aparezcan nuevas oportunidades.</p>
            <?php endif; ?>

            <!-- BLOQUE MOTIVACIONAL DINÁMICO -->
            <div class="motivation-dynamic" id="motivationBox">
                <h3 id="motivationTitle">Sigue construyendo tu futuro ✨</h3>
                <p id="motivationText">
                    Cada paso que das te acerca más a tu meta profesional. Confía en tu proceso.
                </p>
            </div>

            <style>
                .motivation-dynamic {
                    background: linear-gradient(135deg, #e0f2fe, #f0f9ff);
                    border-left: 5px solid #0284c7;
                    padding: 1rem 1.2rem;
                    border-radius: .8rem;
                    margin-top: 1.2rem;
                    box-shadow: 0 3px 10px rgba(0, 0, 0, .08);
                    transition: transform .3s ease, box-shadow .3s ease;
                }

                .motivation-dynamic:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 6px 20px rgba(0, 0, 0, .12);
                }

                .motivation-dynamic h3 {
                    margin: 0 0 .4rem;
                    font-size: 1.2rem;
                    color: #0f172a;
                }

                .motivation-dynamic p {
                    margin: 0;
                    font-size: .95rem;
                    color: #334155;
                    line-height: 1.5;
                }
            </style>

            <script>
                const motivationMessages = [{
                        title: "Sigue construyendo tu futuro ✨",
                        text: "Cada paso que das te acerca más a tus metas profesionales. Confía en tu proceso."
                    },
                    {
                        title: "Tu talento tiene valor 💼",
                        text: "No dudes en mostrar lo que sabes. Cada oportunidad es una puerta que puedes abrir."
                    },
                    {
                        title: "No te rindas 🚀",
                        text: "Las grandes oportunidades llegan para quienes siguen adelante incluso cuando es difícil."
                    },
                    {
                        title: "Cree en ti 🔥",
                        text: "Tus habilidades, tu esfuerzo y tu disciplina te van a llevar más lejos de lo que imaginas."
                    },
                    {
                        title: "Estás progresando 📈",
                        text: "Revisar ofertas y postular ya es avanzar. Mantén el ritmo y llegarás muy lejos."
                    }
                ];

                let index = 0;
                setInterval(() => {
                    index = (index + 1) % motivationMessages.length;
                    document.getElementById("motivationTitle").textContent = motivationMessages[index].title;
                    document.getElementById("motivationText").textContent = motivationMessages[index].text;
                }, 6000);
            </script>

        </section>
    </main>

    <script>
        // Cambiar el detalle al hacer clic en una oferta
        document.querySelectorAll('.oferta-item').forEach(item => {
            item.addEventListener('click', () => {
                const titulo = item.dataset.titulo;
                const ubicacion = item.dataset.ubicacion;
                const modalidad = item.dataset.modalidad || 'No especificada';
                const jornada = item.dataset.jornada || 'No especificada';
                const empresa = item.dataset.empresa;
                const logo = item.dataset.logo || 'main.png';
                const descripcion = item.dataset.descripcion || '';
                const id = item.dataset.id;

                document.getElementById('titulo').textContent = titulo;
                document.getElementById('ubicacion').textContent = ubicacion;
                document.getElementById('modalidad_tag').textContent = modalidad;
                document.getElementById('jornada_tag').textContent = 'Jornada ' + jornada;
                document.getElementById('empresa').textContent = empresa;
                document.getElementById('logo_empresa').src = '/public/img/' + logo;

                document.getElementById('descripcion').innerHTML = descripcion.replace(/\n/g, "<br>");

                // actualizar el input hidden del formulario de postulación
                const hiddenInput = document.getElementById('oferta_id_hidden');
                if (hiddenInput) {
                    hiddenInput.value = id;
                }
            });
        });
    </script>

    <?php include 'views/cabeza/footer.php'; ?>
</body>

</html>