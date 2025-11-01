<?php
require_once __DIR__ . '/config/database.php'; // CORRECTO si config está en la raíz

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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empleos</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arvo:wght@400;700&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Básicos para mostrar lado izquierdo y derecho */
        .empleo {
            display: flex;
            gap: 20px;
        }

      

        .plo {
            cursor: pointer;
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px 0;
            display: flex;
            gap: 10px;
        }

        .plo img {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }

        .btn-btn {
            padding: 5px 10px;
            background: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <?php include 'views/cabeza/header.php'; ?>

    <div class="filtros">
        <h2>Filtros de búsqueda</h2>
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
                <label><input type="checkbox" name="jornada[]" value="media" <?= in_array('media', $jornada) ? 'checked' : '' ?>> Media jornada</label>
                <label><input type="checkbox" name="jornada[]" value="remoto" <?= in_array('remoto', $jornada) ? 'checked' : '' ?>> Remoto</label>
            </fieldset>

            <label for="experiencia">Años de experiencia:</label>
            <input type="number" id="experiencia" name="experiencia" min="0" max="50" value="<?= htmlspecialchars($experiencia) ?>">

            <button class="btn-btn" type="submit">Aplicar filtros</button>
        </form>
    </div>

    <main class="empleo">
        <section class="empleo-izquierdo io">
            <div>
                <p>Principales Empleos que te recomienda la Emi</p>
                <p>En función de tu perfil y actividad.</p>
                <p><?= count($ofertas) ?> resultados</p>
            </div>

            <?php foreach ($ofertas as $o): ?>
                <div class="plo oferta-item"
                    data-titulo="<?= htmlspecialchars($o['titulo'], ENT_QUOTES) ?>"
                    data-ubicacion="<?= htmlspecialchars($o['ubicacion'], ENT_QUOTES) ?>"
                    data-modalidad="<?= htmlspecialchars($o['modalidad'], ENT_QUOTES) ?>"
                    data-jornada="<?= htmlspecialchars($o['tipo_jornada'], ENT_QUOTES) ?>"
                    data-empresa="<?= htmlspecialchars($o['empresa'], ENT_QUOTES) ?>"
                    data-logo="<?= $o['logo_empresa'] ?: 'image.png' ?>"
                    data-descripcion="<?= htmlspecialchars($o['descripcion'], ENT_QUOTES) ?>">
                    <img src="/public/img/<?= $o['logo_empresa'] ?: 'main.png' ?>" alt="">
                    <div>
                        <p><?= htmlspecialchars($o['titulo']) ?></p>
                        <p><?= htmlspecialchars($o['empresa']) ?></p>
                        <p><?= htmlspecialchars($o['ubicacion']) ?></p>
                        <p class="btn-btn">Favorito</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="empleo-derecho io" id="detalle-oferta">
            <?php if (!empty($ofertas[0])):
                $o = $ofertas[0]; ?>
                <input type="submit" value="Postularse" class="btn-btn">
                <p id="titulo"><?= htmlspecialchars($o['titulo']) ?></p>
                <p id="ubicacion"><?= htmlspecialchars($o['ubicacion']) ?> · hace 1 mes · Más de 100 solicitudes</p>
                <div>
                    <p id="modalidad"><?= ucfirst($o['modalidad']) ?></p>
                    <p id="jornada">Jornada <?= ucfirst($o['tipo_jornada']) ?></p>
                    <p>Solicitud sencilla</p>
                </div>
                <div>
                    <p>Conoce al equipo de contratación</p>
                    <div>
                        <img id="logo_empresa" src="/public/img/<?= $o['logo_empresa'] ?: 'image.png' ?>" alt="">
                        <div>
                            <p id="empresa"><?= htmlspecialchars($o['empresa']) ?></p>
                            <p>Anunciante del empleo</p>
                        </div>
                    </div>
                </div>
                <p>Acerca del empleo</p>
                <p id="descripcion"><?= nl2br(htmlspecialchars($o['descripcion'])) ?></p>
            <?php else: ?>
                <p>No hay ofertas disponibles.</p>
            <?php endif; ?>
        </section>
    </main>

    <script>
        // Click en oferta para actualizar detalle
        document.querySelectorAll('.oferta-item').forEach(item => {
            item.addEventListener('click', () => {
                document.getElementById('titulo').textContent = item.dataset.titulo;
                document.getElementById('ubicacion').textContent = item.dataset.ubicacion + ' · hace 1 mes · Más de 100 solicitudes';
                document.getElementById('modalidad').textContent = item.dataset.modalidad;
                document.getElementById('jornada').textContent = 'Jornada ' + item.dataset.jornada;
                document.getElementById('empresa').textContent = item.dataset.empresa;
                document.getElementById('logo_empresa').src = '/public/img/' + item.dataset.logo;
                document.getElementById('descripcion').innerHTML = item.dataset.descripcion.replace(/\n/g, "<br>");
            });
        });
    </script>

    <?php include 'views/cabeza/footer.php'; ?>
</body>

</html>