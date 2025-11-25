<?php
// convocatoria.php

session_start();

// incluye helpers / DB / session
require_once __DIR__ . '/config/database.php';    // debe definir $conn (PDO)
if (file_exists(__DIR__ . '/config/session.php')) {
    require_once __DIR__ . '/config/session.php';
}

// helper por si no tienes verificarSesion() en session.php
if (function_exists('verificarSesion')) {
    verificarSesion(); // aquí puedes validar que sea rol empresa/admin si quieres
}

$usuario_id = $_SESSION['usuario_id'] ?? null;

// Rutas para documentos e imágenes (por si las usas en otros procesos)
$docsPath = __DIR__ . '/../public/docs/';
$imgPath  = __DIR__ . '/../public/img/';

// Crear carpetas si no existen
if (!is_dir($docsPath)) {
    mkdir($docsPath, 0755, true); // true = crear recursivamente
}
if (!is_dir($imgPath)) {
    mkdir($imgPath, 0755, true);
}

// Obtener convocatorias desde la BD (tabla: ofertas)
try {
    $stmt = $conn->query("
        SELECT 
            o.id, 
            o.usuario_id, 
            o.titulo, 
            o.descripcion, 
            o.documento_adj, 
            o.estado, 
            o.publicado_en, 
            o.actualizado_en, 
            u.nombre AS empresa
        FROM ofertas o
        LEFT JOIN usuarios u ON u.id = o.usuario_id
        ORDER BY o.publicado_en DESC
    ");
    $convocatorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener convocatorias: " . $e->getMessage());
}

// función para obtener postulantes de una oferta
function obtenerPostulantes(PDO $conn, $oferta_id)
{
    try {
        $s = $conn->prepare("
            SELECT 
                p.id, 
                p.usuario_id, 
                p.estado,                  -- en_revision / aceptado / rechazado (reclutador)
                p.calificacion, 
                p.mensaje, 
                p.respuesta_postulante,    -- aceptada / rechazada / NULL
                p.fecha_respuesta,         -- cuándo respondió el postulante
                u.nombre
            FROM postulaciones p
            LEFT JOIN usuarios u ON u.id = p.usuario_id
            WHERE p.oferta_id = :oferta_id
            ORDER BY p.creado_en DESC
        ");
        $s->execute(['oferta_id' => $oferta_id]);
        return $s->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// header (vista) – lo incluimos DESPUÉS de verificar sesión
include __DIR__ . '/views/cabeza/header.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Convocatorias - LinkedIn EMI</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
    <style>
        /* estilos básicos (azul-blanco) */
        main {
            padding: 17rem 5rem 5rem 4rem;
        }

        .btn {
            background: #007bff;
            color: #fff;
            padding: .4rem .8rem;
            border-radius: 6px;
            border: 0;
            cursor: pointer;
            margin: 3px
        }

        .btn.danger {
            background: #dc3545
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06)
        }

        .table th {
            background: #007bff;
            color: #fff;
            padding: 8px;
            text-align: left
        }

        .table td {
            padding: 8px;
            border: 1px solid #e6f0ff
        }

        .card {
            background: #fff;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
            margin-top: 1rem
        }

        .form-row {
            margin-bottom: .6rem
        }

        input[type="text"],
        textarea,
        select,
        input[type="file"] {
            width: 100%;
            max-width: 480px;
            padding: .5rem;
            border: 1px solid #ccc;
            border-radius: 6px
        }

        .small {
            font-size: .9rem;
            color: #555
        }

        .postulantes-list {
            padding-left: 18px;
            margin: 0
        }

        .evaluar {
            display: none;
            margin-top: .5rem;
            padding: .5rem;
            background: #f8fbff;
            border-radius: 6px
        }
    </style>
</head>

<body>

    <main>
        <h2>Gestión de Convocatorias</h2>

        <!-- botones -->
        <div>
            <button class="btn" onclick="toggle('filtros')">Mostrar / Ocultar filtros</button>
            <button class="btn" onclick="toggle('crear')">Crear convocatoria</button>
        </div>

        <!-- filtros -->
        <div id="filtros" class="card" style="display:none">
            <h3>Filtros (simulados)</h3>
            <div class="form-row">
                <label>Nivel</label>
                <select>
                    <option>Todos</option>
                    <option>Licenciatura</option>
                    <option>Maestría</option>
                </select>
            </div>
            <div class="form-row">
                <label>Experiencia mínima (años)</label>
                <input type="number" min="0">
            </div>
            <div class="form-row">
                <label>Habilidades</label>
                <input type="text" placeholder="Ej. Python, Redes">
            </div>
            <button class="btn" onclick="alert('Filtros aplicados (simulado)')">Aplicar filtros</button>
        </div>

        <!-- crear -->
        <div id="crear" class="card" style="display:none">
            <h3>Crear Convocatoria</h3>
            <form action="/procesos/crear_convocatoria.php" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <label>Título</label>
                    <input type="text" name="titulo" required>
                </div>

                <div class="form-row">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="4" required></textarea>
                </div>

                <div class="form-row">
                    <label>Documento adjunto (opcional)</label>
                    <input type="file" name="documento" accept=".pdf,.doc,.docx">
                </div>

                <div class="form-row">
                    <label>Categoría</label>
                    <select name="categoria_id" required>
                        <option value="">Seleccione categoría</option>
                        <option value="1">Tecnología</option>
                        <option value="2">Administración</option>
                        <option value="3">Ingeniería</option>
                    </select>
                </div>

                <div class="form-row">
                    <label>Subcategoría</label>
                    <select name="subcategoria_id" required>
                        <option value="">Seleccione subcategoría</option>
                        <option value="1">Desarrollo Web</option>
                        <option value="2">Soporte/TI</option>
                        <option value="3">Gestión de Proyectos</option>
                        <option value="4">Electromecánica</option>
                    </select>
                </div>

                <div class="form-row">
                    <label>Ubicación</label>
                    <input type="text" name="ubicacion" placeholder="Ciudad, País o Remoto">
                </div>

                <div class="form-row">
                    <label>Tipo de jornada</label>
                    <select name="tipo_jornada">
                        <option value="">Seleccione</option>
                        <option value="completa">Completa</option>
                        <option value="media">Media jornada</option>
                        <option value="remoto">Remoto</option>
                    </select>
                </div>

                <div class="form-row">
                    <label>Modalidad</label>
                    <select name="modalidad">
                        <option value="">Seleccione</option>
                        <option value="presencial">Presencial</option>
                        <option value="remoto">Remoto</option>
                        <option value="hibrido">Híbrido</option>
                    </select>
                </div>

                <div class="form-row">
                    <label>Años mínimos de experiencia</label>
                    <input type="number" name="experiencia_min" min="0" max="50">
                </div>

                <div class="form-row">
                    <label>Salario mínimo</label>
                    <input type="number" step="0.01" name="salario_min">
                </div>

                <div class="form-row">
                    <label>Salario máximo</label>
                    <input type="number" step="0.01" name="salario_max">
                </div>

                <div class="form-row">
                    <label>Beneficios</label>
                    <textarea name="beneficios" rows="3"></textarea>
                </div>

                <div class="form-row">
                    <label>Contacto del reclutador</label>
                    <input type="text" name="contacto_reclutador" placeholder="Correo o teléfono">
                </div>

                <div class="form-row">
                    <label>Logo o imagen de la empresa</label>
                    <input type="file" name="imagen_empresa" accept=".jpg,.jpeg,.png,.gif">
                </div>

                <div class="form-row">
                    <button class="btn" type="submit">Guardar convocatoria</button>
                </div>
            </form>

        </div>

        <!-- tabla convocatorias -->
        <div class="card">
            <h3>Convocatorias registradas</h3>

            <?php if (!empty($convocatorias)): ?>
                <table class="table" role="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Documento</th>
                            <th>Postulantes</th>
                            <th>Estado</th>
                            <th>Publicado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($convocatorias as $c): ?>
                            <tr id="fila-<?= $c['id'] ?>">
                                <td><?= $c['id'] ?></td>
                                <td><?= htmlspecialchars($c['titulo']) ?></td>
                                <td class="small"><?= nl2br(htmlspecialchars($c['descripcion'])) ?></td>
                                <td>
                                    <?php if (!empty($c['documento_adj'])): ?>
                                        <a href="/public/docs/<?= rawurlencode($c['documento_adj']) ?>" target="_blank">
                                            <?= htmlspecialchars($c['documento_adj']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="small">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php $postulantes = obtenerPostulantes($conn, $c['id']); ?>
                                    <?php if ($postulantes): ?>
                                        <ul class="postulantes-list">
                                            <?php foreach ($postulantes as $p): ?>
                                                <li>
                                                    <?= htmlspecialchars($p['nombre'] ?? 'Usuario #' . $p['usuario_id']) ?>
                                                    <span class="small">
                                                        (estado: <?= htmlspecialchars($p['estado'] ?? 'en_revision') ?><?php
                                                                                                                        if (!empty($p['respuesta_postulante'])) {
                                                                                                                            echo ' / respuesta: ' . htmlspecialchars($p['respuesta_postulante']);
                                                                                                                        }
                                                                                                                        ?>)
                                                    </span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <span class="small">No hay postulantes</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($c['estado']) ?></td>
                                <td class="small"><?= htmlspecialchars($c['publicado_en']) ?></td>
                                <td>
                                    <!-- acciones: editar / cerrar / eliminar / evaluar -->
                                    <button class="btn" onclick="abrirEditar(
                                        <?= $c['id'] ?>,
                                        <?= json_encode(addslashes($c['titulo'])) ?>,
                                        <?= json_encode(addslashes($c['descripcion'])) ?>
                                    )">Editar</button>

                                    <?php if ($c['estado'] !== 'cerrada'): ?>
                                        <form style="display:inline" method="POST" action="/procesos/cerrar_convocatoria.php" onsubmit="return confirm('¿Cerrar convocatoria?')">
                                            <input type="hidden" name="convocatoria_id" value="<?= $c['id'] ?>">
                                            <button class="btn" type="submit">Cerrar</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="small">cerrada</span>
                                    <?php endif; ?>

                                    <form style="display:inline" method="POST" action="/procesos/eliminar_convocatoria.php" onsubmit="return confirm('¿Eliminar convocatoria? (se borrará permanentemente)')">
                                        <input type="hidden" name="convocatoria_id" value="<?= $c['id'] ?>">
                                        <button class="btn danger" type="submit">Eliminar</button>
                                    </form>

                                    <button class="btn" onclick="toggleEval(<?= $c['id'] ?>)">Evaluar</button>

                                    <!-- panel de evaluación -->
                                    <div id="eval-<?= $c['id'] ?>" class="evaluar">
                                        <?php if ($postulantes): ?>
                                            <form method="POST" action="/procesos/evaluar_postulantes.php">
                                                <input type="hidden" name="oferta_id" value="<?= $c['id'] ?>">
                                                <table style="width:100%;border-collapse:collapse">
                                                    <thead>
                                                        <tr>
                                                            <th>Nombre</th>
                                                            <th>Estado actual</th>
                                                            <th>Calificación</th>
                                                            <th>Nuevo estado</th>
                                                            <th>Acción</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($postulantes as $p): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($p['nombre'] ?? 'Usuario #' . $p['usuario_id']) ?></td>
                                                                <td class="small">
                                                                    <?= htmlspecialchars($p['estado'] ?? 'en_revision') ?>
                                                                    <?php if (!empty($p['respuesta_postulante'])): ?>
                                                                        <br><span class="small">
                                                                            (respuesta: <?= htmlspecialchars($p['respuesta_postulante']) ?>)
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <select name="calificacion[<?= $p['id'] ?>]">
                                                                        <?php for ($i = 0; $i <= 5; $i++): ?>
                                                                            <option value="<?= $i ?>"
                                                                                <?= (isset($p['calificacion']) && $p['calificacion'] == $i) ? 'selected' : '' ?>>
                                                                                <?= $i ?>
                                                                            </option>
                                                                        <?php endfor; ?>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <!-- aquí el reclutador decide -->
                                                                    <select name="estado[<?= $p['id'] ?>]">
                                                                        <?php
                                                                        $estadoActual = $p['estado'] ?? 'en_revision';
                                                                        $estados = ['en_revision' => 'En revisión', 'aceptado' => 'Aceptado', 'rechazado' => 'Rechazado'];
                                                                        foreach ($estados as $valor => $texto): ?>
                                                                            <option value="<?= $valor ?>" <?= ($estadoActual === $valor) ? 'selected' : '' ?>>
                                                                                <?= $texto ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <button class="btn" type="submit" name="guardar_para" value="<?= $p['id'] ?>">
                                                                        Guardar
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </form>
                                        <?php else: ?>
                                            <div class="small">No hay postulantes para evaluar.</div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="small">No hay convocatorias todavía.</div>
            <?php endif; ?>
        </div>

        <!-- formulario edición (modal simple) -->
        <div id="editarModal" class="card" style="display:none;margin-top:1rem">
            <h3>Editar Convocatoria</h3>
            <form id="formEditar" method="POST" action="/procesos/editar_convocatoria.php" enctype="multipart/form-data">
                <input type="hidden" name="convocatoria_id" id="editar_id">
                <div class="form-row">
                    <label>Título</label>
                    <input type="text" name="titulo" id="editar_titulo" required>
                </div>
                <div class="form-row">
                    <label>Descripción</label>
                    <textarea name="descripcion" id="editar_descripcion" rows="4" required></textarea>
                </div>
                <div class="form-row">
                    <label>Nuevo documento (opcional)</label>
                    <input type="file" name="documento">
                </div>
                <div>
                    <button class="btn" type="submit">Guardar cambios</button>
                    <button type="button" class="btn" onclick="toggle('editarModal')">Cancelar</button>
                </div>
            </form>
        </div>

    </main>

    <script>
        function toggle(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
        }

        function toggleEval(id) {
            const el = document.getElementById('eval-' + id);
            if (!el) return;
            el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
        }

        function abrirEditar(id, titulo, descripcion) {
            // abrir modal y rellenar
            document.getElementById('editar_id').value = id;
            // quitar escapes que pusimos con addslashes
            try {
                titulo = titulo.replace(/\\'/g, "'").replace(/\\"/g, '"');
                descripcion = descripcion.replace(/\\'/g, "'").replace(/\\"/g, '"');
            } catch (e) {}
            document.getElementById('editar_titulo').value = titulo;
            document.getElementById('editar_descripcion').value = descripcion;
            document.getElementById('editarModal').style.display = 'block';
        }
    </script>

    <?php include __DIR__ . '/views/cabeza/footer.php'; ?>
</body>

</html>