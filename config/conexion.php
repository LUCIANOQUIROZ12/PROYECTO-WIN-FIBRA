<?php
/**
 * Archivo de Conexión a la Base de Datos
 * Ruta: /config/conexion.php
 */

$host     = 'localhost'; // Usualmente es localhost. Cámbialo si tu base de datos está en otro servidor.
$dbname   = 'winfibra_sistemaDB';
$username = 'winfibra_user';
$password = 'S#8Y3$S4^O#buYh%';

try {
    // 1. DSN (Data Source Name): Especifica el driver, host, nombre de DB y el charset
    // Nota: Usar utf8mb4 asegura compatibilidad completa con caracteres especiales y emojis.
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    
    // 2. Opciones de seguridad y configuración de PDO
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones si hay errores (ideal para debugging seguro)
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve los resultados como un array asociativo por defecto
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Desactiva la emulación para mayor seguridad real en consultas preparadas
    ];

    // 3. Crear la instancia de la conexión
    $conexion = new PDO($dsn, $username, $password, $opciones);

    // Mensaje de prueba (Descomenta la siguiente línea solo para comprobar que funciona, luego bórrala en producción)
    // echo "Conexión exitosa a la base de datos.";

} catch (PDOException $e) {
    // 4. Manejo de Errores
    // IMPORTANTE: En producción, NUNCA muestres $e->getMessage() al usuario final porque revela información sensible de tu servidor.
    
    // Guarda el error real en un log interno del servidor:
    error_log("Error de conexión PDO: " . $e->getMessage());
    
    // Muestra un mensaje genérico al usuario:
    die("Error crítico: No se pudo conectar a la base de datos. Por favor, comuníquese con el administrador del sistema.");
}
?>