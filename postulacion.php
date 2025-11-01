<?php
// postulaciones.php
session_start();
require_once 'config/database.php'; // Ajusta ruta según tu proyecto

// Validar usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /views/usuario/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener postulaciones del usuario
$stmt = $conn->prepare("
    SELECT p.id, o.titulo AS oferta_titulo, o.ubicacion, p.estado, p.creado_en
    FROM postulaciones p
    JOIN ofertas o ON p.oferta_id = o.id
    WHERE p.usuario_id = :usuario_id
    ORDER BY p.creado_en DESC
");
$stmt->bindParam(':usuario_id', $usuario_id);
$stmt->execute();
$postulaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mis Postulaciones - LinkedIn EMI</title>
<link rel="stylesheet" href="/public/css/normalize.css">
<link rel="stylesheet" href="/public/css/styles.css">
<style>
.postulacion-container {
    width: 90%;
    max-width: 900px;
    margin: 2rem auto;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    font-family: 'Roboto', sans-serif;
}

.postulacion-container h1 {
    font-size: 2rem;
    color: #0077b5;
    margin-bottom: 1rem;
    text-align: center;
}

.postulacion-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.2rem;
    border: 1px solid #ccc;
    border-radius: 0.8rem;
    background-color: #fff;
    gap: 1rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.postulacion-info p {
    margin: 0.3rem 0;
    font-size: 1.2rem;
}

.postulacion-actions button {
    padding: 0.6rem 1.2rem;
    font-size: 1rem;
    border: none;
    border-radius: 0.5rem;
    background-color: #0077b5;
    color: #fff;
    cursor: pointer;
    transition: background 0.3s;
}

.postulacion-actions button:hover {
    background-color: #005f8d;
}

p.no-postulaciones {
    text-align: center;
    font-size: 1.3rem;
    margin-top: 2rem;
}
</style>
</head>
<body>

<?php include 'views/cabeza/header.php'; ?>

<main class="postulacion-container">
    <h1>Mis Postulaciones</h1>

    <?php if (count($postulaciones) > 0): ?>
        <?php foreach ($postulaciones as $post): ?>
            <div class="postulacion-card">
                <div class="postulacion-info">
                    <p><strong><?= htmlspecialchars($post['oferta_titulo']) ?></strong></p>
                    <p><?= htmlspecialchars($post['ubicacion']) ?> · hace <?php 
                        $diff = time() - strtotime($post['creado_en']);
                        if ($diff < 3600) echo intval($diff / 60) . ' minutos';
                        elseif ($diff < 86400) echo intval($diff / 3600) . ' horas';
                        elseif ($diff < 604800) echo intval($diff / 86400) . ' días';
                        else echo intval($diff / 604800) . ' semanas';
                    ?></p>
                    <p>Estado: <strong><?= htmlspecialchars($post['estado']) ?></strong></p>
                </div>
                <div class="postulacion-actions">
                    <form method="POST" action="cancelar_postulacion.php">
                        <input type="hidden" name="postulacion_id" value="<?= $post['id'] ?>">
                        <button type="submit">Cancelar Postulación</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-postulaciones">No tienes postulaciones aún.</p>
    <?php endif; ?>
</main>

<?php include 'views/cabeza/footer.php'; ?>

</body>
</html>
