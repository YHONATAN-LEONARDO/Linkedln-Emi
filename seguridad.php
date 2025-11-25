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

// ---------- REGISTRAR ACTIVIDAD ----------
if (isset($_POST['registrar_actividad'])) {
    $detalle = trim($_POST['detalle'] ?? '');
    if ($detalle !== '') {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        dbExecute(
            "INSERT INTO actividades (usuario_id, accion, descripcion, ip, creado_en) 
             VALUES (?, ?, ?, ?, GETDATE())",
            [$_SESSION['usuario_id'], 'accion_admin', $detalle, $ip]
        );
    }
}

// ---------- GUARDAR PERMISOS POR ROL ----------
if (isset($_POST['guardar_permisos'])) {
    $rol_id = (int)($_POST['rol_id'] ?? 0);
    $permisosSeleccionados = $_POST['permisos'] ?? [];

    if ($rol_id > 0) {
        // Borrar permisos antiguos
        dbExecute("DELETE FROM roles_permisos WHERE rol_id = ?", [$rol_id]);

        // Insertar nuevos
        foreach ($permisosSeleccionados as $permiso_id) {
            $permiso_id = (int)$permiso_id;
            if ($permiso_id > 0) {
                dbExecute(
                    "INSERT INTO roles_permisos (rol_id, permiso_id) VALUES (?, ?)",
                    [$rol_id, $permiso_id]
                );
            }
        }
    }
}

// ---------- GUARDAR POLÍTICAS (en contenidos_estaticos, sección Seguridad = id 4) ----------
if (isset($_POST['guardar_politicas'])) {
    $texto = $_POST['politicas'] ?? '';

    dbExecute(
        "INSERT INTO contenidos_estaticos (seccion_id, titulo, contenido, actualizado_por, actualizado_en)
         VALUES (4, 'Políticas de Privacidad', ?, ?, GETDATE())",
        [$texto, $_SESSION['usuario_id']]
    );
}

// ---------- SIMULAR ALERTA ----------
if (isset($_POST['simular_alerta'])) {
    $detalle = "Acceso sospechoso detectado desde la IP " . ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

    dbExecute(
        "INSERT INTO alertas_seguridad (usuario_id, tipo, detalle, atendido, creado_en)
         VALUES (?, ?, ?, 0, GETDATE())",
        [$_SESSION['usuario_id'], 'acceso_sospechoso', $detalle]
    );
}

