<?php
session_start();
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/funcion_db.php';

// Solo admin
verificarSesion();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    header("Location: /views/usuario/login.php");
    exit();
}

// -------------------- GUARDAR PARÁMETROS --------------------
if (isset($_POST['guardar_parametros'])) {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $zona   = $_POST['zona'] ?? '';

    dbExecute(
        "UPDATE parametros_plataforma 
         SET nombre = ?, correo_contacto = ?, zona_horaria = ?, creado_por = ?, creado_en = GETDATE()",
        [$nombre, $correo, $zona, $_SESSION['usuario_id']]
    );
}

// -------------------- ACTUALIZAR SECCIONES --------------------
if (isset($_POST['guardar_secciones'])) {
    $ids = $_POST['secciones'] ?? [];

    // Deshabilitar todas
    dbExecute("UPDATE secciones_plataforma SET habilitada = 0");

    // Habilitar seleccionadas
    foreach ($ids as $id) {
        dbExecute("UPDATE secciones_plataforma SET habilitada = 1 WHERE id = ?", [$id]);
    }
}

// -------------------- AGREGAR CATEGORÍA --------------------
if (isset($_POST['agregar_categoria'])) {
    $nombreCat = trim($_POST['categoria'] ?? '');
    $subsRaw   = $_POST['subcategorias'] ?? '';
    $subs      = array_filter(array_map('trim', explode(',', $subsRaw)));

    if ($nombreCat !== '') {
        dbExecute(
            "INSERT INTO categorias (nombre, descripcion, creada_por, creado_en) 
             VALUES (?, ?, ?, GETDATE())",
            [$nombreCat, '', $_SESSION['usuario_id']]
        );
        $catId = dbLastId();

        foreach ($subs as $s) {
            dbExecute(
                "INSERT INTO subcategorias (categoria_id, nombre, descripcion, creado_en) 
                 VALUES (?, ?, '', GETDATE())",
                [$catId, $s]
            );
        }
    }
}

// -------------------- GUARDAR CONTENIDO ESTÁTICO --------------------
if (isset($_POST['guardar_contenido'])) {
    $titulo    = $_POST['titulo'] ?? '';
    $contenido = $_POST['contenido'] ?? '';

    dbExecute(
        "INSERT INTO contenidos_estaticos 
         (seccion_id, titulo, contenido, actualizado_por, actualizado_en)
         VALUES (3, ?, ?, ?, GETDATE())",
        [$titulo, $contenido, $_SESSION['usuario_id']]
    );
}

// -------------------- APROBAR / RECHAZAR OFERTA --------------------
if (isset($_POST['cambiar_estado'])) {
    $idOferta   = (int)($_POST['id_oferta'] ?? 0);
    $nuevoEstado = $_POST['nuevo_estado'] ?? 'en_revision';

    if ($idOferta > 0) {
        dbExecute(
            "UPDATE ofertas 
             SET estado = ?, actualizado_en = GETDATE() 
             WHERE id = ?",
            [$nuevoEstado, $idOferta]
        );
    }
}

