<?php
/**
 * Archivo: admin/catalogos.php
 * Descripción: Gestor CRUD Dedicado para Tarifarios (Solución a fallos silenciosos de carga).
 */

session_start();
// Previene que PHP corte la ejecución si el archivo tarda mucho en subir
set_time_limit(0); 

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../config/conexion.php';

$mensaje = '';
$tipo_mensaje = '';

// ==========================================
// 1. INTERCEPTOR DE FALLOS SILENCIOSOS DE PHP
// Si el archivo supera el post_max_size, PHP vacía $_POST y $_FILES. 
// Esto detecta esa caída exacta.
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $limite_actual = ini_get('post_max_size');
    $mensaje = "Error CRÍTICO de Servidor: El archivo supera el límite absoluto de tu servidor ($limite_actual). Debes abrir tu php.ini, buscar 'post_max_size' y 'upload_max_filesize', y subirlos a 50M.";
    $tipo_mensaje = "error";
}

// ==========================================
// 2. AUTO-MIGRACIÓN DE BASE DE DATOS Y CARPETAS
// ==========================================
$directorio_subida = '../uploads/catalogos/';
if (!file_exists($directorio_subida)) {
    mkdir($directorio_subida, 0777, true);
}

try {
    $sql_tabla = "CREATE TABLE IF NOT EXISTS catalogos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(150) NOT NULL,
        archivo_url VARCHAR(255) NOT NULL,
        peso_mb DECIMAL(5,2) DEFAULT 0,
        fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    $conexion->exec($sql_tabla);
} catch (PDOException $e) {}

// ==========================================
// 3. PROCESAR SUBIDA (CREATE)
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'subir_documento') {
    $titulo = trim($_POST['titulo']);
    
    if (empty($titulo)) {
        $mensaje = "Debes ingresar un título para el documento.";
        $tipo_mensaje = "error";
    } elseif (!isset($_FILES['archivo_doc']) || $_FILES['archivo_doc']['error'] == UPLOAD_ERR_INI_SIZE) {
        $limite_actual = ini_get('upload_max_filesize');
        $mensaje = "Error CRÍTICO: El archivo supera el 'upload_max_filesize' de tu servidor ($limite_actual). Modifícalo en php.ini a 50M.";
        $tipo_mensaje = "error";
    } elseif ($_FILES['archivo_doc']['error'] !== UPLOAD_ERR_OK) {
        $mensaje = "Error técnico al subir: Código " . $_FILES['archivo_doc']['error'];
        $tipo_mensaje = "error";
    } else {
        $archivo = $_FILES['archivo_doc'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensiones_permitidas = ['pdf', 'doc', 'docx'];
        
        if (in_array($extension, $extensiones_permitidas)) {
            $limite_bytes = 50 * 1024 * 1024; // 50 Megabytes
            
            if ($archivo['size'] <= $limite_bytes) {
                $nombre_nuevo = 'Catalogo_' . uniqid() . '.' . $extension;
                $ruta_destino = $directorio_subida . $nombre_nuevo;
                $ruta_db = 'uploads/catalogos/' . $nombre_nuevo;
                
                $peso_mb = round($archivo['size'] / 1048576, 2);
                
                if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                    try {
                        $stmt = $conexion->prepare("INSERT INTO catalogos (titulo, archivo_url, peso_mb) VALUES (:titulo, :archivo_url, :peso_mb)");
                        $stmt->bindParam(':titulo', $titulo);
                        $stmt->bindParam(':archivo_url', $ruta_db);
                        $stmt->bindParam(':peso_mb', $peso_mb);
                        $stmt->execute();
                        
                        header("Location: catalogos.php?msg=success");
                        exit;
                    } catch (PDOException $e) {
                        $mensaje = "Error en BD: " . $e->getMessage();
                        $tipo_mensaje = "error";
                    }
                } else {
                    $mensaje = "Fallo de permisos: PHP no pudo guardar el archivo en la carpeta 'uploads/catalogos/'.";
                    $tipo_mensaje = "error";
                }
            } else {
                $mensaje = "El documento es demasiado pesado. El límite máximo es de 50MB.";
                $tipo_mensaje = "error";
            }
        } else {
            $mensaje = "Por razones de seguridad, solo se permiten archivos .PDF, .DOC o .DOCX";
            $tipo_mensaje = "error";
        }
    }
}

// ==========================================
// 4. PROCESAR ELIMINACIÓN (DELETE)
// ==========================================
if (isset($_GET['eliminar'])) {
    $id_eliminar = filter_var($_GET['eliminar'], FILTER_VALIDATE_INT);
    if ($id_eliminar) {
        try {
            $stmt = $conexion->prepare("SELECT archivo_url FROM catalogos WHERE id = :id");
            $stmt->bindParam(':id', $id_eliminar);
            $stmt->execute();
            $doc = $stmt->fetch();
            
            if ($doc) {
                $ruta_archivo_fisico = '../' . $doc['archivo_url'];
                $stmt_del = $conexion->prepare("DELETE FROM catalogos WHERE id = :id");
                $stmt_del->bindParam(':id', $id_eliminar);
                
                if ($stmt_del->execute()) {
                    if (file_exists($ruta_archivo_fisico)) {
                        unlink($ruta_archivo_fisico);
                    }
                    header("Location: catalogos.php?msg=deleted");
                    exit;
                }
            }
        } catch (PDOException $e) {}
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'success') { $mensaje = "Catálogo publicado exitosamente."; $tipo_mensaje = "success"; }
    if ($_GET['msg'] == 'deleted') { $mensaje = "Documento eliminado del servidor."; $tipo_mensaje = "success"; }
}

