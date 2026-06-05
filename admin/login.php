<?php
session_start();
require_once '../config/conexion.php';

$error = '';

// Si el usuario ya está logueado, lo mandamos al dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

// Procesar el formulario cuando se envía por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        try {
            // Consultar a la base de datos de forma segura
            $stmt = $conexion->prepare("SELECT id, username, password_hash FROM admins WHERE username = :username AND estado = 1 LIMIT 1");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch();
                $hashed_password = $row['password_hash'];

                // Verificar que la contraseña ingresada coincida con el Hash de la DB
                if (password_verify($password, $hashed_password)) {
                    // Contraseña correcta: Iniciar sesión
                    session_regenerate_id(); // Previene ataques de fijación de sesión
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $row['id'];
                    $_SESSION['admin_username'] = $row['username'];
                    
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = "La contraseña es incorrecta.";
                }
            } else {
                $error = "Usuario no encontrado o inactivo.";
            }
        } catch (PDOException $e) {
            $error = "Error del sistema. Intente más tarde.";
            // En producción registrar el error: error_log($e->getMessage());
        }
    } else {
        $error = "Por favor, ingrese usuario y contraseña.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Panel Administrador AIK</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 400px; text-align: center; }
        .login-logo { color: #ff5a00; font-size: 2rem; font-weight: 800; margin-bottom: 5px; line-height: 1; }
        .login-logo span { font-size: 0.8rem; color: #666; display: block; font-weight: 600; letter-spacing: 1px; }
        .login-subtitle { color: #888; font-size: 0.9rem; margin-bottom: 30px; }
        .form-group { text-align: left; margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; color: #333; }
        .form-group input { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; outline: none; font-size: 1rem; transition: 0.3s; }
        .form-group input:focus { border-color: #ff5a00; }
        .btn-login { background-color: #ff5a00; color: white; border: none; width: 100%; padding: 15px; border-radius: 8px; font-weight: 700; font-size: 1.1rem; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-login:hover { background-color: #e04f00; }
        .error-message { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 20px; border: 1px solid #f87171; }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-logo">
            AIK<span>PANEL ADMINISTRADOR</span>
        </div>
        <p class="login-subtitle">Ingresa tus credenciales para acceder</p>

        <?php if(!empty($error)): ?>
            <div class="error-message">
                <strong>Error:</strong> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="username" placeholder="Ej: admin" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">Ingresar al Panel</button>
        </form>
    </div>

</body>
</html>