// -------------------- DATOS PARA MOSTRAR --------------------
$param        = dbSelectOne("SELECT TOP 1 * FROM parametros_plataforma");
$ofertas      = dbSelect("
    SELECT o.id, o.titulo, u.nombre AS empresa, o.estado
    FROM ofertas o
    INNER JOIN usuarios u ON o.usuario_id = u.id
    ORDER BY o.publicado_en DESC
");
$secciones    = dbSelect("SELECT * FROM secciones_plataforma");
$categorias   = dbSelect("SELECT * FROM categorias");
$subcategorias = dbSelect("SELECT * FROM subcategorias");

// Header HTML
require_once __DIR__ . '/views/cabeza/header.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de la Plataforma</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">

    <style>
        main.admin-plataforma {
            max-width: 1100px;
            margin: 10rem auto 3rem;
            padding: 1.5rem;
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
            font-family: "Montserrat", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        main.admin-plataforma h2 {
            margin-top: 0;
            font-size: 2rem;
            color: #0f172a;
            text-align: center;
        }

        .acciones {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .acciones button,
        .acciones a button {
            border: none;
            padding: .6rem 1.2rem;
            border-radius: 999px;
            background: #0ea5e9;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            font-size: .95rem;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.35);
            transition: background .15s, transform .08s, box-shadow .15s;
        }

        .acciones button:hover {
            background: #0284c7;
            transform: translateY(-1px);
            box-shadow: 0 7px 18px rgba(14, 165, 233, 0.4);
        }

        .acciones button:active {
            transform: translateY(0);
            box-shadow: 0 3px 10px rgba(14, 165, 233, 0.3);
        }

        section.admin-block {
            margin-top: 1.5rem;
            padding: 1rem 1.2rem;
            border-radius: .9rem;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }

        section.admin-block h3 {
            margin-top: 0;
            font-size: 1.3rem;
            color: #111827;
            margin-bottom: .7rem;
        }

        section.admin-block form label {
            display: block;
            margin-top: .4rem;
            font-size: .95rem;
            color: #374151;
        }

        section.admin-block input[type="text"],
        section.admin-block input[type="email"],
        section.admin-block textarea {
            width: 100%;
            max-width: 480px;
            padding: .4rem .6rem;
            border-radius: .5rem;
            border: 1px solid #d1d5db;
            margin-top: .1rem;
            font-size: .95rem;
        }

        section.admin-block button {
            margin-top: .8rem;
            padding: .5rem 1.1rem;
            border-radius: .7rem;
            border: none;
            background: #16a34a;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            font-size: .9rem;
        }

        section.admin-block button:hover {
            background: #15803d;
        }

        table.ofertas-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .95rem;
            background: #ffffff;
            border-radius: .7rem;
            overflow: hidden;
        }

        table.ofertas-table thead {
            background: #0f172a;
            color: #ffffff;
        }

        table.ofertas-table th,
        table.ofertas-table td {
            padding: .5rem .6rem;
            border-bottom: 1px solid #e5e7eb;
        }

        table.ofertas-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        table.ofertas-table .acciones-oferta form {
            display: inline-block;
            margin-right: .3rem;
        }

        table.ofertas-table .acciones-oferta button {
            padding: .35rem .8rem;
            border-radius: .5rem;
            font-size: .85rem;
        }

        table.ofertas-table .btn-aprobar {
            background: #16a34a;
            color: #fff;
        }

        table.ofertas-table .btn-rechazar {
            background: #dc2626;
            color: #fff;
        }

        @media (max-width: 768px) {
            main.admin-plataforma {
                margin-top: 7rem;
                padding: 1rem;
            }

            table.ofertas-table {
                font-size: .85rem;
            }

            .acciones {
                flex-direction: column;
                align-items: stretch;
            }

            .acciones button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <main class="admin-plataforma mar">
        <h2>Administración de la Plataforma</h2>

        <!-- Botones (tabs) -->
        <div class="acciones">
            <button type="button" onclick="mostrarSeccion('revisarOfertas')">Revisar Ofertas</button>
            <button type="button" onclick="mostrarSeccion('parametros')">Configurar Parámetros</button>
            <button type="button" onclick="mostrarSeccion('habilitarSecciones')">Secciones</button>
            <button type="button" onclick="mostrarSeccion('categorias')">Categorías</button>
            <button type="button" onclick="mostrarSeccion('contenido')">Contenido Estático</button>
            <a href="seguridad.php"><button type="button">Seguridad</button></a>
        </div>

        <!-- ================== REVISAR OFERTAS (POR DEFECTO VISIBLE) ================== -->
        <section id="revisarOfertas" class="admin-block" style="display:block;">
            <h3>Revisar y Aprobar Ofertas</h3>

            <?php if (!empty($ofertas)): ?>
                <table class="ofertas-table">
                    <thead>
                        <tr>
                            <th>Oferta</th>
                            <th>Empresa</th>
                            <th>Estado</th>
                            <th style="width: 220px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ofertas as $o): ?>
                            <tr>
                                <td><?= htmlspecialchars($o['titulo']) ?></td>
                                <td><?= htmlspecialchars($o['empresa']) ?></td>
                                <td><?= htmlspecialchars($o['estado']) ?></td>
                                <td class="acciones-oferta">
                                    <form method="POST">
                                        <input type="hidden" name="id_oferta" value="<?= (int)$o['id'] ?>">
                                        <input type="hidden" name="nuevo_estado" value="aprobado">
                                        <button type="submit" name="cambiar_estado" class="btn-aprobar">
                                            Aprobar
                                        </button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="id_oferta" value="<?= (int)$o['id'] ?>">
                                        <input type="hidden" name="nuevo_estado" value="rechazado">
                                        <button type="submit" name="cambiar_estado" class="btn-rechazar">
                                            Rechazar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay ofertas registradas todavía.</p>
            <?php endif; ?>
        </section>

        <!-- ================== PARÁMETROS ================== -->
        <section id="parametros" class="admin-block" style="display:none;">
            <h3>Parámetros Generales</h3>
            <form method="POST">
                <label>Nombre:</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($param['nombre'] ?? '') ?>">

                <label>Correo de contacto:</label>
                <input type="email" name="correo" value="<?= htmlspecialchars($param['correo_contacto'] ?? '') ?>">

                <label>Zona horaria:</label>
                <input type="text" name="zona" value="<?= htmlspecialchars($param['zona_horaria'] ?? 'GMT-4') ?>">

                <button type="submit" name="guardar_parametros">Guardar Parámetros</button>
            </form>
        </section>

        <!-- ================== SECCIONES ================== -->
        <section id="habilitarSecciones" class="admin-block" style="display:none;">
            <h3>Habilitar / Deshabilitar Secciones</h3>
            <form method="POST">
                <?php foreach ($secciones as $s): ?>
                    <label>
                        <input
                            type="checkbox"
                            name="secciones[]"
                            value="<?= (int)$s['id'] ?>"
                            <?= !empty($s['habilitada']) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($s['nombre']) ?>
                    </label><br>
                <?php endforeach; ?>
                <button type="submit" name="guardar_secciones">Guardar Cambios</button>
            </form>
        </section>

        <!-- ================== CATEGORÍAS ================== -->
        <section id="categorias" class="admin-block" style="display:none;">
            <h3>Gestionar Categorías y Subcategorías</h3>

            <form method="POST">
                <label>Nueva Categoría:</label>
                <input type="text" name="categoria" placeholder="Ej. Tecnología">

                <label>Subcategorías (separa con coma):</label>
                <input type="text" name="subcategorias" placeholder="Ej. Web, Redes, Soporte">

                <button type="submit" name="agregar_categoria">Agregar Categoría</button>
            </form>

            <h4 style="margin-top:1rem;">Existentes:</h4>
            <ul>
                <?php foreach ($categorias as $c): ?>
                    <li>
                        <strong><?= htmlspecialchars($c['nombre']) ?></strong>
                        <ul>
                            <?php foreach ($subcategorias as $s): ?>
                                <?php if ((int)$s['categoria_id'] === (int)$c['id']): ?>
                                    <li><?= htmlspecialchars($s['nombre']) ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <!-- ================== CONTENIDO ESTÁTICO ================== -->
        <section id="contenido" class="admin-block" style="display:none;">
            <h3>Contenido Estático</h3>
            <form method="POST">
                <label>Título:</label>
                <input type="text" name="titulo" placeholder="Ej. Sobre Nosotros">

                <label>Contenido:</label>
                <textarea name="contenido" rows="4" placeholder="Escribe el texto..."></textarea>

                <button type="submit" name="guardar_contenido">Guardar Contenido</button>
            </form>
        </section>
    </main>

    <?php include __DIR__ . '/views/cabeza/footer.php'; ?>

    <script>
        // IDs de todas las secciones
        const SECCIONES = ['revisarOfertas', 'parametros', 'habilitarSecciones', 'categorias', 'contenido'];

        function mostrarSeccion(id) {
            SECCIONES.forEach(sec => {
                const el = document.getElementById(sec);
                if (!el) return;
                el.style.display = (sec === id) ? 'block' : 'none';
            });
        }

        // Ya dejamos "revisarOfertas" visible por defecto en el HTML (display:block).
        // Si quisieras forzarlo también por JS al cargar:
        // window.addEventListener('DOMContentLoaded', () => mostrarSeccion('revisarOfertas'));
    </script>

</body>

</html>