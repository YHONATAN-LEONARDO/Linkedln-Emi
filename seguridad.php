<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguridad y Privacidad</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arvo:ital,wght@0,400;0,700;1,400;1,700&family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Lobster&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Share+Tech&display=swap" rel="stylesheet">

</head>

<body>
    <?php include __DIR__ . '/views/cabeza/header.php'; ?>

    <main class="seguridad-privacidad">
        <h1>Seguridad y Privacidad / Security & Privacy</h1>

        <!-- Botones principales -->
        <div class="acciones">
            <button onclick="toggleSeccion('autenticacion')">Autenticación Segura</button>
            <button onclick="toggleSeccion('cifrado')">Cifrado de Contraseñas</button>
            <button onclick="toggleSeccion('registro')">Registro de Actividad</button>
            <button onclick="toggleSeccion('permisos')">Permisos por Rol</button>
            <button onclick="toggleSeccion('politicas')">Políticas de Privacidad</button>
            <button onclick="toggleSeccion('notificaciones')">Notificación de Accesos</button>
        </div>

        <!-- Sección Autenticación Segura -->
        <div id="autenticacion" style="display:block; margin-top:1rem;">
            <h2>Autenticación Segura</h2>
            <p>Los usuarios deben iniciar sesión con un sistema seguro de autenticación.</p>
            <label>Usuario:</label>
            <input type="text" placeholder="Ingrese su usuario">
            <label>Contraseña:</label>
            <input type="password" placeholder="Ingrese su contraseña">
            <label>Confirmar contraseña:</label>
            <input type="password" placeholder="Confirme contraseña">
            <button onclick="loginTest()">Probar Login</button>
            <p id="loginMsg" style="color:green;"></p>
        </div>

        <!-- Sección Cifrado -->
        <div id="cifrado" style="display:none; margin-top:1rem;">
            <h2>Cifrado de Contraseñas y Datos Sensibles</h2>
            <p>Simulación de cifrado de datos sensibles:</p>
            <input type="text" placeholder="Texto a cifrar" id="textoCifrar">
            <button onclick="cifrarTexto()">Cifrar</button>
            <p id="resultadoCifrado" style="color:blue;"></p>
        </div>

        <!-- Sección Registro de Actividad -->
        <div id="registro" style="display:none; margin-top:1rem;">
            <h2>Registro de Actividad</h2>
            <p>Lista de actividades recientes del usuario:</p>
            <ul id="actividadLista">
                <li>Usuario inició sesión</li>
                <li>Cambió contraseña</li>
                <li>Actualizó perfil</li>
            </ul>
            <button onclick="agregarActividad()">Agregar actividad</button>
        </div>

        <!-- Sección Permisos por Rol -->
        <div id="permisos" style="display:none; margin-top:1rem;">
            <h2>Configuración de Permisos por Rol</h2>
            <p>Asignar permisos a diferentes roles:</p>
            <label>Seleccionar rol:</label>
            <select id="rolSelect" onchange="mostrarPermisos()">
                <option value="admin">Administrador</option>
                <option value="editor">Editor</option>
                <option value="usuario">Usuario</option>
            </select>
            <div id="permisosRol" style="margin-top:1rem;">
                <p>Permisos del rol seleccionado se mostrarán aquí</p>
            </div>
            <button onclick="guardarPermisos()">Guardar Permisos</button>
        </div>

        <!-- Sección Políticas de Privacidad -->
        <div id="politicas" style="display:none; margin-top:1rem;">
            <h2>Políticas de Privacidad y Manejo de Datos</h2>
            <textarea rows="6" cols="50">Texto de políticas de privacidad...</textarea><br>
            <button onclick="guardarPoliticas()">Guardar Políticas</button>
            <p id="politicaMsg" style="color:green;"></p>
        </div>

        <!-- Sección Notificación de accesos -->
        <div id="notificaciones" style="display:none; margin-top:1rem;">
            <h2>Notificación de Accesos Sospechosos</h2>
            <p>Simulación de alertas de seguridad:</p>
            <button onclick="simularAcceso()">Simular Acceso Sospechoso</button>
            <ul id="alertasLista" style="color:red;"></ul>
        </div>
    </main>

    <?php include __DIR__ . '/views/cabeza/footer.php'; ?>

    <script>
        // Función para mostrar/ocultar secciones
        function toggleSeccion(id) {
            const secciones = ['autenticacion', 'cifrado', 'registro', 'permisos', 'politicas', 'notificaciones'];
            secciones.forEach(sec => {
                document.getElementById(sec).style.display = (sec === id ? 'block' : 'none');
            });
        }

        // Función login de prueba
        function loginTest() {
            const loginMsg = document.getElementById('loginMsg');
            loginMsg.innerText = 'Login simulado exitoso!';
        }

        // Función de cifrado simulado
        function cifrarTexto() {
            const texto = document.getElementById('textoCifrar').value;
            const resultado = btoa(texto); // Base64 como ejemplo
            document.getElementById('resultadoCifrado').innerText = 'Texto cifrado: ' + resultado;
        }

        // Registro de actividad
        function agregarActividad() {
            const lista = document.getElementById('actividadLista');
            const fecha = new Date().toLocaleString();
            const li = document.createElement('li');
            li.innerText = `Actividad simulada - ${fecha}`;
            lista.appendChild(li);
        }

        // Permisos por rol
        const permisosRoles = {
            admin: ['Crear', 'Editar', 'Eliminar', 'Ver'],
            editor: ['Editar', 'Ver'],
            usuario: ['Ver']
        };

        function mostrarPermisos() {
            const rol = document.getElementById('rolSelect').value;
            const div = document.getElementById('permisosRol');
            div.innerHTML = '';
            permisosRoles[rol].forEach(p => {
                const pEl = document.createElement('p');
                pEl.innerText = p;
                div.appendChild(pEl);
            });
        }

        function guardarPermisos() {
            alert('Permisos guardados (simulado)');
        }

        // Políticas de privacidad
        function guardarPoliticas() {
            document.getElementById('politicaMsg').innerText = 'Políticas guardadas exitosamente!';
        }

        // Simulación de accesos sospechosos
        function simularAcceso() {
            const lista = document.getElementById('alertasLista');
            const fecha = new Date().toLocaleString();
            const li = document.createElement('li');
            li.innerText = `Acceso sospechoso detectado - ${fecha}`;
            lista.appendChild(li);
        }
    </script>

</body>

</html>