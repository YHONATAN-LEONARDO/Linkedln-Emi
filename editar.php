<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - LinkedIn Emi</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arvo:ital,wght@0,400;0,700;1,400;1,700&family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Lobster&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Share+Tech&display=swap" rel="stylesheet">


<body>

    <?php include 'views/cabeza/header.php'; ?>

    <section class="editar-perfil-sec">
        <h1 class="editar-perfil-titulo">Editar Información Personal</h1>

        <form action="editar.php" method="post" enctype="multipart/form-data" class="editar-perfil-form">
            <label for="nombre" class="label-nombre">Nombre completo:</label><br>
            <input type="text" id="nombre" name="nombre" class="input-nombre" value="Yhonatan Leonardo Mamani Torrez"><br><br>

            <label for="educacion" class="label-educacion">Educación:</label><br>
            <input type="text" id="educacion" name="educacion" class="input-educacion" value='Escuela Militar de Ingeniería "Mcal. Antonio José de Sucre"'><br><br>

            <label for="ubicacion" class="label-ubicacion">Ubicación:</label><br>
            <input type="text" id="ubicacion" name="ubicacion" class="input-ubicacion" value="La Paz, La Paz, Bolivia"><br><br>

            <label for="email" class="label-email">Email:</label><br>
            <input type="email" id="email" name="email" class="input-email" value="yhonatan@example.com"><br><br>

            <label for="telefono" class="label-telefono">Teléfono:</label><br>
            <input type="text" id="telefono" name="telefono" class="input-telefono" value="+591 12345678"><br><br>

            <label for="nacimiento" class="label-nacimiento">Fecha de nacimiento:</label><br>
            <input type="date" id="nacimiento" name="nacimiento" class="input-nacimiento" value="2002-01-01"><br><br>

            <label for="foto" class="label-foto">Cambiar foto de perfil:</label><br>
            <input type="file" id="foto" name="foto" class="input-foto"><br><br>

            <label for="cv" class="label-cv">Subir CV:</label><br>
            <input type="file" id="cv" name="cv" class="input-cv"><br><br>

            <button type="submit" class="btn-guardar">Guardar cambios</button>
            <a href="perfil.php" class="btn-volver wq">Volver</a>
        </form>
    </section>

    <?php include 'views/cabeza/footer.php'; ?>

</body>

</html>