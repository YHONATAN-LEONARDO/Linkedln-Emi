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

// ---------- REGISTRAR ACTIVIDAD ----------
if (isset($_POST['registrar_actividad'])) {
    $detalle = $_POST['detalle'] ?? '';
    if ($detalle != '') {
        dbExecute("INSERT INTO actividades (usuario_id, detalle, creado_en) VALUES (?, ?, GETDATE())", [$_SESSION['usuario_id'], $detalle]);
    }
}

// ---------- GUARDAR PERMISOS ----------
if (isset($_POST['guardar_permisos'])) {
    $rol = $_POST['rol'] ?? '';
    $permisos = $_POST['permisos'] ?? [];
    // Primero borramos permisos antiguos
    dbExecute("DELETE FROM permisos_roles WHERE rol = ?", [$rol]);
    foreach ($permisos as $p) {
        dbExecute("INSERT INTO permisos_roles (rol, permiso_codigo) VALUES (?, ?)", [$rol, $p]);
    }
}

// ---------- GUARDAR POLÍTICAS ----------
if (isset($_POST['guardar_politicas'])) {
    $texto = $_POST['politicas'] ?? '';
    dbExecute("INSERT INTO politicas_privacidad (contenido, actualizado_por, actualizado_en) VALUES (?, ?, GETDATE())", [$texto, $_SESSION['usuario_id']]);
}

// ---------- SIMULAR ALERTA ----------
if (isset($_POST['simular_alerta'])) {
    $detalle = "Acceso sospechoso detectado";
    dbExecute("INSERT INTO alertas_seguridad (usuario_id, detalle, creado_en) VALUES (?, ?, GETDATE())", [$_SESSION['usuario_id'], $detalle]);
}

// ---------- OBTENER DATOS ----------
$actividades = dbSelect("SELECT TOP 10 a.*, u.nombre FROM actividades a INNER JOIN usuarios u ON u.id=a.usuario_id ORDER BY a.creado_en DESC");
$alertas = dbSelect("SELECT TOP 10 * FROM alertas_seguridad ORDER BY creado_en DESC");

// Para permisos por rol
$permisosRoles = [
    'admin' => dbSelect("SELECT permiso_codigo FROM permisos_roles WHERE rol='admin'"),
    'editor' => dbSelect("SELECT permiso_codigo FROM permisos_roles WHERE rol='editor'"),
    'usuario' => dbSelect("SELECT permiso_codigo FROM permisos_roles WHERE rol='usuario'")
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seguridad y Privacidad</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>

<main class="seguridad-privacidad mar">
    <h1>Seguridad y Privacidad</h1>

    <div class="acciones">
        <button onclick="toggleSeccion('autenticacion')">Autenticación Segura</button>
        <button onclick="toggleSeccion('cifrado')">Cifrado de Contraseñas</button>
        <button onclick="toggleSeccion('registro')">Registro de Actividad</button>
        <button onclick="toggleSeccion('permisos')">Permisos por Rol</button>
        <button onclick="toggleSeccion('politicas')">Políticas</button>
        <button onclick="toggleSeccion('notificaciones')">Notificaciones</button>
    </div>

    <!-- AUTENTICACIÓN -->
    <div id="autenticacion" style="display:block; margin-top:1rem;">
        <h2>Autenticación Segura</h2>
        <p>Usuarios deben iniciar sesión con un sistema seguro.</p>
        <form method="POST">
            <label>Usuario:</label>
            <input type="text" name="detalle" placeholder="Ingrese acción">
            <button name="registrar_actividad">Registrar Actividad</button>
        </form>
        <ul>
            <?php foreach($actividades as $a): ?>
                <li><?= htmlspecialchars($a['nombre'] . ' - ' . $a['detalle'] . ' (' . $a['creado_en'] . ')') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- CIFRADO -->
    <div id="cifrado" style="display:none; margin-top:1rem;">
        <h2>Cifrado de Contraseñas</h2>
        <form method="POST">
            <input type="text" id="textoCifrar" placeholder="Texto a cifrar">
            <button type="button" onclick="cifrarTexto()">Cifrar</button>
        </form>
        <p id="resultadoCifrado" style="color:blue;"></p>
    </div>

    <!-- PERMISOS POR ROL -->
    <div id="permisos" style="display:none; margin-top:1rem;">
        <h2>Permisos por Rol</h2>
        <form method="POST">
            <label>Seleccionar rol:</label>
            <select name="rol" id="rolSelect" onchange="mostrarPermisos()">
                <option value="admin">Administrador</option>
                <option value="editor">Editor</option>
                <option value="usuario">Usuario</option>
            </select>
            <div id="permisosRol" style="margin-top:1rem;"></div>
            <input type="hidden" name="permisos[]" id="permisosInput">
            <button name="guardar_permisos">Guardar Permisos</button>
        </form>
    </div>

    <!-- POLÍTICAS -->
    <div id="politicas" style="display:none; margin-top:1rem;">
        <h2>Políticas de Privacidad</h2>
        <form method="POST">
            <textarea name="politicas" rows="6" cols="50">Texto de políticas...</textarea><br>
            <button name="guardar_politicas">Guardar</button>
        </form>
    </div>

    <!-- NOTIFICACIONES -->
    <div id="notificaciones" style="display:none; margin-top:1rem;">
        <h2>Alertas de Seguridad</h2>
        <form method="POST">
            <button name="simular_alerta">Simular Acceso Sospechoso</button>
        </form>
        <ul style="color:red;">
            <?php foreach($alertas as $al): ?>
                <li><?= htmlspecialchars($al['detalle'] . ' - ' . $al['creado_en']) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</main>

<?php include __DIR__ . '/views/cabeza/footer.php'; ?>

<script>
function toggleSeccion(id){
    const secciones = ['autenticacion','cifrado','registro','permisos','politicas','notificaciones'];
    secciones.forEach(sec => document.getElementById(sec).style.display = (sec===id?'block':'none'));
}

function cifrarTexto(){
    const texto = document.getElementById('textoCifrar').value;
    document.getElementById('resultadoCifrado').innerText = 'Texto cifrado (Base64): ' + btoa(texto);
}

// Permisos por rol dinámicos
const permisosRoles = <?= json_encode(array_map(fn($v)=>array_map(fn($p)=>$p['permiso_codigo'],$v), $permisosRoles)) ?>;

function mostrarPermisos(){
    const rol = document.getElementById('rolSelect').value;
    const div = document.getElementById('permisosRol');
    div.innerHTML = '';
    if(permisosRoles[rol]){
        permisosRoles[rol].forEach(p=>{
            const pEl = document.createElement('p'); pEl.innerText = p; div.appendChild(pEl);
        });
    }
}
</script>

</body>
</html>
