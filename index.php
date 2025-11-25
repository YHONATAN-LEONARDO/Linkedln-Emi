<?php
// 1️⃣ Incluir manejo de sesión (aquí normalmente va session_start())
require_once __DIR__ . '/config/session.php';

// 2️⃣ Si NO hay usuario logueado, mandamos a portada
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /portada.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkedIn EMI</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arvo:ital,wght@0,400;0,700;1,400;1,700&family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Lobster&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Share+Tech&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'views/cabeza/header.php'; ?>
    <h1 id="titulo-trabajo">
        <span class="texto-titulo">CREANDO MI FUTURO PROFESIONAL</span>

        <style>
            /* El H1 en sí */
            #titulo-trabajo {
                margin: 0;
                text-align: center;
                padding: 40px 20px;
                color: black !important;
                box-sizing: border-box;
            }

            /* Texto del título */
            #titulo-trabajo .texto-titulo {
                display: inline-block;
                text-transform: uppercase;
                letter-spacing: 0.18em;
                font-weight: 600;
                font-size: clamp(28px, 4vw, 40px);
                color: black !important;
                text-shadow:
                    0 0 6px rgba(255, 255, 255, 0.4),
                    0 0 18px rgba(255, 255, 255, 0.25);
                animation: brillo 3.5s ease-in-out infinite alternate;
                transition: opacity 0.4s ease, transform 0.4s ease;
                cursor: default;
            }

            /* Estado oculto para el cambio suave */
            #titulo-trabajo .texto-titulo.oculto {
                opacity: 0;
                transform: translateY(10px);
            }

            /* Efecto hover extra */
            #titulo-trabajo .texto-titulo:hover {
                text-shadow:
                    0 0 14px rgba(255, 255, 255, 0.9),
                    0 0 32px rgba(255, 255, 255, 0.6);
            }

            /* @keyframes para el brillo del texto */
            @keyframes brillo {
                0% {
                    text-shadow:
                        0 0 4px rgba(255, 255, 255, 0.2),
                        0 0 10px rgba(255, 255, 255, 0.15);
                }

                50% {
                    text-shadow:
                        0 0 10px rgba(255, 255, 255, 0.7),
                        0 0 26px rgba(255, 255, 255, 0.35);
                }

                100% {
                    text-shadow:
                        0 0 18px rgba(255, 255, 255, 1),
                        0 0 36px rgba(255, 255, 255, 0.7);
                }
            }
        </style>

        <script>
            // Frases que irán rotando en el título
            const frases = [
                "CREANDO MI FUTURO PROFESIONAL",
                "TRABAJANDO DURO POR MIS METAS",
                "CADA DÍA MÁS CERCA DEL ÉXITO",
                "DISCIPLINA, ENFOQUE Y CONSTANCIA",
                "MI TRABAJO HABLA POR MÍ"
            ];

            const spanTexto = document.querySelector("#titulo-trabajo .texto-titulo");
            let indice = 0;
            const tiempoCambio = 3200; // ms

            function cambiarTitulo() {
                // Fade out con clase
                spanTexto.classList.add("oculto");

                setTimeout(() => {
                    indice = (indice + 1) % frases.length;
                    spanTexto.textContent = frases[indice];
                    spanTexto.classList.remove("oculto");
                }, 400); // un poco más corto que el intervalo
            }

            setInterval(cambiarTitulo, tiempoCambio);
        </script>
    </h1>

    <?php include 'views/principal/principal-index.php'; ?>
    <?php include 'views/cabeza/footer.php'; ?>
</body>

</html>