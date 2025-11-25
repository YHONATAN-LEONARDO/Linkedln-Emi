<?php
require_once __DIR__ . '/config/session.php';

// Si por alguna razón en session.php no se llamó a session_start(),
// nos aseguramos aquí:
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /portada.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <!-- ==========================
         Metadatos básicos del documento
         =========================== -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Viewport para móviles / responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Título de la pestaña -->
    <title>LinkedIn EMI</title>

    <!-- Descripción para buscadores -->
    <meta name="description"
        content="LinkedIn EMI es la plataforma profesional para estudiantes y egresados de la Escuela Militar de Ingeniería. Conecta con empresas, encuentra empleos y comparte tus proyectos.">

    <!-- Palabras clave (SEO básico) -->
    <meta name="keywords"
        content="LinkedIn EMI, EMI, bolsa de trabajo, empleos, prácticas, estudiantes, egresados, ingeniería, Bolivia">

    <!-- Autor del sitio -->
    <meta name="author" content="LinkedIn EMI">

    <!-- Control de robots (indexación) -->
    <meta name="robots" content="index, follow">

    <!-- Idioma principal del contenido -->
    <meta http-equiv="content-language" content="es">

    <!-- Color de la barra del navegador en móviles -->
    <meta name="theme-color" content="#007bff">
    <meta name="msapplication-TileColor" content="#007bff">

    <!-- ==========================
         Favicon / íconos usando /public/img/main.png
         =========================== -->
    <!-- Icono principal -->
    <link rel="icon" type="image/png" sizes="32x32" href="/public/img/main.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/public/img/main.png">

    <!-- Apple Touch Icon (para iOS) -->
    <link rel="apple-touch-icon" sizes="180x180" href="/public/img/main.png">

    <!-- Manifest (PWA opcional, solo si lo usas) -->
    <!-- <link rel="manifest" href="/public/manifest.json"> -->

    <!-- ==========================
         Canonical (puedes cambiar a tu dominio cuando lo tengas)
         =========================== -->
    <link rel="canonical" href="http://13.59.7.49/">

    <!-- ==========================
         Hojas de estilo globales
         =========================== -->
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">

    <!-- ==========================
         Google Fonts
         =========================== -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Arvo:ital,wght@0,400;0,700;1,400;1,700&family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Lobster&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Share+Tech&display=swap"
        rel="stylesheet">

    <!-- ==========================
         Open Graph (para compartir en redes)
         =========================== -->
    <meta property="og:title" content="LinkedIn EMI - Tu red profesional en la EMI">
    <meta property="og:description"
        content="Conecta con empresas, encuentra oportunidades laborales y comparte tus proyectos como estudiante o egresado de la Escuela Militar de Ingeniería.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://13.59.7.49/">
    <meta property="og:image" content="http://13.59.7.49/public/img/main.png">
    <meta property="og:site_name" content="LinkedIn EMI">
    <meta property="og:locale" content="es_BO">

    <!-- ==========================
         Twitter Cards
         =========================== -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="LinkedIn EMI - Oportunidades para la comunidad EMI">
    <meta name="twitter:description"
        content="Plataforma profesional para estudiantes y egresados de la EMI. Encuentra empleos, prácticas y contactos.">
    <meta name="twitter:image" content="http://13.59.7.49/public/img/main.png">
    <meta name="twitter:site" content="@emi">
    <meta name="twitter:creator" content="@emi">

    <!-- ==========================
         Iconos (Ionicons, si usas <ion-icon>)
         =========================== -->
    <script type="module"
        src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule
        src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <!-- ==========================
         Estilos base rápidos en línea (opcionales)
         =========================== -->
    <style>
        :root {
            --color-primario: #007bff;
            --color-primario-oscuro: #0056b3;
            --color-secundario: #10b981;
            --color-fondo: #f3f4f6;
            --color-texto: #111827;
            --color-texto-suave: #6b7280;
            --color-borde-suave: #e5e7eb;
            --radius-base: 0.75rem;
            --sombra-suave: 0 10px 25px rgba(15, 23, 42, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background-color: var(--color-fondo);
            color: var(--color-texto);
            font-family: "Montserrat", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a {
            color: var(--color-primario);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        a:hover {
            color: var(--color-primario-oscuro);
        }

        .contenedor {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        /* Cabecera fija opcional (si usas .nav-principal) */
        .nav-principal {
            background-color: #ffffff;
            border-bottom: 1px solid var(--color-borde-suave);
            box-shadow: var(--sombra-suave);
        }

        /* Ajustes responsive generales */
        @media (max-width: 1024px) {
            .contenedor {
                padding: 0 0.75rem;
            }
        }

        @media (max-width: 640px) {
            body {
                font-size: 0.95rem;
            }
        }
    </style>

    <!-- ==========================
         Datos estructurados JSON-LD (Organization)
         =========================== -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "LinkedIn EMI",
            "url": "http://13.59.7.49/",
            "logo": "http://13.59.7.49/public/img/main.png",
            "sameAs": [
                "https://www.facebook.com/",
                "https://www.linkedin.com/",
                "https://www.instagram.com/"
            ],
            "description": "Plataforma profesional para estudiantes y egresados de la Escuela Militar de Ingeniería.",
            "address": {
                "@type": "PostalAddress",
                "addressCountry": "BO",
                "addressLocality": "La Paz",
                "streetAddress": "Escuela Militar de Ingeniería"
            }
        }
    </script>
</head>


<body>
    <?php include __DIR__ . '/views/cabeza/header.php'; ?>

    <h1 id="titulo-trabajo">
        <span class="texto-titulo">CREANDO MI FUTURO PROFESIONAL</span>
    </h1>

    <style>
        /* Contenedor del título */
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

        /* Estado oculto para el cambio suave (fade) */
        #titulo-trabajo .texto-titulo.oculto {
            opacity: 0;
            transform: translateY(10px);
        }

        /* Efecto extra al pasar el mouse */
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

    <!-- 🔁 SCRIPT PARA ROTAR LAS FRASES DEL TÍTULO -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Frases que irán rotando en el título
            const frases = [
                "CREANDO MI FUTURO PROFESIONAL",
                "TRABAJANDO DURO POR MIS METAS",
                "CADA DÍA MÁS CERCA DEL ÉXITO",
                "DISCIPLINA, ENFOQUE Y CONSTANCIA",
                "MI TRABAJO HABLA POR MÍ"
            ];

            const spanTexto = document.querySelector("#titulo-trabajo .texto-titulo");
            if (!spanTexto) return;

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
        });
    </script>

    <!-- Aquí va tu feed / contenido principal -->
    <?php include __DIR__ . '/views/principal/principal-index.php'; ?>

    <?php include __DIR__ . '/views/cabeza/footer.php'; ?>
</body>

</html>