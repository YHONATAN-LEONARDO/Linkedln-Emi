<?php
require_once __DIR__ . '/config/database.php'; // Ajusta la ruta según tu proyecto
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
    $check = $conn->prepare("SELECT * FROM postulaciones WHERE usuario_id = :usuario_id AND oferta_id = :oferta_id");
    $check->execute([':usuario_id' => $usuario_id, ':oferta_id' => $oferta_id]);

    if ($check->rowCount() == 0) {
        $stmt = $conn->prepare("INSERT INTO postulaciones (usuario_id, oferta_id, estado, creado_en) VALUES (:usuario_id, :oferta_id, 'en_revision', GETDATE())");
        $stmt->execute([':usuario_id' => $usuario_id, ':oferta_id' => $oferta_id]);
        $mensaje = "Postulación realizada correctamente.";
    } else {
        $mensaje = "Ya te postulaste a esta oferta.";
    }
}

// FILTROS
$ubicacion = $_GET['ubicacion'] ?? '';
$jornada = $_GET['jornada'] ?? [];
$experiencia = $_GET['experiencia'] ?? '';

// Construir query dinámico
$sql = "SELECT o.*, u.nombre AS empresa, u.foto AS logo_empresa
        FROM ofertas o
        JOIN usuarios u ON o.usuario_id = u.id
        WHERE 1=1";

$params = [];

if (!empty($ubicacion)) {
    $sql .= " AND o.ubicacion LIKE :ubicacion";
    $params[':ubicacion'] = "%$ubicacion%";
}

if (!empty($jornada)) {
    $sql .= " AND o.tipo_jornada IN (" . implode(',', array_fill(0, count($jornada), '?')) . ")";
    $params = array_merge($params, $jornada);
}

if (!empty($experiencia)) {
    $sql .= " AND o.experiencia_min <= ?";
    $params[] = $experiencia;
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
    <title>Empleos</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
    <style>
        .empleo {
            display: flex;
            gap: 2rem;
        }

        .empleo-izquierdo,
        .empleo-derecho {
            flex: 1;
        }

        .plo {
            cursor: pointer;
            border: 1px solid #ddd;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            gap: 10px;
        }

        .plo img {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }

        .btn-btn {
            padding: .5rem 1rem;
            background: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: .3rem;
        }

        .mensaje {
            margin: 1rem 0;
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>
    
    <?php include 'views/cabeza/header.php'; ?>

    <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>

    <div class="filtros">
        <form method="GET">
            <label for="ubicacion">Ubicación:</label>
            <select id="ubicacion" name="ubicacion">
                <option value="">Todas</option>
                <option value="Bolivia" <?= $ubicacion == 'Bolivia' ? 'selected' : '' ?>>Bolivia</option>
                <option value="Perú" <?= $ubicacion == 'Perú' ? 'selected' : '' ?>>Perú</option>
                <option value="Chile" <?= $ubicacion == 'Chile' ? 'selected' : '' ?>>Chile</option>
            </select>

            <fieldset>
                <legend>Jornada:</legend>
                <label><input type="checkbox" name="jornada[]" value="completa" <?= in_array('completa', $jornada) ? 'checked' : '' ?>> Completa</label>
                <label><input type="checkbox" name="jornada[]" value="media" <?= in_array('media', $jornada) ? 'checked' : '' ?>> Media</label>
                <label><input type="checkbox" name="jornada[]" value="remoto" <?= in_array('remoto', $jornada) ? 'checked' : '' ?>> Remoto</label>
            </fieldset>

            <label for="experiencia">Años experiencia:</label>
            <input type="number" name="experiencia" id="experiencia" value="<?= htmlspecialchars($experiencia) ?>">

            <button class="btn-btn" type="submit">Filtrar</button>
        </form>
    </div>

    <main class="empleo">
        <section class="empleo-izquierdo io">
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

                    <img src="/public/img/<?php echo $o['imagen_empresa'] ?: 'main.png'; ?>" alt="">


                    <div>
                        <p><?= htmlspecialchars($o['titulo']) ?></p>
                        <p><?= htmlspecialchars($o['empresa']) ?></p>
                        <p><?= htmlspecialchars($o['ubicacion']) ?></p>
                    </div>
                </div>

            <?php endforeach; ?>
        </section>

        <section class="empleo-derecho io" id="detalle-oferta">
            <?php if (!empty($ofertas[0])):
                $o = $ofertas[0]; ?>
                <form method="POST">
                    <input type="hidden" name="oferta_id" value="<?= $o['id'] ?>">
                    <button type="submit" class="btn-btn">Postularse</button>
                </form>
                <h2 id="titulo"><?= htmlspecialchars($o['titulo']) ?></h2>
                <p id="ubicacion"><?= htmlspecialchars($o['ubicacion']) ?></p>
                <p id="modalidad"><?= ucfirst($o['modalidad']) ?></p>
                <p id="jornada">Jornada <?= ucfirst($o['tipo_jornada']) ?></p>
                <h3 id="empresa"><?= htmlspecialchars($o['empresa']) ?></h3>
<img id="logo_empresa" src="/public/img/<?= $o['imagen_empresa'] ?: 'main.png' ?>" width="100">

                <p id="descripcion"><?= nl2br(htmlspecialchars($o['descripcion'])) ?></p>
            <?php else: ?>
                <p>No hay ofertas disponibles.</p>
            <?php endif; ?>
        </section>
    </main>

    <script>
        document.querySelectorAll('.oferta-item').forEach(item => {
    item.addEventListener('click', () => {
        // actualizar texto y detalles
        document.getElementById('titulo').textContent = item.dataset.titulo;
        document.getElementById('ubicacion').textContent = item.dataset.ubicacion;
        document.getElementById('modalidad').textContent = item.dataset.modalidad;
        document.getElementById('jornada').textContent = 'Jornada ' + item.dataset.jornada;
        document.getElementById('empresa').textContent = item.dataset.empresa;
       document.getElementById('logo_empresa').src = '/public/img/' + item.dataset.logo;

        document.getElementById('descripcion').innerHTML = item.dataset.descripcion.replace(/\n/g, "<br>");

        // actualizar el input del formulario
        document.querySelector('form button[type="submit"]').previousElementSibling.value = item.dataset.id;
    });
});

    </script>

    <?php include 'views/cabeza/footer.php'; ?>
</body>

</html>