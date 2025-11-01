<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil LinkedIn Emi</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arvo:ital,wght@0,400;0,700;1,400;1,700&family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Lobster&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Share+Tech&display=swap" rel="stylesheet">

</head>

<body>

    <?php include 'views/cabeza/header.php'; ?>

    <section class="perfil-principal op">
        <div class="perfil-header">
            <img src="public/img/image.png" alt="Foto de perfil" class="perfil-img">
            <h1>Yhonatan Leonardo Mamani Torrez</h1>
            <p><strong>Educación:</strong> Escuela Militar de Ingeniería "Mcal. Antonio José de Sucre"</p>
            <p><strong>Ubicación:</strong> La Paz, La Paz, Bolivia</p>
            <p><strong>Email:</strong> yhonatan@example.com</p>
            <p><strong>Teléfono:</strong> +591 12345678</p>
            <p><strong>Fecha de nacimiento:</strong> 01/01/2002</p>
        </div>

        <div class="acciones-perfil">
            <a href="editar.php">

                <button>Editar información</button>
            </a>
            <a href="/">

                <button>Ver publicaciones</button>
            </a>
            <a href="postulacion.php">
                <button>Postulaciones</button>
            </a>
            
        </div>
    </section>

    <section class="actividad op">
        <h2>Actividad reciente</h2>
        <p>No hay publicaciones aún.</p>
    </section>

    <?php include 'views/cabeza/footer.php'; ?>

</body>

</html>