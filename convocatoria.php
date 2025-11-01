<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convocatoria</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arvo:ital,wght@0,400;0,700;1,400;1,700&family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Lobster&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Share+Tech&display=swap" rel="stylesheet">

</head>

<body>
    <?php include 'views/cabeza/header.php'; ?>
    <main class="mar convo-principal">

        <!-- Botón para mostrar/ocultar filtros -->
        <div class="acciones">
            <button class="btn-btn" onclick="toggleFiltros()">Mostrar/Ocultar Filtros</button>
            <button class="btn-btn" onclick="toggleCrear()">Crear Convocatoria</button>
            <button class="btn-btn" onclick="toggleEditar()">Editar Convocatoria</button>
        </div>

        <!-- Sección de filtros (oculta por defecto) -->
        <div class="filtros" id="filtros" style="display:none; margin-top:1rem;">
            <h2>Filtros de Postulaciones</h2>
            <label for="nivel">Nivel de estudios:</label>
            <select id="nivel">
                <option>Licenciatura</option>
                <option>Maestría</option>
            </select>

            <label for="experiencia">Experiencia mínima (años):</label>
            <input type="number" id="experiencia" min="0" placeholder="Ej. 2">

            <label for="habilidades">Habilidades específicas:</label>
            <input type="text" id="habilidades" placeholder="Java, Python, Marketing">

            <label for="estado">Estado de la postulación:</label>
            <select id="estado">
                <option>En revisión</option>
                <option>Aceptado</option>
                <option>Rechazado</option>
            </select>

            <label for="ubicacion">Ubicación geográfica:</label>
            <input type="text" id="ubicacion" placeholder="Ciudad o País">

            <button class="btn-btn" onclick="aplicarFiltros()">Aplicar Filtros</button>
        </div>

        <!-- Formulario Crear Convocatoria -->
        <div id="crear-convocatoria" style="display:none; margin-top:1rem;">
            <h3>Crear Convocatoria</h3>
            <label>Título:</label>
            <input type="text" placeholder="Ej. Asistente Virtual">
            <label>Descripción:</label>
            <textarea rows="3" placeholder="Descripción detallada..."></textarea>
            <label>Documento adjunto:</label>
            <input type="file">
            <button class="btn-btn">Guardar</button>
        </div>

        <!-- Formulario Editar Convocatoria -->
        <div id="editar-convocatoria" style="display:none; margin-top:1rem;">
            <h3>Editar Convocatoria</h3>
            <select>
                <option>Asistente Virtual</option>
                <option>Marketing Specialist</option>
            </select>
            <label>Título:</label>
            <input type="text" value="Asistente Virtual">
            <label>Descripción:</label>
            <textarea rows="3">Descripción existente...</textarea>
            <label>Documento adjunto:</label>
            <input type="file">
            <button class="btn-btn">Guardar Cambios</button>
        </div>

        <!-- Tabla de convocatorias -->
        <div class="tabla-convocatorias" style="margin-top:1rem;">
            <h2>Convocatorias</h2>
            <table border="1" cellpadding="5" cellspacing="0">
                <thead>
                    <tr>
                        <td>Convocatoria</td>
                        <td>Título</td>
                        <td>Descripción</td>
                        <td>Documento</td>
                        <td>Postulantes</td>
                        <td>Acciones</td>
                    </tr>
                </thead>
                <tbody id="listado-convocatorias">
                    <tr>
                        <td>1</td>
                        <td>Asistente Virtual</td>
                        <td>Apoyo administrativo remoto</td>
                        <td>CV_Asistente.pdf</td>
                        <td>
                            <ul>
                                <li>Juan Pérez</li>
                                <li>María López</li>
                            </ul>
                        </td>
                        <td>
                            <button class="btn-btn" onclick="toggleEvaluar('evaluar1')">Evaluar Postulantes</button>
                            <button class="btn-btn" onclick="cerrarConvocatoria(this)">Cerrar Convocatoria</button>
                            <!-- Sección evaluar -->
                            <div id="evaluar1" style="display:none; margin-top:0.5rem;">
                                <table border="1" cellpadding="3" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <td>Nombre</td>
                                            <td>Experiencia</td>
                                            <td>Calificación</td>
                                            <td>Acciones</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Juan Pérez</td>
                                            <td>3 años</td>
                                            <td>
                                                <select>
                                                    <option>0</option>
                                                    <option>1</option>
                                                    <option>2</option>
                                                    <option>3</option>
                                                    <option>4</option>
                                                    <option>5</option>
                                                </select>
                                            </td>
                                            <td><button class="btn-btn">Guardar</button></td>
                                        </tr>
                                        <tr>
                                            <td>María López</td>
                                            <td>5 años</td>
                                            <td>
                                                <select>
                                                    <option>0</option>
                                                    <option>1</option>
                                                    <option>2</option>
                                                    <option>3</option>
                                                    <option>4</option>
                                                    <option>5</option>
                                                </select>
                                            </td>
                                            <td><button class="btn-btn">Guardar</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </main>

    <script>
        function toggleFiltros() {
            const f = document.getElementById('filtros');
            f.style.display = (f.style.display === 'none') ? 'block' : 'none';
        }

        function toggleCrear() {
            const crear = document.getElementById('crear-convocatoria');
            crear.style.display = (crear.style.display === 'none') ? 'block' : 'none';
        }

        function toggleEditar() {
            const editar = document.getElementById('editar-convocatoria');
            editar.style.display = (editar.style.display === 'none') ? 'block' : 'none';
        }

        function toggleEvaluar(id) {
            const sec = document.getElementById(id);
            sec.style.display = (sec.style.display === 'none') ? 'block' : 'none';
        }

        function cerrarConvocatoria(button) {
            const row = button.closest('tr');
            if (row) {
                row.remove();
            }
        }
    </script>



    <?php include 'views/cabeza/footer.php'; ?>

</body>

</html>