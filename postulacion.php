<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postulaciones</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arvo:ital,wght@0,400;0,700;1,400;1,700&family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Lobster&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Share+Tech&display=swap" rel="stylesheet">

</head>
<body>
  <?php include 'views/cabeza/header.php'; ?>
    <main class="postulacion-container">
        <h1>Mis Postulaciones</h1>

        <!-- Card de postulación 1 -->
        <div class="postulacion-card" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding: 1rem; border: 0.1rem solid #ccc; border-radius: 0.8rem;">
            <div class="postulacion-info">
                <p><strong>Real Estate Rental Virtual Assistant</strong></p>
                <p>Bolivia · hace 1 mes</p>
                <p>Estado: <strong>En revisión</strong></p>
            </div>
            <div class="postulacion-actions">
                <button class="btn-btn">Cancelar Postulación</button>
            </div>
        </div>

        <!-- Card de postulación 2 -->
        <div class="postulacion-card" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding: 1rem; border: 0.1rem solid #ccc; border-radius: 0.8rem;">
            <div class="postulacion-info">
                <p><strong>Marketing Specialist</strong></p>
                <p>Bolivia · hace 2 semanas</p>
                <p>Estado: <strong>Aceptada</strong></p>
            </div>
            <div class="postulacion-actions">
                <button class="btn-btn">Cancelar Postulación</button>
            </div>
        </div>

        <!-- Card de postulación 3 -->
        <div class="postulacion-card" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding: 1rem; border: 0.1rem solid #ccc; border-radius: 0.8rem;">
            <div class="postulacion-info">
                <p><strong>Software Developer</strong></p>
                <p>Bolivia · hace 3 días</p>
                <p>Estado: <strong>Rechazada</strong></p>
            </div>
            <div class="postulacion-actions">
                <button class="btn-btn">Cancelar Postulación</button>
            </div>
        </div>

    </main>
  <?php include 'views/cabeza/footer.php'; ?>

</body>
</html>
