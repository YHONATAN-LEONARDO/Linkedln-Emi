<?php
// /procesos/evaluar_postulantes.php

session_start();
require_once __DIR__ . '/../config/database.php';
if (file_exists(__DIR__ . '/../config/session.php')) {
    require_once __DIR__ . '/../config/session.php';
}

// Validar sesión (si tienes función propia)
if (function_exists('verificarSesion')) {
    verificarSesion(); // aquí puedes limitar a empresa/admin si quieres
}

// Verificar usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /views/usuario/login.php');
    exit;
}

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /convocatoria.php');
    exit;
}

$usuarioEvaluador = (int)$_SESSION['usuario_id'];

// Debe venir desde el form de evaluación
$oferta_id = isset($_POST['oferta_id']) ? (int)$_POST['oferta_id'] : 0;
$guardar_para = isset($_POST['guardar_para']) ? (int)$_POST['guardar_para'] : 0; // id de la postulacion

if ($oferta_id <= 0 || $guardar_para <= 0) {
    header('Location: /convocatoria.php');
    exit;
}

// Arrays enviados desde el formulario
$calificaciones = $_POST['calificacion'] ?? [];
$estados        = $_POST['estado'] ?? [];

// Obtener datos para esa postulación específica
$nuevaCalificacion = null;
$nuevoEstado       = null;

if (isset($calificaciones[$guardar_para])) {
    $nuevaCalificacion = (int)$calificaciones[$guardar_para];
}

if (isset($estados[$guardar_para])) {
    $tmpEstado = strtolower(trim($estados[$guardar_para]));
    // Validamos que sea uno de los valores esperados
    $validos = ['en_revision', 'aceptado', 'rechazado'];
    if (in_array($tmpEstado, $validos, true)) {
        $nuevoEstado = $tmpEstado;
    } else {
        $nuevoEstado = 'en_revision';
    }
} else {
    $nuevoEstado = 'en_revision';
}

try {
    $conn->beginTransaction();

    // 1) Obtener info de la postulación (para notificación)
    $stmt = $conn->prepare("
        SELECT p.id, p.usuario_id, p.oferta_id, p.estado, p.calificacion,
               o.titulo AS oferta_titulo
        FROM postulaciones p
        JOIN ofertas o ON o.id = p.oferta_id
        WHERE p.id = :postulacion_id AND p.oferta_id = :oferta_id
    ");
    $stmt->execute([
        ':postulacion_id' => $guardar_para,
        ':oferta_id'      => $oferta_id
    ]);
    $postulacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$postulacion) {
        // No existe esa postulación/oferta
        $conn->rollBack();
        header('Location: /convocatoria.php');
        exit;
    }

    $usuarioPostulante = (int)$postulacion['usuario_id'];
    $tituloOferta      = $postulacion['oferta_titulo'] ?? 'una de tus postulaciones';

    // 2) Actualizar la postulación (estado + calificación)
    $sqlUpdate = "
        UPDATE postulaciones
        SET estado = :estado,
            calificacion = :calificacion,
            notificado = 0   -- para indicar que hay cambio pendiente
        WHERE id = :id
    ";

    $stmtUp = $conn->prepare($sqlUpdate);
    $stmtUp->bindValue(':estado', $nuevoEstado, PDO::PARAM_STR);

    if ($nuevaCalificacion === null) {
        $stmtUp->bindValue(':calificacion', null, PDO::PARAM_NULL);
    } else {
        $stmtUp->bindValue(':calificacion', $nuevaCalificacion, PDO::PARAM_INT);
    }

    $stmtUp->bindValue(':id', $guardar_para, PDO::PARAM_INT);
    $stmtUp->execute();

    // 3) Crear una notificación para el postulante
    //    Texto según el nuevo estado
    $tituloNotif  = '';
    $mensajeNotif = '';

    switch ($nuevoEstado) {
        case 'aceptado':
            $tituloNotif  = '¡Buena noticia sobre tu postulación!';
            $mensajeNotif = "Tu postulación a la oferta \"{$tituloOferta}\" ha sido *ACEPTADA* por el reclutador.\n\nRevisa los detalles y mantente atento a próximos pasos.";
            break;
        case 'rechazado':
            $tituloNotif  = 'Actualización sobre tu postulación';
            $mensajeNotif = "Tu postulación a la oferta \"{$tituloOferta}\" ha sido *RECHAZADA* por el reclutador.\n\nNo te desanimes, cada intento suma experiencia. Sigue postulando y mejorando tu perfil.";
            break;
        default: // en_revision u otro
            $tituloNotif  = 'Tu postulación está en revisión';
            $mensajeNotif = "Tu postulación a la oferta \"{$tituloOferta}\" se encuentra en estado de *REVISIÓN*.\n\nEl reclutador está evaluando tu perfil. Te avisaremos cuando haya una decisión.";
            break;
    }

    // Insertar en la tabla notificaciones
    $stmtNotif = $conn->prepare("
        INSERT INTO notificaciones (usuario_id, titulo, mensaje, leido, creado_en)
        VALUES (:usuario_id, :titulo, :mensaje, 0, GETDATE())
    ");

    $stmtNotif->execute([
        ':usuario_id' => $usuarioPostulante,
        ':titulo'     => $tituloNotif,
        ':mensaje'    => $mensajeNotif
    ]);

    $conn->commit();

    // Redirigir de regreso a la gestión de convocatorias
    header('Location: /convocatoria.php');
    exit;

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error al evaluar postulante: " . $e->getMessage();
    exit;
}
