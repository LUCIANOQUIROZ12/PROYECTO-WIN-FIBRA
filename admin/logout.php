<?php
session_start();
$_SESSION = array(); // Vaciar variables de sesión
session_destroy(); // Destruir la sesión
header("Location: login.php"); // Redirigir al login
exit;
?>