
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administracion</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arvo:ital,wght@0,400;0,700;1,400;1,700&family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Lobster&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Share+Tech&display=swap" rel="stylesheet">

</head>

<body>
    <?php include __DIR__ . '/views/cabeza/header.php'; ?> 
    <main class="admin-plataforma mar">

    <h2>Administración de la Plataforma</h2>

    <!-- Botones principales -->
    <div class="acciones">
        <button onclick="toggleSeccion('parametros')">Configurar Parámetros Generales</button>
        <button onclick="toggleSeccion('revisarOfertas')">Revisar y Aprobar Ofertas</button>
        <button onclick="toggleSeccion('habilitarSecciones')">Habilitar/Deshabilitar Secciones</button>
        <button onclick="toggleSeccion('categorias')">Gestionar Categorías/Subcategorías</button>
        <button onclick="toggleSeccion('contenido')">Gestionar Contenido Estático</button>
        <a  href="seguridad.php"><button>Configurar Seguridad</button></a>
    </div>

    <!-- Sección Configurar Parámetros Generales -->
    <div id="parametros" style="display:none; margin-top:1rem;">
        <h3>Configurar Parámetros Generales</h3>
        <label>Nombre de la Plataforma:</label>
        <input type="text" placeholder="Nombre actual">
        <label>Correo de contacto:</label>
        <input type="email" placeholder="contacto@plataforma.com">
        <label>Zona horaria:</label>
        <select>
            <option>GMT-4</option>
            <option>GMT-3</option>
            <option>GMT-5</option>
        </select>
        <button>Guardar Parámetros</button>
    </div>

    <!-- Sección Revisar y Aprobar Ofertas -->
    <div id="revisarOfertas" style="display:none; margin-top:1rem;">
        <h3>Revisar y Aprobar Ofertas</h3>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <td>Oferta</td>
                    <td>Empresa</td>
                    <td>Estado</td>
                    <td>Acciones</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Asistente Virtual</td>
                    <td>The Link Housing</td>
                    <td>En revisión</td>
                    <td>
                        <button onclick="aprobar(this)">Aprobar</button>
                        <button onclick="rechazar(this)">Rechazar</button>
                    </td>
                </tr>
                <tr>
                    <td>Marketing Specialist</td>
                    <td>Empresa XYZ</td>
                    <td>En revisión</td>
                    <td>
                        <button onclick="aprobar(this)">Aprobar</button>
                        <button onclick="rechazar(this)">Rechazar</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Sección Habilitar/Deshabilitar Secciones -->
    <div id="habilitarSecciones" style="display:none; margin-top:1rem;">
        <h3>Habilitar o Deshabilitar Secciones</h3>
        <label><input type="checkbox" checked> Gestión de Usuarios</label><br>
        <label><input type="checkbox" checked> Publicación de Convocatorias</label><br>
        <label><input type="checkbox"> Búsqueda de Ofertas</label><br>
        <label><input type="checkbox"> Administración de Plataforma</label><br>
        <button>Guardar Cambios</button>
    </div>

    <!-- Sección Gestionar Categorías/Subcategorías -->
    <div id="categorias" style="display:none; margin-top:1rem;">
        <h3>Gestionar Categorías y Subcategorías</h3>
        <label>Nueva Categoría:</label>
        <input type="text" placeholder="Ej. Tecnología">
        <label>Subcategorías:</label>
        <input type="text" placeholder="Ej. Programación, Redes">
        <button>Agregar Categoría</button>
        <h4>Categorías Existentes</h4>
        <ul>
            <li>Tecnología
                <ul>
                    <li>Programación</li>
                    <li>Redes</li>
                </ul>
            </li>
            <li>Marketing
                <ul>
                    <li>Publicidad</li>
                    <li>Social Media</li>
                </ul>
            </li>
        </ul>
    </div>

    <!-- Sección Gestionar Contenido Estático -->
    <div id="contenido" style="display:none; margin-top:1rem;">
        <h3>Gestionar Contenido Estático</h3>
        <label>Título de la Página:</label>
        <input type="text" placeholder="Ej. Sobre Nosotros">
        <label>Contenido:</label>
        <textarea rows="4">Texto existente...</textarea>
        <button>Guardar Contenido</button>
    </div>

</main>
    <?php include 'views/cabeza/footer.php'; ?>

<script>
function toggleSeccion(id) {
    const secciones = ['parametros', 'revisarOfertas', 'habilitarSecciones', 'categorias', 'contenido'];
    secciones.forEach(sec => {
        document.getElementById(sec).style.display = (sec === id ? 
            (document.getElementById(sec).style.display === 'none' ? 'block' : 'none') 
            : 'none');
    });
}

function aprobar(button){
    const row = button.closest('tr');
    if(row) row.cells[2].innerText = 'Aprobado';
}

function rechazar(button){
    const row = button.closest('tr');
    if(row) row.cells[2].innerText = 'Rechazado';
}
</script>

</body>
</html>