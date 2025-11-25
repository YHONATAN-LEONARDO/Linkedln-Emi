<?php
// editar.php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /views/usuario/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Crear carpetas si no existen
$dirFotos = __DIR__ . '/uploads/fotos';
$dirCv    = __DIR__ . '/uploads/cv';

if (!file_exists($dirFotos)) mkdir($dirFotos, 0777, true);
if (!file_exists($dirCv))    mkdir($dirCv, 0777, true);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Campos del formulario
    $nombre           = $_POST['nombre']     ?? '';
    $educacion        = $_POST['educacion']  ?? ''; // lo guardaremos en columna "carrera"
    $ubicacion        = $_POST['ubicacion']  ?? ''; // lo guardaremos en "ubicacion_ciudad"
    $correo           = $_POST['email']      ?? '';
    $telefono         = $_POST['telefono']   ?? '';
    $fecha_nacimiento = $_POST['nacimiento'] ?? '';

    // ---- FOTO ----
    $foto_nombre = null;
    if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $nombreBase = time() . '_' . basename($_FILES['foto']['name']);
        $rutaRel    = 'uploads/fotos/' . $nombreBase;      // lo que se guarda en la BD
        $rutaFisica = $dirFotos . '/' . $nombreBase;       // ruta física

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaFisica)) {
            $foto_nombre = $rutaRel;
        }
    }

    // ---- CV ----
    $cv_nombre = null;
    if (!empty($_FILES['cv']['name']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $nombreBaseCv = time() . '_' . basename($_FILES['cv']['name']);
        $rutaRelCv    = 'uploads/cv/' . $nombreBaseCv;
        $rutaFisicaCv = $dirCv . '/' . $nombreBaseCv;

        if (move_uploaded_file($_FILES['cv']['tmp_name'], $rutaFisicaCv)) {
            $cv_nombre = $rutaRelCv;
        }
    }

    // IMPORTANTE: usar las columnas reales de la tabla usuarios
    // nombre, carrera, ubicacion_ciudad, correo, telefono, fecha_nacimiento, foto, cv
    $query = "UPDATE usuarios 
              SET nombre           = :nombre,
                  carrera          = :carrera,
                  ubicacion_ciudad = :ubicacion_ciudad,
                  correo           = :correo,
                  telefono         = :telefono,
                  fecha_nacimiento = :fecha_nacimiento";

    if ($foto_nombre) $query .= ", foto = :foto";
    if ($cv_nombre)   $query .= ", cv   = :cv";

    $query .= ", actualizado_en = GETDATE()
               WHERE id = :id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':nombre',           $nombre);
    $stmt->bindParam(':carrera',          $educacion);        // educación → carrera
    $stmt->bindParam(':ubicacion_ciudad', $ubicacion);        // ubicación → ubicacion_ciudad
    $stmt->bindParam(':correo',           $correo);
    $stmt->bindParam(':telefono',         $telefono);
    $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
    $stmt->bindParam(':id',               $usuario_id, PDO::PARAM_INT);

    if ($foto_nombre) $stmt->bindParam(':foto', $foto_nombre);
    if ($cv_nombre)   $stmt->bindParam(':cv',   $cv_nombre);

    $stmt->execute();

    header('Location: perfil.php');
    exit;
}