// ---------- OBTENER DATOS ----------
// Últimas actividades
$actividades = dbSelect("
    SELECT TOP 10 a.*, u.nombre 
    FROM actividades a 
    INNER JOIN usuarios u ON u.id = a.usuario_id
    ORDER BY a.creado_en DESC
");

// Últimas alertas
$alertas = dbSelect("
    SELECT TOP 10 * 
    FROM alertas_seguridad 
    ORDER BY creado_en DESC
");

// Roles y permisos
$roles = dbSelect("SELECT id, nombre FROM roles ORDER BY id");
$permisos = dbSelect("SELECT id, codigo, descripcion FROM permisos ORDER BY id");

// Permisos actuales por rol (array rol_id => [permiso_id, ...])
$permisosPorRol = [];
foreach ($roles as $r) {
    $rid = (int)$r['id'];
    $rows = dbSelect("SELECT permiso_id FROM roles_permisos WHERE rol_id = ?", [$rid]);
    $permisosPorRol[$rid] = array_map(fn($x) => (int)$x['permiso_id'], $rows);
}

// Última política de privacidad guardada (seccion Seguridad id=4)
$politica = dbSelectOne("
    SELECT TOP 1 * 
    FROM contenidos_estaticos 
    WHERE seccion_id = 4 AND titulo = 'Políticas de Privacidad'
    ORDER BY actualizado_en DESC
");
$textoPolitica = $politica['contenido'] ?? "Texto de políticas...";

// Header HTML
require_once __DIR__ . '/views/cabeza/header.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Seguridad y Privacidad - LinkedIn EMI</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
    <style>
        main.seguridad-privacidad {
            max-width: 1100px;
            margin: 10rem auto 3rem;
            padding: 1.5rem;
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
            font-family: "Montserrat", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        main.seguridad-privacidad h1 {
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

        .acciones button {
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

        section.seg-block {
            margin-top: 1.5rem;
            padding: 1rem 1.2rem;
            border-radius: .9rem;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }

        section.seg-block h2 {
            margin-top: 0;
            font-size: 1.4rem;
            color: #111827;
            margin-bottom: .7rem;
        }

        section.seg-block form label {
            display: block;
            margin-top: .4rem;
            font-size: .95rem;
            color: #374151;
        }

        section.seg-block input[type="text"],
        section.seg-block textarea,
        section.seg-block select {
            width: 100%;
            max-width: 480px;
            padding: .4rem .6rem;
            border-radius: .5rem;
            border: 1px solid #d1d5db;
            margin-top: .1rem;
            font-size: .95rem;
        }

        section.seg-block button {
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

        section.seg-block button:hover {
            background: #15803d;
        }

        ul.actividades,
        ul.alertas {
            list-style: none;
            padding-left: 0;
            margin-top: .7rem;
        }

        ul.actividades li,
        ul.alertas li {
            padding: .3rem 0;
            border-bottom: 1px dashed #e5e7eb;
            font-size: .9rem;
            color: #111827;
        }

        .badge-alerta {
            display: inline-block;
            padding: .15rem .5rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 700;
            margin-right: .3rem;
        }

        .badge-alerta.sospechoso {
            background: #fee2e2;
            color: #b91c1c;
        }

        .badge-alerta.otro {
            background: #e5e7eb;
            color: #374151;
        }

        .permisos-list {
            margin-top: .7rem;
            display: flex;
            flex-direction: column;
            gap: .2rem;
        }

        .permisos-list label {
            font-size: .9rem;
            color: #111827;
        }

        @media (max-width: 768px) {
            main.seguridad-privacidad {
                margin-top: 7rem;
                padding: 1rem;
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

    <main class="seguridad-privacidad mar">
        <h1>Seguridad y Privacidad</h1>

        <div class="acciones">
            <button type="button" onclick="toggleSeccion('autenticacion')">Autenticación Segura</button>
            <button type="button" onclick="toggleSeccion('cifrado')">Cifrado de Contraseñas</button>
            <button type="button" onclick="toggleSeccion('registro')">Registro de Actividad</button>
            <button type="button" onclick="toggleSeccion('permisos')">Permisos por Rol</button>
            <button type="button" onclick="toggleSeccion('politicas')">Políticas</button>
            <button type="button" onclick="toggleSeccion('notificaciones')">Alertas</button>
        </div>

        <!-- AUTENTICACIÓN (info básica) -->
        <section id="autenticacion" class="seg-block" style="display:block;">
            <h2>Autenticación Segura</h2>
            <p>
                La plataforma utiliza roles (<strong>admin</strong>, <strong>empresa</strong>, <strong>postulante</strong>)
                y valida sesión antes de acceder a secciones sensibles.
            </p>
            <p>
                Asegúrate de no compartir tu contraseña y de cerrar sesión en equipos públicos.
            </p>
        </section>

        <!-- REGISTRO DE ACTIVIDAD -->
        <section id="registro" class="seg-block" style="display:none;">
            <h2>Registro de Actividad</h2>
            <form method="POST">
                <label>Registrar acción manual (se guarda en la tabla <code>actividades</code>):</label>
                <input type="text" name="detalle" placeholder="Ej. Revisó ofertas, modificó permisos...">
                <button type="submit" name="registrar_actividad">Registrar Actividad</button>
            </form>

            <h3 style="margin-top:1rem;">Últimas actividades</h3>
            <ul class="actividades">
                <?php if (!empty($actividades)): ?>
                    <?php foreach ($actividades as $a): ?>
                        <li>
                            <strong><?= htmlspecialchars($a['nombre']) ?></strong>
                            &nbsp;→&nbsp;
                            <?= htmlspecialchars($a['accion'] ?? '') ?>:
                            <?= htmlspecialchars($a['descripcion'] ?? '') ?>
                            <br>
                            <small><?= htmlspecialchars($a['creado_en']) ?> · IP: <?= htmlspecialchars($a['ip'] ?? '') ?></small>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No hay actividades registradas aún.</li>
                <?php endif; ?>
            </ul>
        </section>

        <!-- CIFRADO -->
        <section id="cifrado" class="seg-block" style="display:none;">
            <h2>Cifrado de Contraseñas</h2>
            <p>
                En la base de datos, tus contraseñas se guardan como hash usando
                <code>HASHBYTES('SHA2_256', ...)</code> (según el script que cargaste).
            </p>
            <p>
                Aquí solo simulamos un "cifrado" en el navegador para que veas el concepto (Base64, no usarlo para contraseñas reales).
            </p>
            <form onsubmit="return false;">
                <label>Texto a enmascarar (demo):</label>
                <input type="text" id="textoCifrar" placeholder="Escribe algo...">
                <button type="button" onclick="cifrarTexto()">Enmascarar (Base64)</button>
            </form>
            <p id="resultadoCifrado" style="color:#1d4ed8; margin-top:.7rem;"></p>
        </section>

        <!-- PERMISOS POR ROL -->
        <section id="permisos" class="seg-block" style="display:none;">
            <h2>Permisos por Rol</h2>
            <form method="POST">
                <label>Seleccionar rol:</label>
                <select name="rol_id" id="rolSelect" onchange="actualizarChecks()">
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= (int)$r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="permisos-list" id="permisosRol">
                    <?php foreach ($permisos as $p): ?>
                        <label>
                            <input
                                type="checkbox"
                                name="permisos[]"
                                value="<?= (int)$p['id'] ?>"
                                class="chk-permiso">
                            <strong><?= htmlspecialchars($p['codigo']) ?></strong>
                            &nbsp;&mdash;&nbsp;
                            <?= htmlspecialchars($p['descripcion']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <button type="submit" name="guardar_permisos">Guardar Permisos</button>
            </form>
        </section>

        <!-- POLÍTICAS -->
        <section id="politicas" class="seg-block" style="display:none;">
            <h2>Políticas de Privacidad</h2>
            <p>El texto se guarda en la tabla <code>contenidos_estaticos</code>, sección Seguridad.</p>
            <form method="POST">
                <textarea name="politicas" rows="8" style="width:100%;max-width:100%;"><?= htmlspecialchars($textoPolitica) ?></textarea>
                <button type="submit" name="guardar_politicas">Guardar Políticas</button>
            </form>
        </section>

        <!-- NOTIFICACIONES / ALERTAS -->
        <section id="notificaciones" class="seg-block" style="display:none;">
            <h2>Alertas de Seguridad</h2>
            <form method="POST">
                <button type="submit" name="simular_alerta">Simular Acceso Sospechoso</button>
            </form>

            <h3 style="margin-top:1rem;">Últimas alertas</h3>
            <ul class="alertas">
                <?php if (!empty($alertas)): ?>
                    <?php foreach ($alertas as $al): ?>
                        <?php
                        $tipo = strtolower($al['tipo'] ?? '');
                        $claseBadge = ($tipo === 'acceso_sospechoso') ? 'sospechoso' : 'otro';
                        ?>
                        <li>
                            <span class="badge-alerta <?= $claseBadge ?>">
                                <?= htmlspecialchars($al['tipo'] ?? 'alerta') ?>
                            </span>
                            <?= htmlspecialchars($al['detalle'] ?? '') ?>
                            <br>
                            <small><?= htmlspecialchars($al['creado_en']) ?></small>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No hay alertas registradas.</li>
                <?php endif; ?>
            </ul>
        </section>
    </main>

    <?php include __DIR__ . '/views/cabeza/footer.php'; ?>

    <script>
        function toggleSeccion(id) {
            const secciones = ['autenticacion', 'cifrado', 'registro', 'permisos', 'politicas', 'notificaciones'];
            secciones.forEach(sec => {
                const el = document.getElementById(sec);
                if (!el) return;
                el.style.display = (sec === id ? 'block' : 'none');
            });
        }

        // Demo cifrado (Base64)
        function cifrarTexto() {
            const texto = document.getElementById('textoCifrar').value || '';
            if (texto === '') {
                document.getElementById('resultadoCifrado').innerText = 'Escribe algo primero.';
                return;
            }
            document.getElementById('resultadoCifrado').innerText =
                'Texto enmascarado (Base64, solo demo): ' + btoa(unescape(encodeURIComponent(texto)));
        }

        // Permisos por rol dinámicos
        const permisosPorRol = <?= json_encode($permisosPorRol, JSON_UNESCAPED_UNICODE) ?>;

        function actualizarChecks() {
            const rolId = document.getElementById('rolSelect').value;
            const checks = document.querySelectorAll('.chk-permiso');

            // Desmarcar todos
            checks.forEach(chk => chk.checked = false);

            if (!permisosPorRol[rolId]) return;

            const activos = permisosPorRol[rolId].map(String); // ids como string
            checks.forEach(chk => {
                if (activos.includes(String(chk.value))) {
                    chk.checked = true;
                }
            });
        }

        // Al cargar, actualizar checkboxes del primer rol
        document.addEventListener('DOMContentLoaded', actualizarChecks);
    </script>

</body>

</html>