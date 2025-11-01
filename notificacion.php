<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        main {
            max-width: 600px;
            /* Más estrecho para una columna */
            margin: 2rem auto;
            padding: 0 1rem;
            margin-top: 20rem;
        }

        h1 {
            text-align: center;
            margin-bottom: 2rem;
        }

        .cards-container {
            display: flex;
            flex-direction: column;
            /* Una sola columna */
            gap: 1rem;
        }

        .card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        .card ion-icon {
            font-size: 2rem;
            color: #007bff;
            flex-shrink: 0;
        }

        .card-content h3 {
            margin: 0;
            margin-bottom: 0.3rem;
            color: #007bff;
        }

        .card-content p {
            margin: 0.2rem 0;
            color: #555;
        }

        .card-content small {
            display: block;
            margin-top: 0.3rem;
            color: #999;
        }
    </style>
</head>

<body>

    <?php include 'views/cabeza/header.php'; ?>

    <main>
        <h1>Notificaciones</h1>

        <div class="cards-container">
            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Oferta Aceptada</h3>
                    <p>Tu postulación a "Desarrollador Web" ha sido aceptada.</p>
                    <small>Hace 2 horas</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Nuevo Mensaje</h3>
                    <p>Has recibido un mensaje de la empresa "Tech Solutions".</p>
                    <small>Hace 3 horas</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Recordatorio</h3>
                    <p>No olvides actualizar tu CV antes del 5 de noviembre.</p>
                    <small>Hace 5 horas</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Oferta Rechazada</h3>
                    <p>Tu postulación a "Analista de Datos" ha sido rechazada.</p>
                    <small>Ayer</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Nuevo Evento</h3>
                    <p>Se ha creado un webinar sobre ciberseguridad el 10 de noviembre.</p>
                    <small>Ayer</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Alerta</h3>
                    <p>Se ha detectado actividad sospechosa en tu cuenta.</p>
                    <small>Hace 2 días</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Actualización de Perfil</h3>
                    <p>Tu perfil ha sido actualizado correctamente.</p>
                    <small>Hace 2 días</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Nuevo Curso Disponible</h3>
                    <p>Curso de Python avanzado ahora disponible en la plataforma.</p>
                    <small>Hace 3 días</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Oferta Nueva</h3>
                    <p>"Ingeniero de Software" publicado en tu área de interés.</p>
                    <small>Hace 3 días</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Notificación General</h3>
                    <p>La plataforma estará en mantenimiento el 7 de noviembre.</p>
                    <small>Hace 4 días</small>
                </div>
            </div>
            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Entrevista Programada</h3>
                    <p>Tienes una entrevista con "InnovaSoft" el 3 de noviembre a las 10:00 AM.</p>
                    <small>Hace 1 hora</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Nueva Postulación</h3>
                    <p>Has postulado a la oferta "Administrador de Sistemas".</p>
                    <small>Hace 2 horas</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Actualización de Oferta</h3>
                    <p>La empresa "CyberCorp" actualizó los requisitos de la vacante.</p>
                    <small>Hace 4 horas</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Solicitud Aprobada</h3>
                    <p>Tu solicitud para el programa de pasantías fue aprobada.</p>
                    <small>Hace 6 horas</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Mensaje Nuevo</h3>
                    <p>El reclutador de "TechWorld" te ha enviado un nuevo mensaje.</p>
                    <small>Hace 7 horas</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Oferta Expirada</h3>
                    <p>La oferta "Analista Junior" ha expirado.</p>
                    <small>Ayer</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Perfil Incompleto</h3>
                    <p>Completa tu perfil para aumentar tus posibilidades de selección.</p>
                    <small>Hace 1 día</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Nuevo Curso Recomendado</h3>
                    <p>Se recomienda el curso "Hacking Ético Básico" para tu perfil.</p>
                    <small>Hace 2 días</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Oferta Guardada</h3>
                    <p>Has guardado la oferta "Ingeniero DevOps" en tus favoritos.</p>
                    <small>Hace 2 días</small>
                </div>
            </div>

            <div class="card">
                <ion-icon name="notifications-circle-outline"></ion-icon>
                <div class="card-content">
                    <h3>Nueva Empresa Registrada</h3>
                    <p>La empresa "BlueNet Security" se unió a la plataforma.</p>
                    <small>Hace 3 días</small>
                </div>
            </div>

        </div>
    </main>

    <?php include 'views/cabeza/footer.php'; ?>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>

</html>