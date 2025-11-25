<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LinkedIn EMI - Portada</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ================================
           RESET
        ================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            font-size: 62.5%;
        }

        body {
            font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f3f3f4 url("/public/img/page.png") no-repeat top center fixed;

            /* ---- MENOS ZOOM ---- */
            background-size: 80%;
            /* <--- AJUSTA AQUÍ */
            background-position: center 40px;
            /* efecto parallax suave en PC */
            color: #111827;
            min-height: 100vh;
        }

        @media (max-width: 768px) {
            body {
                background-attachment: scroll;
            }
        }


        a {
            text-decoration: none;
            color: inherit;
        }

        /* ================================
           ANIMACIONES
        ================================ */

        /* Entrada suave hacia arriba */
        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(2.4rem);
            }

            60% {
                opacity: 0.9;
                transform: translateY(0.6rem);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Desaparición de la intro */
        @keyframes fadeOutIntro {
            0% {
                opacity: 1;
                transform: scale(1);
            }

            100% {
                opacity: 0;
                transform: scale(0.97);
                filter: blur(2px);
            }
        }

        /* Logo flotando libre, sin encierro */
        @keyframes logoFloat {
            0% {
                transform: translateY(0) scale(1);
            }

            50% {
                transform: translateY(-0.7rem) scale(1.03);
            }

            100% {
                transform: translateY(0) scale(1);
            }
        }

        /* Contenido principal */
        @keyframes contentIn {
            0% {
                opacity: 0;
                transform: translateY(2rem);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Tarjetas flotando sutilmente */
        @keyframes cardFloat {
            0% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-0.6rem);
            }

            100% {
                transform: translateY(0);
            }
        }

        /* Items de info uno por uno */
        @keyframes infoItemIn {
            0% {
                opacity: 0;
                transform: translateY(1.2rem);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Botón respirando */
        @keyframes buttonBreath {
            0% {
                transform: translateY(0);
                box-shadow: 0 0.8rem 1.8rem rgba(0, 0, 0, 0.18);
            }

            50% {
                transform: translateY(-0.18rem);
                box-shadow: 0 1.1rem 2.1rem rgba(0, 0, 0, 0.24);
            }

            100% {
                transform: translateY(0);
                box-shadow: 0 0.8rem 1.8rem rgba(0, 0, 0, 0.18);
            }
        }

        /* ================================
           PANTALLA DE ENTRADA (BLANCA)
        ================================ */

        .intro {
            position: fixed;
            inset: 0;
            background: #f3f3f4 url("/public/img/page.png") no-repeat top center fixed;

            /* ---- MENOS ZOOM ---- */
            background-size: 80%;
            /* <--- AJUSTA AQUÍ */
            background-position: center 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.8rem;
            z-index: 9999;
            animation: fadeInUp 0.6s ease-out;
        }

        .intro.hide {
            animation: fadeOutIntro 0.7s ease-in forwards;
            pointer-events: none;
        }

        /* Logo libre, sin caja */
        .intro-logo {
            width: min(26rem, 60vw);
            animation: logoFloat 3s ease-in-out infinite;
        }

        .intro-logo img {
            width: 100%;
            height: auto;
            object-fit: contain;
        }

        .intro-title {
            font-size: 2.8rem;
            font-weight: 700;
            color: #0066cc;
            letter-spacing: 0.03em;
            text-align: center;
        }

        .intro-text {
            font-size: 1.6rem;
            text-align: center;
            max-width: 32rem;
            color: #111827;
        }

        .intro-sub {
            font-size: 1.4rem;
            color: #004c99;
            text-align: center;
            max-width: 36rem;
        }

        /* ================================
           CONTENEDOR PRINCIPAL
        ================================ */

        .contenedor {
            max-width: 120rem;
            margin: 0 auto;
            padding: 2.4rem 2rem 3rem 2rem;
            opacity: 0;
            transform: translateY(2rem);
        }

        .contenedor.show {
            animation: contentIn 0.9s ease-out forwards;
        }

        /* ================================
           HEADER SIMPLE
        ================================ */

        .top-bar {
            background-color: #ffffff;
            border-radius: 1.2rem;
            padding: 1.6rem 2.2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.6rem;
            box-shadow: 0 0.4rem 1.2rem rgba(0, 0, 0, 0.08);
        }

        .top-left {
            display: flex;
            align-items: center;
            gap: 1.4rem;
        }

        .top-logo {
            width: 9rem;
            /* un poco más grande */
            height: auto;
            /* para que no lo apriete */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            /* sin padding */
            border-radius: 0;
            /* completamente libre */
            background: none;
            /* sin fondo */
            box-shadow: none;
            /* sin sombra */
        }

        /* Logo libre, sin límites */
        .top-logo img {
            width: 100%;
            height: auto;
            object-fit: contain;
            image-rendering: high-quality;
            transition: transform 0.25s ease, filter 0.25s ease;
        }

        /* Efecto suave al pasar el mouse (solo en PC) */
        @media (hover: hover) {
            .top-logo img:hover {
                transform: scale(1.06);
                filter: drop-shadow(0 0.4rem 1rem rgba(0, 0, 0, 0.15));
            }
        }

        /* Ajuste para móviles */
        @media (max-width: 480px) {
            .top-logo {
                width: 4.2rem;
            }
        }

        .top-title {
            display: flex;
            flex-direction: column;
        }

        .top-title span:first-child {
            font-size: 1.7rem;
            font-weight: 700;
            color: #0066cc;
        }

        .top-title span:last-child {
            font-size: 1.3rem;
            color: #004c99;
        }

        .top-right {
            font-size: 1.3rem;
            background-color: #e1e9ee;
            padding: 0.7rem 1.6rem;
            border-radius: 5rem;
            color: #111827;
            text-align: center;
        }

        /* ================================
           HERO PRINCIPAL
        ================================ */

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
            gap: 3rem;
            margin-top: 4rem;
            align-items: stretch;
        }

        .hero-texto {
            background-color: #ffffff;
            border-radius: 1.6rem;
            padding: 3rem;
            box-shadow: 0 0.6rem 1.6rem rgba(0, 0, 0, 0.1);
            animation: cardFloat 7s ease-in-out infinite;
        }

        .hero-kicker {
            font-size: 1.3rem;
            color: #004c99;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .hero-texto h2 {
            font-size: 3rem;
            color: #111827;
            margin-bottom: 1.8rem;
            line-height: 1.25;
        }

        .hero-texto h2 span {
            color: #0066cc;
        }

        .hero-texto p {
            font-size: 1.5rem;
            margin-bottom: 1.1rem;
            line-height: 1.6;
            color: #374151;
        }

        .hero-texto p strong {
            color: #004c99;
        }

        .botones {
            display: flex;
            flex-wrap: wrap;
            gap: 1.4rem;
            margin-top: 2.2rem;
        }

        .btn {
            border: none;
            border-radius: 5rem;
            padding: 1.2rem 2.6rem;
            font-size: 1.5rem;
            cursor: pointer;
            transition: background-color 0.25s ease, box-shadow 0.25s ease, transform 0.15s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
        }

        .btn-primario {
            background-color: #0066cc;
            color: #ffffff;
            box-shadow: 0 0.8rem 1.8rem rgba(0, 0, 0, 0.18);
            animation: buttonBreath 3.4s ease-in-out infinite;
        }

        .btn-primario:hover {
            background-color: #004c99;
        }

        .btn-secundario {
            background-color: #e1e9ee;
            color: #111827;
        }

        .btn-secundario:hover {
            background-color: #cfd8dc;
        }

        .hero-note {
            margin-top: 1.8rem;
            font-size: 1.3rem;
            color: #6b7280;
        }

        .hero-img-box {
            background-color: #ffffff;
            border-radius: 1.6rem;
            padding: 2.6rem;
            box-shadow: 0 0.6rem 1.6rem rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.6rem;
        }

        .hero-img-box img {
            width: 75%;
            max-width: 24rem;
        }

        .hero-img-box p {
            font-size: 1.4rem;
            text-align: center;
            color: #004c99;
            line-height: 1.5;
        }

        /* ================================
           SECCIÓN INFORMACIÓN EXTRA
        ================================ */

        .info {
            margin-top: 3.5rem;
            background-color: #ffffff;
            border-radius: 1.6rem;
            padding: 2.6rem;
            box-shadow: 0 0.6rem 1.6rem rgba(0, 0, 0, 0.08);
        }

        .info h3 {
            font-size: 2.1rem;
            color: #0066cc;
            margin-bottom: 1.3rem;
        }

        .info p {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #374151;
        }

        .info-lista {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.6rem;
            margin-top: 1.8rem;
        }

        .info-item {
            background-color: #f4f2ee;
            border-radius: 1.1rem;
            padding: 1.8rem;
            font-size: 1.4rem;
            opacity: 0;
            transform: translateY(1.2rem);
            animation: infoItemIn 0.7s ease-out forwards;
        }

        .info-item:nth-child(1) {
            animation-delay: 0.2s;
        }

        .info-item:nth-child(2) {
            animation-delay: 0.4s;
        }

        .info-item:nth-child(3) {
            animation-delay: 0.6s;
        }

        .info-item h4 {
            font-size: 1.6rem;
            color: #004c99;
            margin-bottom: 0.8rem;
        }

        .info-item p {
            font-size: 1.4rem;
            margin-bottom: 0;
        }

        /* ================================
           RESPONSIVE
        ================================ */

        @media (max-width: 900px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .hero-img-box {
                order: -1;
            }
        }

        @media (max-width: 768px) {
            .contenedor {
                padding: 2rem 1.6rem 2.6rem 1.6rem;
            }

            .top-bar {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-lista {
                grid-template-columns: 1fr;
            }

            .hero-texto,
            .hero-img-box,
            .info {
                padding: 2.2rem;
            }

            .hero-texto h2 {
                font-size: 2.4rem;
            }
        }

        @media (max-width: 480px) {
            .top-bar {
                padding: 1.4rem 1.6rem;
            }

            .hero {
                margin-top: 3rem;
            }

            .hero-texto h2 {
                font-size: 2.1rem;
            }

            .botones {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <!-- ENTRADA BLANCA 3s -->
    <div class="intro" id="intro">
        <div class="intro-logo">
            <img src="/public/img/main.png" alt="Logo LinkedIn EMI">
        </div>
        <!-- <h1 class="intro-title">Bienvenido</h1> -->
        <!-- <p class="intro-text">Aquí encontrarás tu oportunidad con la EMI.</p> -->
        <p class="intro-sub">Tu espacio para crecer profesionalmente comienza aquí.</p>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenedor" id="contenido">

        <!-- HEADER -->
        <header class="top-bar">
            <div class="top-left">
                <div class="top-logo">
                    <img src="/public/img/main.png" alt="Logo LinkedIn EMI">
                </div>
                <div class="top-title">
                    <span>LinkedIn EMI</span>
                    <span>Conectando talento EMI con oportunidades reales</span>
                </div>
            </div>
            <div class="top-right">
                Para continuar, solo debes iniciar sesión o registrarte.
            </div>
        </header>

        <!-- HERO PRINCIPAL -->
        <section class="hero">
            <article class="hero-texto">
                <p class="hero-kicker">Plataforma oficial para la comunidad EMI</p>
                <h2>
                    Aquí comienza tu <span>futuro profesional</span>.
                </h2>
                <p>
                    LinkedIn EMI está pensada para estudiantes y egresados de la
                    Escuela Militar de Ingeniería.
                </p>
                <p>
                    En un solo lugar tendrás acceso a oportunidades laborales<,
                    convocatorias y herramientas para dar tus primeros pasos en el mundo profesional.
                </p>
                <p>
                    Comparte tus logros, muestra tu perfil y conecta con empresas que confían en la formación EMI.
                </p>

                <div class="botones">
                    <a href="views/usuario/login.php">
                        <button class="btn btn-primario">Iniciar sesión</button>
                    </a>
                    <a href="views/usuario/registro.php">
                        <button class="btn btn-secundario">Registrarme</button>
                    </a>
                </div>

                <p class="hero-noe">
                    Solo necesitas una cuenta para empezar a construir tu presencia profesional dentro de la comunidad EMI.
                </p>
            </article>

            <aside class="hero-img-box">
                <img src="/public/img/main.png" alt="Logo EMI">
                <p>
                    Un solo lugar para reunir tu formación, experiencia y las oportunidades que están esperando por ti.
                </p>
            </aside>
        </section>

        <!-- SECCIÓN: QUIÉNES SOMOS / QUÉ TENDRÁS -->
        <section class="info">
            <h3>¿Quiénes somos?</h3>
            <p>
                Somos una plataforma hecha con la EMI y para la EMI: estudiantes, egresados y reclutadores que valoran
                el esfuerzo, la disciplina y la formación de la Escuela Militar de Ingeniería.
            </p>
            <p>
                Nuestra misión es acercarte a las mejores oportunidades de forma clara, ordenada y conectada con la
                realidad del mercado laboral.
            </p>

            <h3>¿Qué encontrarás aquí?</h3>
            <p>
                Todo está diseñado para ayudarte a dar el siguiente paso:
            </p>

            <div class="info-lista">
                <div class="info-item">
                    <h4>Ofertas de trabajo</h4>
                    <p>
                        Convocatorias y empleos alineados con tu perfil académico y profesional.
                    </p>
                </div>
                <div class="info-item">
                    <h4>Perfil profesional</h4>
                    <p>
                        Un espacio donde mostrar tu educación, proyectos, habilidades y tu CV.
                    </p>
                </div>
                <div class="info-item">
                    <h4>Conexión con empresas</h4>
                    <p>
                        Contacto directo con reclutadores y aliados que confían en el talento EMI.
                    </p>
                </div>
            </div>


        </section>
    </div>

    <script>
        // Mostrar intro blanca 3 segundos y luego el contenido
        window.addEventListener('load', function() {
            const intro = document.getElementById('intro');
            const contenido = document.getElementById('contenido');

            setTimeout(function() {
                intro.classList.add('hide');
                contenido.classList.add('show');
            }, 3000);
        });
    </script>
</body>

</html>