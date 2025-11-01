<?php
session_start();
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/funcion_db.php';
require_once __DIR__ . '/views/cabeza/header.php';

// Solo admin
verificarSesion();
if ($_SESSION['rol'] != 1) {
    header("Location: /views/usuario/login.php");
    exit();
}

// -------------------- GUARDAR PARÁMETROS --------------------
if (isset($_POST['guardar_parametros'])) {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $zona = $_POST['zona'] ?? '';
    dbExecute("UPDATE parametros_plataforma SET nombre=?, correo_contacto=?, zona_horaria=?, creado_por=?, creado_en=GETDATE()",
              [$nombre, $correo, $zona, $_SESSION['usuario_id']]);
}

// -------------------- ACTUALIZAR SECCIONES --------------------
if (isset($_POST['guardar_secciones'])) {
    $ids = $_POST['secciones'] ?? [];
    // Deshabilitar todas
    dbExecute("UPDATE secciones_plataforma SET habilitada=0");
    // Habilitar seleccionadas
    foreach ($ids as $id) {
        dbExecute("UPDATE secciones_plataforma SET habilitada=1 WHERE id=?", [$id]);
    }
}

// -------------------- AGREGAR CATEGORÍA --------------------
if (isset($_POST['agregar_categoria'])) {
    $nombre = trim($_POST['categoria']);
    $subs = explode(',', $_POST['subcategorias']);
    if ($nombre != '') {
        dbExecute("INSERT INTO categorias (nombre, descripcion, creada_por, creado_en) VALUES (?, ?, ?, GETDATE())",
                  [$nombre, '', $_SESSION['usuario_id']]);
        $catId = dbLastId();
        foreach ($subs as $s) {
            $s = trim($s);
            if ($s != '') dbExecute("INSERT INTO subcategorias (categoria_id, nombre, descripcion, creado_en) VALUES (?, ?, '', GETDATE())", [$catId, $s]);
        }
    }
}

// -------------------- GUARDAR CONTENIDO ESTÁTICO --------------------
if (isset($_POST['guardar_contenido'])) {
    $titulo = $_POST['titulo'] ?? '';
    $contenido = $_POST['contenido'] ?? '';
    dbExecute("INSERT INTO contenidos_estaticos (seccion_id, titulo, contenido, actualizado_por, actualizado_en)
               VALUES (3, ?, ?, ?, GETDATE())",
              [$titulo, $contenido, $_SESSION['usuario_id']]);
}

// -------------------- APROBAR / RECHAZAR OFERTA --------------------
if (isset($_POST['cambiar_estado'])) {
    $id = $_POST['id_oferta'];
    $nuevo = $_POST['nuevo_estado'];
    dbExecute("UPDATE ofertas SET estado=?, actualizado_en=GETDATE() WHERE id=?", [$nuevo, $id]);
}

