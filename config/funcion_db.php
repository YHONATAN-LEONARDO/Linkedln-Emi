<?php
/**
 * ------------------------------------------------------------
 * FUNCION_DB.PHP
 * Archivo de funciones para manipular la base de datos
 * Requiere: database.php (con la conexión PDO activa)
 * ------------------------------------------------------------
 */

require_once __DIR__ . '/database.php';


/* ============================================================
   ⚙ FUNCIONES GENERALES
   ============================================================ */

/**
 * Ejecutar una consulta SELECT y devolver todos los resultados.
 * @param string $sql
 * @param array  $params
 * @return array
 */
function dbSelect($sql, $params = [])
{
    global $conn;
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("dbSelect error: " . $e->getMessage());
        return [];
    }
}

/**
 * Ejecutar una consulta SELECT y devolver una sola fila.
 * @param string $sql
 * @param array  $params
 * @return array|null
 */
function dbSelectOne($sql, $params = [])
{
    global $conn;
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("dbSelectOne error: " . $e->getMessage());
        return null;
    }
}

/**
 * Ejecutar un INSERT, UPDATE o DELETE.
 * @param string $sql
 * @param array  $params
 * @return bool
 */
function dbExecute($sql, $params = [])
{
    global $conn;
    try {
        $stmt = $conn->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("dbExecute error: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtener el último ID insertado (IDENTITY)
 */
function dbLastId()
{
    global $conn;
    try {
        $result = $conn->query("SELECT SCOPE_IDENTITY() AS id");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['id'] : null;
    } catch (PDOException $e) {
        error_log("dbLastId error: " . $e->getMessage());
        return null;
    }
}

/* ============================================================
   👤 FUNCIONES DE USUARIOS
   ============================================================ */

/**
 * Registrar un nuevo usuario
 */
function registrarUsuario($nombre, $correo, $password, $rol_id = 3)
{
    $hash = hash('sha256', $password);

    $sql = "INSERT INTO usuarios (rol_id, nombre, correo, password, estado, creado_en)
            VALUES (?, ?, ?, ?, 'activo', GETDATE())";

    return dbExecute($sql, [$rol_id, $nombre, $correo, $hash]);
}

/**
 * Obtener usuario por correo
 */
function obtenerUsuarioPorCorreo($correo)
{
    $sql = "SELECT TOP 1 * FROM usuarios WHERE correo = ?";
    return dbSelectOne($sql, [$correo]);
}

/**
 * Validar credenciales de usuario (login)
 */
function validarLogin($correo, $password)
{
    $usuario = obtenerUsuarioPorCorreo($correo);
    if ($usuario && $usuario['password'] === hash('sha256', $password)) {
        return $usuario;
    }
    return false;
}

/* ============================================================
   📰 FUNCIONES DE PUBLICACIONES
   ============================================================ */

/**
 * Crear una nueva publicación
 */
function crearPublicacion($usuario_id, $contenido, $imagen = null)
{
    $sql = "INSERT INTO publicaciones (usuario_id, contenido, imagen, creado_en)
            VALUES (?, ?, ?, GETDATE())";
    return dbExecute($sql, [$usuario_id, $contenido, $imagen]);
}

/**
 * Obtener publicaciones con datos de usuario
 */
function obtenerPublicaciones()
{
    $sql = "SELECT p.id, p.contenido, p.imagen, p.creado_en,
                   u.nombre, u.foto
            FROM publicaciones p
            INNER JOIN usuarios u ON p.usuario_id = u.id
            ORDER BY p.creado_en DESC";
    return dbSelect($sql);
}

/* ============================================================
   💼 FUNCIONES DE OFERTAS / CONVOCATORIAS
   ============================================================ */

/**
 * Crear una nueva oferta
 */
function crearOferta($usuario_id, $categoria_id, $subcategoria_id, $titulo, $descripcion, $ubicacion, $tipo_jornada, $modalidad)
{
    $sql = "INSERT INTO ofertas
            (usuario_id, categoria_id, subcategoria_id, titulo, descripcion, ubicacion, tipo_jornada, modalidad, estado, publicado_en)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_revision', GETDATE())";

    return dbExecute($sql, [
        $usuario_id, $categoria_id, $subcategoria_id, $titulo, $descripcion, $ubicacion, $tipo_jornada, $modalidad
    ]);
}

/**
 * Listar todas las ofertas aprobadas
 */
function obtenerOfertasPublicadas()
{
    $sql = "SELECT o.*, c.nombre AS categoria, s.nombre AS subcategoria, u.nombre AS autor
            FROM ofertas o
            LEFT JOIN categorias c ON o.categoria_id = c.id
            LEFT JOIN subcategorias s ON o.subcategoria_id = s.id
            INNER JOIN usuarios u ON o.usuario_id = u.id
            WHERE o.estado = 'aprobado'
            ORDER BY o.publicado_en DESC";
    return dbSelect($sql);
}

/* ============================================================
   🛡 FUNCIONES DE ACTIVIDADES Y ALERTAS
   ============================================================ */

/**
 * Registrar una actividad (log)
 */
function registrarActividad($usuario_id, $accion, $descripcion, $ip)
{
    $sql = "INSERT INTO actividades (usuario_id, accion, descripcion, ip, creado_en)
            VALUES (?, ?, ?, ?, GETDATE())";
    return dbExecute($sql, [$usuario_id, $accion, $descripcion, $ip]);
}

/**
 * Crear una alerta de seguridad
 */
function crearAlerta($usuario_id, $tipo, $detalle)
{
    $sql = "INSERT INTO alertas_seguridad (usuario_id, tipo, detalle, atendido, creado_en)
            VALUES (?, ?, ?, 0, GETDATE())";
    return dbExecute($sql, [$usuario_id, $tipo, $detalle]);
}

/* ============================================================
   🧾 FUNCIONES AUXILIARES
   ============================================================ */

/**
 * Obtener IP del cliente
 */
function obtenerIpCliente()
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
?>