<?php
/**
 * Archivo: admin/portadas.php
 * Descripción: Gestor CRUD para las portadas/banners del Carrusel Principal.
 */

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../config/conexion.php';

$mensaje = '';
$tipo_mensaje = '';

$directorio_subida = '../uploads/portadas/';
if (!file_exists($directorio_subida)) {
    mkdir($directorio_subida, 0777, true);
}

// ==========================================
// 1. PROCESAR SUBIDA (CREATE) - Ahora el título es Opcional
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'agregar') {
    // Si viene vacío, lo dejamos como un string en blanco
    $alt_text = !empty(trim($_POST['alt_text'])) ? trim($_POST['alt_text']) : '';
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $archivo = $_FILES['imagen'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($extension, $extensiones_permitidas)) {
            $nombre_nuevo = uniqid('banner_') . '.' . $extension;
            $ruta_destino = $directorio_subida . $nombre_nuevo;
            
            // Ruta relativa para la BD
            $ruta_db = 'uploads/portadas/' . $nombre_nuevo;
            
            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                try {
                    $stmt = $conexion->prepare("INSERT INTO portadas (imagen_url, alt_text, estado) VALUES (:imagen_url, :alt_text, 1)");
                    $stmt->bindParam(':imagen_url', $ruta_db);
                    $stmt->bindParam(':alt_text', $alt_text);
                    $stmt->execute();
                    
                    $mensaje = "¡Portada publicada con éxito!";
                    $tipo_mensaje = "success";
                } catch (PDOException $e) {
                    $mensaje = "Error en BD: " . $e->getMessage();
                    $tipo_mensaje = "error";
                }
            } else {
                $mensaje = "Error al mover el archivo al servidor.";
                $tipo_mensaje = "error";
            }
        } else {
            $mensaje = "Formato no permitido. Usa JPG, PNG o WEBP.";
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "Debes seleccionar una imagen válida.";
        $tipo_mensaje = "error";
    }
}

// ==========================================
// 2. PROCESAR ELIMINACIÓN (DELETE)
// ==========================================
if (isset($_GET['eliminar'])) {
    $id_eliminar = filter_var($_GET['eliminar'], FILTER_VALIDATE_INT);
    
    if ($id_eliminar) {
        try {
            $stmt = $conexion->prepare("SELECT imagen_url FROM portadas WHERE id = :id");
            $stmt->bindParam(':id', $id_eliminar);
            $stmt->execute();
            $portada = $stmt->fetch();
            
            if ($portada) {
                $ruta_archivo_fisico = '../' . $portada['imagen_url'];
                $stmt_del = $conexion->prepare("DELETE FROM portadas WHERE id = :id");
                $stmt_del->bindParam(':id', $id_eliminar);
                
                if ($stmt_del->execute()) {
                    if (file_exists($ruta_archivo_fisico)) {
                        unlink($ruta_archivo_fisico);
                    }
                    $mensaje = "Portada eliminada.";
                    $tipo_mensaje = "success";
                }
            }
        } catch (PDOException $e) {
            $mensaje = "Error al eliminar.";
            $tipo_mensaje = "error";
        }
    }
}