// -------------------- DATOS PARA MOSTRAR --------------------
$param = dbSelectOne("SELECT TOP 1 * FROM parametros_plataforma");
$ofertas = dbSelect("SELECT o.id, o.titulo, u.nombre AS empresa, o.estado
                     FROM ofertas o
                     INNER JOIN usuarios u ON o.usuario_id=u.id
                     ORDER BY o.publicado_en DESC");
$secciones = dbSelect("SELECT * FROM secciones_plataforma");
$categorias = dbSelect("SELECT * FROM categorias");
$subcategorias = dbSelect("SELECT * FROM subcategorias");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
<main class="admin-plataforma mar">
    <h2>Administración de la Plataforma</h2>

    <!-- Botones -->
    <div class="acciones">
        <button onclick="toggleSeccion('parametros')">Configurar Parámetros</button>
        <button onclick="toggleSeccion('revisarOfertas')">Revisar Ofertas</button>
        <button onclick="toggleSeccion('habilitarSecciones')">Secciones</button>
        <button onclick="toggleSeccion('categorias')">Categorías</button>
        <button onclick="toggleSeccion('contenido')">Contenido Estático</button>
        <a href="seguridad.php"><button>Seguridad</button></a>
    </div>

    <!-- ================== PARÁMETROS ================== -->
    <div id="parametros" style="display:none; margin-top:1rem;">
        <h3>Parámetros Generales</h3>
        <form method="POST">
            <label>Nombre:</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($param['nombre'] ?? '') ?>">
            <label>Correo:</label>
            <input type="email" name="correo" value="<?= htmlspecialchars($param['correo_contacto'] ?? '') ?>">
            <label>Zona horaria:</label>
            <input type="text" name="zona" value="<?= htmlspecialchars($param['zona_horaria'] ?? 'GMT-4') ?>">
            <button name="guardar_parametros">Guardar Parámetros</button>
        </form>
    </div>

    <!-- ================== REVISAR OFERTAS ================== -->
    <div id="revisarOfertas" style="display:none; margin-top:1rem;">
        <h3>Revisar y Aprobar Ofertas</h3>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr><th>Oferta</th><th>Empresa</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody>
            <?php foreach ($ofertas as $o): ?>
                <tr>
                    <td><?= htmlspecialchars($o['titulo']) ?></td>
                    <td><?= htmlspecialchars($o['empresa']) ?></td>
                    <td><?= htmlspecialchars($o['estado']) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id_oferta" value="<?= $o['id'] ?>">
                            <input type="hidden" name="nuevo_estado" value="aprobado">
                            <button name="cambiar_estado">Aprobar</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id_oferta" value="<?= $o['id'] ?>">
                            <input type="hidden" name="nuevo_estado" value="rechazado">
                            <button name="cambiar_estado">Rechazar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ================== SECCIONES ================== -->
    <div id="habilitarSecciones" style="display:none; margin-top:1rem;">
        <h3>Habilitar / Deshabilitar Secciones</h3>
        <form method="POST">
        <?php foreach ($secciones as $s): ?>
            <label>
                <input type="checkbox" name="secciones[]" value="<?= $s['id'] ?>" <?= $s['habilitada'] ? 'checked' : '' ?>>
                <?= htmlspecialchars($s['nombre']) ?>
            </label><br>
        <?php endforeach; ?>
            <button name="guardar_secciones">Guardar Cambios</button>
        </form>
    </div>

    <!-- ================== CATEGORÍAS ================== -->
    <div id="categorias" style="display:none; margin-top:1rem;">
        <h3>Gestionar Categorías y Subcategorías</h3>
        <form method="POST">
            <label>Nueva Categoría:</label>
            <input type="text" name="categoria" placeholder="Ej. Tecnología">
            <label>Subcategorías (separa con coma):</label>
            <input type="text" name="subcategorias" placeholder="Ej. Web, Redes, Soporte">
            <button name="agregar_categoria">Agregar Categoría</button>
        </form>
        <h4>Existentes</h4>
        <ul>
            <?php foreach ($categorias as $c): ?>
                <li><?= htmlspecialchars($c['nombre']) ?>
                    <ul>
                        <?php foreach ($subcategorias as $s): if ($s['categoria_id'] == $c['id']): ?>
                            <li><?= htmlspecialchars($s['nombre']) ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- ================== CONTENIDO ESTÁTICO ================== -->
    <div id="contenido" style="display:none; margin-top:1rem;">
        <h3>Contenido Estático</h3>
        <form method="POST">
            <label>Título:</label>
            <input type="text" name="titulo" placeholder="Ej. Sobre Nosotros">
            <label>Contenido:</label>
            <textarea name="contenido" rows="4" placeholder="Escribe el texto..."></textarea>
            <button name="guardar_contenido">Guardar Contenido</button>
        </form>
    </div>
</main>

<?php include __DIR__ . '/views/cabeza/footer.php'; ?>

<script>
function toggleSeccion(id) {
    const secciones = ['parametros','revisarOfertas','habilitarSecciones','categorias','contenido'];
    secciones.forEach(sec => {
        document.getElementById(sec).style.display = (sec === id ?
            (document.getElementById(sec).style.display === 'none' ? 'block' : 'none') 
            : 'none');
    });
}
</script>
</body>
</html>
