<?php
// database.php
$serverName = "localhost"; 
$dbName = "linkdin_emi";
$user = "sa";
$pass = "Liz12345";

try {
    // Crear conexión PDO para SQL Server con ODBC Driver 18
    $conn = new PDO("sqlsrv:Server=$serverName;Database=$dbName;Encrypt=optional;TrustServerCertificate=1", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,          // Manejo de errores
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC      // Devuelve arrays asociativos por defecto
    ]);

    // echo "Conexión exitosa a SQL Server";
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