// Obtener datos actuales del usuario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - LinkedIn EMI</title>

    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">

    <style>
        body {
            font-family: "Montserrat", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            padding: 0;
            background: #f3f4f6;
        }

        .editar-perfil-sec {
            max-width: 720px;
            margin: 7rem auto 3rem;
            background: #ffffff;
            padding: 2rem 1.8rem;
            border-radius: 1rem;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.12);
        }

        .editar-perfil-titulo {
            font-size: 2.2rem;
            font-weight: 700;
            color: #0f172a;
            text-align: center;
            margin-bottom: 1.2rem;
        }

        .editar-perfil-sub {
            text-align: center;
            font-size: 1rem;
            color: #6b7280;
            margin-bottom: 1.5rem;
        }

        .editar-perfil-form {
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
        }

        .campo {
            display: flex;
            flex-direction: column;
            gap: .25rem;
        }

        .campo label {
            font-weight: 600;
            font-size: 1.05rem;
            color: #111827;
        }

        .campo input[type="text"],
        .campo input[type="email"],
        .campo input[type="date"],
        .campo input[type="file"] {
            width: 100%;
            padding: .8rem 1rem;
            font-size: 1.05rem;
            border-radius: .7rem;
            border: 1px solid #d1d5db;
            background: #f9fafb;
        }

        .campo input:focus {
            border-color: #0ea5e9;
            outline: none;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.25);
        }

        .hint {
            font-size: .85rem;
            color: #9ca3af;
        }

        .vista-previa {
            margin-top: .4rem;
            font-size: .9rem;
            color: #4b5563;
        }

        .vista-previa img {
            width: 10rem;
            height: auto;
            margin: 0 auto;
            border-radius: .5rem;
            border: 2px solid #e5e7eb;
        }

        .vista-previa a {
            color: #0ea5e9;
            font-weight: 600;
            text-decoration: none;
        }

        .vista-previa a:hover {
            text-decoration: underline;
        }

        .acciones-form {
            margin-top: 1.2rem;
            display: flex;
            flex-wrap: wrap;
            gap: .7rem;
        }

        .btn-guardar {
            flex: 1 1 180px;
            padding: .9rem 1.2rem;
            background: #0ea5e9;
            color: #ffffff;
            border: none;
            border-radius: .8rem;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            transition: background .2s, transform .1s, box-shadow .2s;
        }

        .btn-guardar:hover {
            background: #0284c7;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        }

        .btn-volver {
            flex: 1 1 140px;
            padding: .9rem 1.2rem;
            background: #e5e7eb;
            color: #111827;
            border-radius: .8rem;
            font-size: 1.05rem;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background .2s;
        }

        .btn-volver:hover {
            background: #d1d5db;
        }

        .mensaje-tip {
            margin-top: 1.2rem;
            font-size: .9rem;
            color: #6b7280;
            background: #eff6ff;
            border-radius: .7rem;
            padding: .6rem .8rem;
            border: 1px solid #bfdbfe;
        }

        .mensaje-tip strong {
            color: #1d4ed8;
        }

        /* Letra más grande en móviles */
        @media (max-width: 768px) {
            .editar-perfil-sec {
                margin: 6rem 1rem 2.5rem;
                padding: 1.6rem 1.3rem;
            }

            .editar-perfil-titulo {
                font-size: 2.3rem;
            }

            .campo label,
            .campo input,
            .btn-guardar,
            .btn-volver {
                font-size: 1.15rem;
            }
        }

        @media (max-width: 480px) {
            .editar-perfil-sec {
                margin: 5.5rem .8rem 2rem;
            }

            .editar-perfil-titulo {
                font-size: 2.4rem;
            }

            .campo label,
            .campo input,
            .btn-guardar,
            .btn-volver {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/views/cabeza/header.php'; ?>

    <section class="editar-perfil-sec">
        <h1 class="editar-perfil-titulo">Editar Información Personal</h1>
        <p class="editar-perfil-sub">
            Actualiza tu información para que los reclutadores te conozcan mejor.
        </p>

        <form action="editar.php" method="post" enctype="multipart/form-data" class="editar-perfil-form">

            <div class="campo">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre"
                    value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="educacion">Educación</label>
                <input type="text" id="educacion" name="educacion"
                    placeholder="Ej: Ingeniería de Sistemas - EMI"
                    value="<?= htmlspecialchars($usuario['carrera'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="ubicacion">Ubicación</label>
                <input type="text" id="ubicacion" name="ubicacion"
                    placeholder="Ciudad - País"
                    value="<?= htmlspecialchars($usuario['ubicacion_ciudad'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                    value="<?= htmlspecialchars($usuario['correo'] ?? '') ?>">
                <span class="hint">Este correo se usará para que las empresas puedan contactarte.</span>
            </div>

            <div class="campo">
                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono"
                    value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="nacimiento">Fecha de nacimiento</label>
                <input type="date" id="nacimiento" name="nacimiento"
                    value="<?= htmlspecialchars($usuario['fecha_nacimiento'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="foto">Cambiar foto de perfil</label>
                <input type="file" id="foto" name="foto" accept="image/*">
                <?php if (!empty($usuario['foto'])): ?>
                    <div class="vista-previa">
                        Foto actual:<br>
                        <img src="<?= htmlspecialchars($usuario['foto']) ?>" class="img-im"  alt="Foto perfil">
                    </div>
                <?php endif; ?>
            </div>

            <div class="campo">
                <label for="cv">Subir CV</label>
                <input type="file" id="cv" name="cv" accept=".pdf,.doc,.docx">
                <?php if (!empty($usuario['cv'])): ?>
                    <div class="vista-previa">
                        CV actual:
                        <a href="<?= htmlspecialchars($usuario['cv']) ?>" target="_blank">Ver CV</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="acciones-form">
                <button type="submit" class="btn-guardar">Guardar cambios</button>
                <a href="perfil.php" class="btn-volver">Volver al perfil</a>
            </div>

            <div class="mensaje-tip">
                <strong>Tip:</strong> Una foto profesional, un CV actualizado y datos correctos
                aumentan mucho tus posibilidades de ser contactado por empresas.
            </div>
        </form>
    </section>

    <?php include __DIR__ . '/views/cabeza/footer.php'; ?>

</body>

</html>