// ==========================================
// 5. LECTURA DE CATÁLOGOS (READ)
// ==========================================
$lista_catalogos = [];
try {
    $stmt = $conexion->query("SELECT * FROM catalogos ORDER BY fecha_subida DESC");
    $lista_catalogos = $stmt->fetchAll();
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Tarifarios | Admin AIK</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --admin-bg-page: #f4f7f6; --admin-primary: #ff5a00; --admin-primary-dark: #e04f00; --admin-dark: #1a1a2e; --text-main: #333333; --text-muted: #888888; --card-bg: #ffffff; --transition: all 0.3s ease; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: var(--admin-bg-page); color: var(--text-main); }
        .admin-layout { display: flex; min-height: 100vh; }
        .main-content { flex: 1; margin-left: 260px; padding: 30px 40px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .topbar-title h1 { font-size: 1.8rem; font-weight: 700; color: var(--admin-dark); }
        .topbar-title p { color: var(--text-muted); font-size: 0.9rem; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; font-weight: 500; display: flex; align-items: center; gap: 10px; line-height: 1.4; }
        .alert-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

        .form-card { background: var(--card-bg); padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 40px; border-top: 4px solid var(--admin-primary); }
        .form-card h3 { margin-bottom: 25px; font-size: 1.2rem; color: var(--admin-dark); display: flex; align-items: center; gap: 10px; }
        
        .upload-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; align-items: start; }
        
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; color: var(--admin-dark); }
        .form-group input[type="text"] { width: 100%; padding: 12px 15px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.95rem; transition: var(--transition); background: #f8fafc; margin-bottom: 20px; }
        .form-group input[type="text"]:focus { border-color: var(--admin-primary); outline: none; background: #fff; box-shadow: 0 0 0 3px rgba(255,90,0,0.1); }
        
        .btn-submit { background: var(--admin-primary); color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: var(--transition); display: flex; align-items: center; gap: 8px; font-size: 1rem; width: 100%; justify-content: center; }
        .btn-submit:hover { background: var(--admin-primary-dark); }

        .dropzone-area { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 40px 20px; text-align: center; background: #f8fafc; cursor: pointer; transition: var(--transition); position: relative; }
        .dropzone-area:hover, .dropzone-area.dragover { border-color: var(--admin-primary); background: rgba(255,90,0,0.02); }
        .dropzone-area input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .dropzone-icon { font-size: 3rem; color: #94a3b8; margin-bottom: 15px; transition: var(--transition); }
        .dropzone-area:hover .dropzone-icon { color: var(--admin-primary); }
        .dropzone-text { font-weight: 600; color: var(--admin-dark); font-size: 1.1rem; margin-bottom: 5px; }
        .dropzone-subtext { font-size: 0.85rem; color: var(--text-muted); }
        .file-name-display { margin-top: 15px; font-weight: 600; color: var(--admin-primary); font-size: 0.9rem; word-break: break-all; }

        .pdf-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .pdf-card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px 20px; display: flex; align-items: center; gap: 15px; transition: var(--transition); position: relative; }
        .pdf-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); border-color: #cbd5e1; }
        
        .pdf-icon { font-size: 2.5rem; }
        .pdf-info { flex: 1; overflow: hidden; }
        .pdf-title { font-weight: 700; color: var(--admin-dark); font-size: 1rem; margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pdf-meta { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; }
        
        .pdf-actions { display: flex; gap: 8px; }
        .btn-icon { width: 35px; height: 35px; border-radius: 8px; display: flex; justify-content: center; align-items: center; text-decoration: none; transition: var(--transition); font-size: 0.9rem; }
        .btn-view { background: #eff6ff; color: #3b82f6; }
        .btn-view:hover { background: #3b82f6; color: white; }
        .btn-del { background: #fef2f2; color: #ef4444; border: none; cursor: pointer; }
        .btn-del:hover { background: #ef4444; color: white; }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; padding: 20px; }
            .upload-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="topbar">
                <div class="topbar-title">
                    <h1>Catálogos y Tarifarios</h1>
                    <p>Sube y administra documentos (PDF, DOC, DOCX) legales y comerciales.</p>
                </div>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <i class="fa-solid <?php echo $tipo_mensaje == 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                    <span><?php echo $mensaje; ?></span>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <h3><i class="fa-solid fa-file-arrow-up" style="color:var(--admin-primary);"></i> Subir Nuevo Documento</h3>
                
                <form action="catalogos.php" method="POST" enctype="multipart/form-data" id="upload-form">
                    <input type="hidden" name="accion" value="subir_documento">
                    
                    <div class="upload-grid">
                        <div class="upload-left">
                            <div class="form-group">
                                <label>Título del Documento *</label>
                                <input type="text" name="titulo" placeholder="Ej: Tarifario WIN Dúos 2026" required autocomplete="off">
                            </div>
                            <button type="submit" class="btn-submit" id="btn-submit">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Publicar Documento
                            </button>
                        </div>

                        <div class="upload-right">
                            <div class="dropzone-area" id="dropzone">
                                <input type="file" name="archivo_doc" id="archivo_doc" accept=".pdf,.doc,.docx" required>
                                <i class="fa-solid fa-cloud-arrow-up dropzone-icon"></i>
                                <div class="dropzone-text">Arrastra tu PDF o Word aquí</div>
                                <div class="dropzone-subtext">Formatos: .PDF, .DOC, .DOCX (Límite 50MB)</div>
                                <div class="file-name-display" id="file-name"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <h3 style="margin-bottom: 20px; color: var(--admin-dark); font-size: 1.2rem;">Repositorio de Documentos</h3>
            
            <div class="pdf-grid">
                <?php if (count($lista_catalogos) > 0): ?>
                    <?php foreach ($lista_catalogos as $doc): 
                        $ext = strtolower(pathinfo($doc['archivo_url'], PATHINFO_EXTENSION));
                        $icono_clase = 'fa-file-lines'; 
                        $icono_color = '#64748b'; 
                        
                        if ($ext === 'pdf') {
                            $icono_clase = 'fa-file-pdf';
                            $icono_color = '#ef4444'; 
                        } elseif ($ext === 'doc' || $ext === 'docx') {
                            $icono_clase = 'fa-file-word';
                            $icono_color = '#2563eb'; 
                        }
                    ?>
                        <div class="pdf-card">
                            <i class="fa-solid <?php echo $icono_clase; ?> pdf-icon" style="color: <?php echo $icono_color; ?>;"></i>
                            <div class="pdf-info" title="<?php echo htmlspecialchars($doc['titulo']); ?>">
                                <div class="pdf-title"><?php echo htmlspecialchars($doc['titulo']); ?></div>
                                <div class="pdf-meta">
                                    <?php echo date('d M, Y', strtotime($doc['fecha_subida'])); ?> • <?php echo $doc['peso_mb']; ?> MB • <?php echo strtoupper($ext); ?>
                                </div>
                            </div>
                            <div class="pdf-actions">
                                <a href="../<?php echo $doc['archivo_url']; ?>" target="_blank" class="btn-icon btn-view" title="Ver / Descargar">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                                <a href="catalogos.php?eliminar=<?php echo $doc['id']; ?>" class="btn-icon btn-del" onclick="return confirm('¿Seguro que deseas borrar este documento permanentemente?');" title="Eliminar">
                                    <i class="fa-regular fa-trash-can"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 50px; background: white; border-radius: 12px; color: var(--text-muted); border: 1px dashed #cbd5e1;">
                        <i class="fa-regular fa-folder-open" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;"></i>
                        <p>Aún no has subido ningún documento.</p>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <script>
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('archivo_doc');
        const fileNameDisplay = document.getElementById('file-name');
        const uploadForm = document.getElementById('upload-form');
        const btnSubmit = document.getElementById('btn-submit');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) { e.preventDefault(); e.stopPropagation(); }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.remove('dragover'), false);
        });

        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                const fileName = file.name;
                const ext = fileName.slice((Math.max(0, fileName.lastIndexOf(".")) || Infinity) + 1).toLowerCase();
                
                const allowedExts = ['pdf', 'doc', 'docx'];

                if (allowedExts.includes(ext)) {
                    const maxSize = 50 * 1024 * 1024;
                    if (file.size <= maxSize) {
                        fileNameDisplay.innerHTML = `<i class="fa-solid fa-check"></i> Archivo listo: ${file.name}`;
                        fileNameDisplay.style.color = "#22c55e";
                    } else {
                        fileNameDisplay.innerHTML = `<i class="fa-solid fa-xmark"></i> Error: El archivo supera los 50MB.`;
                        fileNameDisplay.style.color = "#ef4444";
                        this.value = ""; 
                    }
                } else {
                    fileNameDisplay.innerHTML = `<i class="fa-solid fa-xmark"></i> Error: Solo archivos PDF o Word.`;
                    fileNameDisplay.style.color = "#ef4444";
                    this.value = ""; 
                }
            }
        });

        // Feedback UX de Carga
        uploadForm.addEventListener('submit', function() {
            if(fileInput.files.length > 0) {
                btnSubmit.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Subiendo documento...';
                btnSubmit.style.pointerEvents = 'none';
                btnSubmit.style.opacity = '0.8';
            }
        });
    </script>
</body>
</html>