// ==========================================
// 3. OBTENER PORTADAS (READ)
// ==========================================
$lista_portadas = [];
try {
    $stmt = $conexion->query("SELECT * FROM portadas ORDER BY id DESC");
    $lista_portadas = $stmt->fetchAll();
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Portadas | Admin AIK</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --admin-bg-page: #f4f7f6;
            --admin-primary: #ff5a00;
            --admin-primary-dark: #e04f00;
            --admin-dark: #1a1a2e;
            --text-main: #333333;
            --text-muted: #888888;
            --card-bg: #ffffff;
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: var(--admin-bg-page); color: var(--text-main); }
        .admin-layout { display: flex; min-height: 100vh; }
        .main-content { flex: 1; margin-left: 260px; padding: 30px 40px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .topbar-title h1 { font-size: 1.8rem; font-weight: 700; color: var(--admin-dark); }
        .topbar-title p { color: var(--text-muted); font-size: 0.9rem; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
        .alert-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .upload-card { background: var(--card-bg); padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 40px; }
        .upload-card h3 { margin-bottom: 20px; font-size: 1.2rem; color: var(--admin-dark); border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; }
        .upload-form { display: flex; gap: 20px; align-items: flex-end; }
        .form-group { flex: 1; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; color: var(--text-muted); }
        .form-group input[type="text"], .form-group input[type="file"] { width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.95rem; }
        .form-group input[type="file"] { padding: 7px 15px; background: #f9f9f9; }
        .btn-submit { background: var(--admin-primary); color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: var(--transition); height: 43px; display: flex; align-items: center; gap: 8px; }
        .btn-submit:hover { background: var(--admin-primary-dark); }
        .portadas-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }
        .portada-card { background: var(--card-bg); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.03); transition: var(--transition); border: 1px solid #eee; }
        .portada-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        .portada-img { width: 100%; height: 180px; object-fit: cover; border-bottom: 1px solid #eee; background-color: #f0f0f0; }
        .portada-info { padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .portada-details h4 { font-size: 1rem; color: var(--admin-dark); font-weight: 600; margin-bottom: 5px; }
        .portada-details p { font-size: 0.75rem; color: var(--text-muted); }
        .btn-delete { background: #fee2e2; color: #dc2626; border: none; width: 40px; height: 40px; border-radius: 8px; cursor: pointer; transition: var(--transition); display: flex; justify-content: center; align-items: center; font-size: 1.1rem; text-decoration: none; }
        .btn-delete:hover { background: #dc2626; color: white; }
        .empty-state { text-align: center; padding: 50px; color: var(--text-muted); background: white; border-radius: 12px; grid-column: 1 / -1; }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; padding: 20px; }
            .upload-form { flex-direction: column; align-items: stretch; }
            .btn-submit { justify-content: center; }
        }
    </style>
</head>
<body>

    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="topbar">
                <div class="topbar-title">
                    <h1>Banners y Portadas</h1>
                    <p>Gestiona las imágenes del carrusel principal de la web</p>
                </div>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <i class="fa-solid <?php echo $tipo_mensaje == 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="upload-card">
                <h3><i class="fa-solid fa-cloud-arrow-up" style="color:var(--admin-primary);"></i> Subir Nueva Portada</h3>
                <form action="portadas.php" method="POST" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="accion" value="agregar">
                    
                    <div class="form-group">
                        <label>Nombre de la campaña / Texto Alternativo</label>
                        <!-- AQUÍ SE QUITÓ EL "required" Y SE AÑADIÓ "Opcional" -->
                        <input type="text" name="alt_text" placeholder="Ej: Promo Dúos Julio (Opcional)">
                    </div>
                    
                    <div class="form-group">
                        <label>Seleccionar Imagen (Resolución ideal: 1920x600px)</label>
                        <input type="file" name="imagen" accept="image/png, image/jpeg, image/jpg, image/webp" required>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-upload"></i> Publicar
                    </button>
                </form>
            </div>

            <h3 style="margin-bottom: 20px; color: var(--admin-dark); font-size: 1.2rem;">Portadas Activas en la Web</h3>
            
            <div class="portadas-grid">
                <?php if (count($lista_portadas) > 0): ?>
                    <?php foreach ($lista_portadas as $portada): ?>
                        <div class="portada-card">
                            <img src="../<?php echo htmlspecialchars($portada['imagen_url']); ?>" alt="Portada" class="portada-img">
                            
                            <div class="portada-info">
                                <div class="portada-details">
                                    <!-- Mostramos 'Campaña Sin Título' si se subió vacío -->
                                    <h4><?php echo !empty($portada['alt_text']) ? htmlspecialchars($portada['alt_text']) : '<i>Campaña Sin Título</i>'; ?></h4>
                                    <p>Subido el: <?php echo date('d/m/Y', strtotime($portada['fecha_creacion'])); ?></p>
                                </div>
                                
                                <a href="portadas.php?eliminar=<?php echo $portada['id']; ?>" class="btn-delete" onclick="return confirm('¿Estás seguro de eliminar esta portada permanentemente?');" title="Eliminar">
                                    <i class="fa-regular fa-trash-can"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-regular fa-images" style="font-size: 3rem; color: #ddd; margin-bottom: 15px;"></i>
                        <p>No hay portadas subidas. Se mostrará el diseño naranja por defecto en la página web.</p>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

</body>
</html>