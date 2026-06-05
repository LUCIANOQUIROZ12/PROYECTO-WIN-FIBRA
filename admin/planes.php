<?php
/**
 * Archivo: admin/planes.php
 * Descripción: Gestor CRUD Avanzado para Planes (WINTV, DGO, FONOWIN, GAMER).
 */

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../config/conexion.php';

$mensaje = '';
$tipo_mensaje = '';

// ==========================================
// 0. AUTO-MIGRACIÓN DE BASE DE DATOS (NIVEL SENIOR)
// ==========================================
try {
    $checkCol = $conexion->query("SHOW COLUMNS FROM planes LIKE 'subcategoria'");
    if ($checkCol->rowCount() == 0) { $conexion->exec("ALTER TABLE planes ADD subcategoria VARCHAR(50) DEFAULT NULL AFTER categoria"); }
    
    $checkCol2 = $conexion->query("SHOW COLUMNS FROM planes LIKE 'velocidad_base_mbps'");
    if ($checkCol2->rowCount() == 0) { $conexion->exec("ALTER TABLE planes ADD velocidad_base_mbps INT DEFAULT NULL AFTER velocidad_mbps"); }
    
    $checkCol3 = $conexion->query("SHOW COLUMNS FROM planes LIKE 'logo_url'");
    if ($checkCol3->rowCount() == 0) { $conexion->exec("ALTER TABLE planes ADD logo_url VARCHAR(255) DEFAULT NULL AFTER beneficios_extra"); }
} catch (PDOException $e) {}

// ==========================================
// 1. PROCESAR FORMULARIO (CREATE / UPDATE)
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion'])) {
    $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : 0;
    
    $categoria = $_POST['categoria'];
    $subcategoria = isset($_POST['subcategoria']) && !empty($_POST['subcategoria']) ? $_POST['subcategoria'] : null;
    $titulo = trim($_POST['titulo']);
    
    $velocidad = ($categoria === 'TRÍOS') ? 0 : (int)filter_var($_POST['velocidad_mbps'], FILTER_VALIDATE_INT);
    $velocidad_base = !empty($_POST['velocidad_base_mbps']) ? (int)filter_var($_POST['velocidad_base_mbps'], FILTER_VALIDATE_INT) : null;
    
    $precio_regular = filter_var($_POST['precio_regular'], FILTER_VALIDATE_FLOAT);
    $precio_promocional = !empty($_POST['precio_promocional']) ? filter_var($_POST['precio_promocional'], FILTER_VALIDATE_FLOAT) : null;
    $meses_promocion = !empty($_POST['meses_promocion']) ? filter_var($_POST['meses_promocion'], FILTER_VALIDATE_INT) : null;
    $beneficios_extra = !empty(trim($_POST['beneficios_extra'])) ? trim($_POST['beneficios_extra']) : null;
    
    $logo_url = !empty($_POST['logo_url']) ? $_POST['logo_url'] : null;

    if ($titulo && $precio_regular !== false) {
        try {
            if ($_POST['accion'] == 'agregar') {
                $sql = "INSERT INTO planes (categoria, subcategoria, titulo, velocidad_mbps, velocidad_base_mbps, precio_regular, precio_promocional, meses_promocion, beneficios_extra, logo_url, estado) 
                        VALUES (:categoria, :subcategoria, :titulo, :velocidad, :velocidad_base, :precio_regular, :precio_promocional, :meses_promocion, :beneficios_extra, :logo_url, 1)";
                $stmt = $conexion->prepare($sql);
                $mensaje = "¡Plan creado con éxito!";
            } elseif ($_POST['accion'] == 'editar' && $id > 0) {
                $sql = "UPDATE planes SET categoria = :categoria, subcategoria = :subcategoria, titulo = :titulo, velocidad_mbps = :velocidad, velocidad_base_mbps = :velocidad_base, 
                        precio_regular = :precio_regular, precio_promocional = :precio_promocional, 
                        meses_promocion = :meses_promocion, beneficios_extra = :beneficios_extra, logo_url = :logo_url 
                        WHERE id = :id";
                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $mensaje = "¡Plan actualizado correctamente!";
            }

            $stmt->bindParam(':categoria', $categoria);
            $stmt->bindParam(':subcategoria', $subcategoria);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':velocidad', $velocidad, PDO::PARAM_INT);
            $stmt->bindParam(':velocidad_base', $velocidad_base, PDO::PARAM_INT);
            $stmt->bindParam(':precio_regular', $precio_regular);
            $stmt->bindParam(':precio_promocional', $precio_promocional);
            $stmt->bindParam(':meses_promocion', $meses_promocion, PDO::PARAM_INT);
            $stmt->bindParam(':beneficios_extra', $beneficios_extra);
            $stmt->bindParam(':logo_url', $logo_url);
            
            if ($stmt->execute()) {
                header("Location: planes.php?msg=success");
                exit;
            }
        } catch (PDOException $e) {
            $mensaje = "Error en BD: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "Por favor, completa los campos obligatorios.";
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
            $stmt = $conexion->prepare("DELETE FROM planes WHERE id = :id");
            $stmt->bindParam(':id', $id_eliminar, PDO::PARAM_INT);
            $stmt->execute();
            header("Location: planes.php?msg=deleted");
            exit;
        } catch (PDOException $e) {}
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'success') { $mensaje = "Operación realizada con éxito."; $tipo_mensaje = "success"; }
    if ($_GET['msg'] == 'deleted') { $mensaje = "Plan eliminado permanentemente."; $tipo_mensaje = "success"; }
}

// ==========================================
// 3. OBTENER DATOS PARA EDICIÓN
// ==========================================
$plan_editar = null;
$modo_edicion = false;
if (isset($_GET['editar'])) {
    $id_editar = filter_var($_GET['editar'], FILTER_VALIDATE_INT);
    if ($id_editar) {
        $stmt = $conexion->prepare("SELECT * FROM planes WHERE id = :id");
        $stmt->bindParam(':id', $id_editar, PDO::PARAM_INT);
        $stmt->execute();
        $plan_editar = $stmt->fetch();
        if ($plan_editar) $modo_edicion = true;
    }
}

// ==========================================
// 4. LECTURA DE PLANES
// ==========================================
$lista_planes = [];
try {
    $stmt = $conexion->query("SELECT * FROM planes ORDER BY categoria, subcategoria, precio_regular ASC");
    $lista_planes = $stmt->fetchAll();
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Planes | Admin AIK</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
        .alert-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

        .form-card { background: var(--card-bg); padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 40px; border-top: 4px solid var(--admin-primary); }
        .form-card h3 { margin-bottom: 25px; font-size: 1.2rem; color: var(--admin-dark); display: flex; align-items: center; gap: 10px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; color: var(--admin-dark); }
        .form-group input, .form-group select { width: 100%; padding: 12px 15px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.95rem; transition: var(--transition); background: #f8fafc; }
        .form-group input:focus, .form-group select:focus { border-color: var(--admin-primary); outline: none; background: #fff; box-shadow: 0 0 0 3px rgba(255,90,0,0.1); }
        .form-group.full-width { grid-column: 1 / -1; }
        
        .btn-group { display: flex; gap: 15px; }
        .btn-submit { background: var(--admin-primary); color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: var(--transition); display: flex; align-items: center; gap: 8px; font-size: 1rem; }
        .btn-cancel { background: #e2e8f0; color: #475569; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; }

        .planes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }
        .card-replica { border-radius: 15px; padding: 30px 20px; text-align: center; position: relative; display: flex; flex-direction: column; transition: transform 0.3s; overflow: hidden; }
        .card-replica:hover { transform: translateY(-5px); }
        .admin-tag { position: absolute; top: 0; left: 0; right: 0; background: #eee; font-size: 0.7rem; padding: 4px; font-weight: 700; color: #666; letter-spacing: 1px; z-index: 10; border-radius: 15px 15px 0 0; }
        
        /* THEMES */
        .theme-duo { background: #fff; border: 2px solid var(--admin-primary); padding-top: 40px; }
        .theme-duo .c-title { color: var(--admin-primary); font-weight: 900; font-size: 1rem; margin-bottom: 5px; }
        .c-speed-base { font-size: 0.95rem; font-weight: 800; color: #888; text-decoration: line-through; margin-bottom: -5px; }
        .theme-duo .c-speed { color: var(--admin-primary); font-weight: 900; font-size: 2.8rem; line-height: 1; letter-spacing: -1px; margin-bottom: 5px; }
        .theme-duo .c-speed span { font-size: 1.2rem; letter-spacing: 0; }
        .theme-duo .c-promo { font-size: 0.75rem; font-weight: 700; margin-bottom: 20px; }
        .theme-duo .c-logo { font-size: 1.5rem; font-weight: 900; margin-bottom: 15px; color: #333; }
        .theme-duo .c-logo span { color: var(--admin-primary); }
        .theme-duo .c-pill { background: var(--admin-primary); color: white; border-radius: 30px; padding: 8px 15px; font-size: 1.5rem; font-weight: 900; margin: 15px auto 5px; width: 90%; }
        .theme-duo .c-pill span { font-size: 0.9rem; font-weight: 600; }
        .theme-duo .c-extra { font-size: 0.75rem; font-weight: 700; color: #333; margin-bottom: 5px; }
        .theme-duo .c-reg { font-size: 0.8rem; font-weight: 600; color: #666; margin-bottom: 20px; }
        
        /* =========================================================================
           NUEVOS ESTILOS TRÍOS ADMIN (TWO-TONE: BLANCO ARRIBA / COLOR ABAJO)
           ========================================================================= */
        .theme-trio { padding: 0 !important; border-width: 2px !important; border-style: solid !important; justify-content: space-between; }
        .theme-adicional-wintv { border-color: var(--admin-primary) !important; }
        .theme-adicional-dgo { border-color: #1da1f2 !important; }

        .trio-card-header { background: white; padding: 30px 15px; display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 100px; border-radius: 13px 13px 0 0; }
        .trio-card-body { padding: 30px 0 0 0; color: white; flex-grow: 1; display: flex; flex-direction: column; border-radius: 0 0 13px 13px; }

        .theme-adicional-wintv .trio-card-body { background: var(--admin-primary); }
        .theme-adicional-dgo .trio-card-body { background: #1da1f2; }

        .trio-card-body .c-pill { font-size: 2.5rem; font-weight: 900; margin-bottom: 5px; text-align: center; }
        .trio-card-body .c-pill span { font-size: 1.2rem; font-weight: 600; }
        .trio-card-body .c-extra { font-size: 0.9rem; font-weight: 700; margin-bottom: 25px; text-align: center; }
        .trio-card-body .btn-yellow { background: #ffcc00; color: #333; border: none; padding: 12px; border-radius: 30px; font-weight: 800; font-size: 1rem; width: 85%; margin: 0 auto 20px; display: block; text-align: center;}

        /* Header Custom para DGO */
        .dgo-custom-header { width: 100%; display: flex; flex-direction: column; border-radius: 13px 13px 0 0; overflow: hidden; background: white; }
        .dgo-top { padding: 25px 10px 30px 10px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; }
        .dgo-top .dgo-main-logo { max-height: 40px; margin-bottom: 8px; }
        .dgo-top .dgo-channels { font-size: 1rem; font-weight: 600; color: #1a1a1a; letter-spacing: -0.3px; font-family: 'Arial', sans-serif;}
        .dgo-bottom { background: #080808; padding: 18px 5px 15px 5px; position: relative; }
        .dgo-badge { background: #ff5a00; color: white; padding: 3px 15px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; position: absolute; top: -12px; left: 50%; transform: translateX(-50%); box-shadow: 0 4px 8px rgba(0,0,0,0.4); }
        .dgo-bottom-logos { display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; }
        .dgo-bottom-logos img { height: 14px; object-fit: contain; }
        .dgo-plus { color: #ff5a00; font-size: 1rem; font-weight: 900; line-height: 1; margin: 0 2px;}

        /* Fin de estilos TRÍOS ADMIN */

        .theme-gamer { background: #0a0a0a; border: 1px solid var(--admin-primary); padding-top: 40px; color: white; }
        .theme-gamer .c-title { color: var(--admin-primary); font-weight: 900; font-size: 1rem; margin-bottom: 5px; }
        .theme-gamer .c-speed { color: var(--admin-primary); font-weight: 900; font-size: 2.8rem; line-height: 1; letter-spacing: -1px; margin-bottom: 30px; }
        .theme-gamer .c-speed span { font-size: 1.2rem; }
        .theme-gamer .c-logo { font-size: 1.2rem; font-weight: 900; color: white; line-height: 1.1; margin-bottom: 30px; }
        .theme-gamer .c-logo span { font-size: 2.2rem; color: var(--admin-primary); display: block; letter-spacing: -1px; }
        .theme-gamer .c-pill { background: var(--admin-primary); color: white; border-radius: 30px; padding: 8px 15px; font-size: 1.5rem; font-weight: 900; margin: 15px auto 5px; width: 90%; }
        .theme-gamer .c-pill span { font-size: 0.9rem; }
        .theme-gamer .c-reg { font-size: 0.8rem; font-weight: 600; color: #888; margin-bottom: 20px; }
        .mesh-badge { position: absolute; right: 15px; top: 40%; background: #222; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; }

        /* Estilo para las imágenes oficiales renderizadas */
        .c-logo-img { height: 50px; width: auto; max-width: 90%; margin: 15px auto 25px; object-fit: contain; display: block; }

        .card-actions { display: grid; grid-template-columns: 1fr 1fr; border-top: 1px solid rgba(0,0,0,0.1); margin-top: auto; padding-top: 10px; }
        .trio-card-body .card-actions, .theme-gamer .card-actions { border-top-color: rgba(255,255,255,0.2); }
        .btn-action { padding: 10px; font-size: 0.85rem; font-weight: 600; text-decoration: none; color: inherit; }
        .btn-action:hover { opacity: 0.7; }
        
        @media (max-width: 992px) { .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>

    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="topbar">
                <div class="topbar-title">
                    <h1>Catálogo de Planes y Adicionales</h1>
                    <p>Gestiona Internet, Dúos WINTV/Fonowin, Adicionales DGO y Planes Gamer.</p>
                </div>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <i class="fa-solid <?php echo $tipo_mensaje == 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="form-card" id="formulario">
                <h3>
                    <i class="fa-solid <?php echo $modo_edicion ? 'fa-pen-to-square' : 'fa-layer-group'; ?>" style="color:var(--admin-primary);"></i> 
                    <?php echo $modo_edicion ? 'Modificando: ' . htmlspecialchars($plan_editar['titulo']) : 'Registrar Nuevo Plan / Adicional'; ?>
                </h3>
                
                <form action="planes.php" method="POST">
                    <input type="hidden" name="accion" value="<?php echo $modo_edicion ? 'editar' : 'agregar'; ?>">
                    <?php if($modo_edicion): ?>
                        <input type="hidden" name="id" value="<?php echo $plan_editar['id']; ?>">
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Categoría Principal *</label>
                            <select name="categoria" id="select-categoria" required onchange="actualizarFormulario()">
                                <option value="INTERNET" <?php echo ($modo_edicion && $plan_editar['categoria'] == 'INTERNET') ? 'selected' : ''; ?>>Internet (Solo Fibra)</option>
                                <option value="DÚOS" <?php echo ($modo_edicion && $plan_editar['categoria'] == 'DÚOS') ? 'selected' : ''; ?>>Dúos (Fibra + Extra)</option>
                                <option value="TRÍOS" <?php echo ($modo_edicion && $plan_editar['categoria'] == 'TRÍOS') ? 'selected' : ''; ?>>Convierte a Dúo o Trío (Solo Streaming)</option>
                                <option value="GAMER" <?php echo ($modo_edicion && $plan_editar['categoria'] == 'GAMER') ? 'selected' : ''; ?>>Gamer (Oscuro)</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="box-subcategoria" style="display: none;">
                            <label>Subcategoría / Servicio *</label>
                            <select name="subcategoria" id="select-subcategoria" onchange="actualizarFormulario()"></select>
                        </div>

                        <div class="form-group" id="box-logo" style="display: none;">
                            <label>Logo del Servicio Oficial *</label>
                            <select name="logo_url" id="select-logo"></select>
                        </div>

                        <div class="form-group">
                            <label id="label-titulo">Nombre Interno / Etiqueta Alternativa *</label>
                            <input type="text" name="titulo" placeholder="Ej: L1 MAX Premium, PLAN GAMER, etc." value="<?php echo $modo_edicion ? htmlspecialchars($plan_editar['titulo']) : ''; ?>" required>
                        </div>

                        <div class="form-group" id="box-velocidad">
                            <label>Velocidad Promocional (Mbps) *</label>
                            <input type="number" name="velocidad_mbps" id="input-velocidad" placeholder="Ej: 600" value="<?php echo $modo_edicion ? $plan_editar['velocidad_mbps'] : ''; ?>">
                        </div>

                        <div class="form-group" id="box-velocidad-base">
                            <label>Velocidad Base Real (Mbps) - Opcional</label>
                            <input type="number" name="velocidad_base_mbps" id="input-velocidad-base" placeholder="Ej: 300" value="<?php echo $modo_edicion ? $plan_editar['velocidad_base_mbps'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label>Precio Regular / Mensualidad (S/) *</label>
                            <input type="number" step="0.01" name="precio_regular" placeholder="Ej: 159.90" value="<?php echo $modo_edicion ? $plan_editar['precio_regular'] : ''; ?>" required>
                        </div>

                        <div class="form-group" id="box-promo-precio">
                            <label>Precio Promocional (S/) - Opcional</label>
                            <input type="number" step="0.01" name="precio_promocional" placeholder="Ej: 99.90" value="<?php echo $modo_edicion ? $plan_editar['precio_promocional'] : ''; ?>">
                        </div>

                        <div class="form-group" id="box-promo-meses">
                            <label>Meses de Promoción - Opcional</label>
                            <input type="number" name="meses_promocion" placeholder="Ej: 3 o 6" value="<?php echo $modo_edicion ? $plan_editar['meses_promocion'] : ''; ?>">
                        </div>

                        <div class="form-group full-width">
                            <label>Texto Destacado (Beneficio / Adicional) - Opcional</label>
                            <input type="text" name="beneficios_extra" placeholder="Ej: +1 Mesh, Incluye 2 equipos, Adicional a tu plan..." value="<?php echo $modo_edicion ? htmlspecialchars($plan_editar['beneficios_extra']) : ''; ?>">
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn-submit">
                            <i class="fa-solid <?php echo $modo_edicion ? 'fa-save' : 'fa-plus'; ?>"></i> 
                            <?php echo $modo_edicion ? 'Guardar Cambios' : 'Publicar Tarjeta'; ?>
                        </button>
                        <?php if($modo_edicion): ?>
                            <a href="planes.php" class="btn-cancel">Cancelar Edición</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <h3 style="margin-bottom: 20px; color: var(--admin-dark);"><i class="fa-solid fa-table-cells"></i> Vista Previa de Tarjetas Publicadas</h3>
            
            <div class="planes-grid">
                <?php if (count($lista_planes) > 0): ?>
                    <?php foreach ($lista_planes as $plan): 
                        $cat = strtoupper($plan['categoria']);
                        $sub = strtoupper($plan['subcategoria']);
                        
                        $clase_theme = 'theme-duo';
                        if ($cat == 'GAMER') {
                            $clase_theme = 'theme-gamer';
                        } elseif ($cat == 'TRÍOS') {
                            $clase_theme = 'theme-trio ' . (($sub == 'DGO') ? 'theme-adicional-dgo' : 'theme-adicional-wintv');
                        }
                    ?>
                        <div class="card-replica <?php echo $clase_theme; ?>">
                            <div class="admin-tag"><?php echo $cat; ?> <?php echo $sub ? ' | ' . $sub : ''; ?></div>
                            
                            <?php if ($cat == 'INTERNET' || $cat == 'DÚOS' || $cat == 'GAMER'): ?>
                                <div class="c-title">INTERNET 100% FIBRA</div>
                                
                                <?php if(!empty($plan['velocidad_base_mbps'])): ?>
                                    <div class="c-speed-base"><s><?php echo $plan['velocidad_base_mbps']; ?> Mbps</s></div>
                                <?php else: ?>
                                    <div class="c-speed-base">&nbsp;</div>
                                <?php endif; ?>

                                <div class="c-speed"><?php echo $plan['velocidad_mbps']; ?> <span>Mbps</span></div>
                                
                                <?php if($cat != 'GAMER'): ?>
                                    <?php if($plan['meses_promocion']): ?>
                                        <div class="c-promo">DUPLICA TU VELOCIDAD X<?php echo $plan['meses_promocion']; ?> MESES</div>
                                    <?php else: ?>
                                        <div class="c-promo">&nbsp;</div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php 
                                // RENDERIZADO DEL LOGO (IMAGEN VS TEXTO)
                                if (!empty($plan['logo_url'])): ?>
                                    <img src="../<?php echo htmlspecialchars($plan['logo_url']); ?>" alt="<?php echo htmlspecialchars($plan['titulo']); ?>" class="c-logo-img">
                                <?php elseif ($cat == 'GAMER'): ?>
                                    <div class="c-logo">PLANES<span>G4MER</span>WIN</div>
                                <?php elseif ($sub == 'WINTV'): ?>
                                    <div class="c-logo"><span>wintv.</span> <?php echo htmlspecialchars($plan['titulo']); ?></div>
                                <?php elseif ($sub == 'FONOWIN'): ?>
                                    <div class="c-logo" style="font-size: 1.2rem; color: #555;">TELEFONÍA<br><span style="color:var(--admin-dark); font-size: 1.8rem; letter-spacing: -1px;">fonowin</span></div>
                                <?php else: ?>
                                    <div class="c-logo">&nbsp;</div>
                                <?php endif; ?>
                                
                                <?php if(!empty($plan['beneficios_extra']) && strpos(strtolower($plan['beneficios_extra']), 'mesh') !== false): ?>
                                    <div class="mesh-badge" style="background:var(--admin-primary); top: 50%;"><?php echo htmlspecialchars($plan['beneficios_extra']); ?></div>
                                <?php endif; ?>

                                <?php $precio = $plan['precio_promocional'] ?: $plan['precio_regular']; ?>
                                <div class="c-pill">
                                    S/. <?php echo $precio; ?> <span><?php echo $plan['meses_promocion'] ? 'x'.$plan['meses_promocion'].' meses' : 'al mes'; ?></span>
                                </div>
                                
                                <?php if($cat != 'GAMER' && $plan['beneficios_extra'] && strpos(strtolower($plan['beneficios_extra']), 'equipo') !== false): ?>
                                    <div class="c-extra"><?php echo htmlspecialchars($plan['beneficios_extra']); ?></div>
                                <?php endif; ?>
                                
                                <div class="c-reg">Precio regular: S/ <?php echo $plan['precio_regular']; ?></div>

                                <div class="card-actions">
                                    <a href="planes.php?editar=<?php echo $plan['id']; ?>#formulario" class="btn-action" style="color: #3b82f6;"><i class="fa-solid fa-pen"></i> Editar</a>
                                    <a href="planes.php?eliminar=<?php echo $plan['id']; ?>" class="btn-action" style="color: #ef4444; border-left: 1px solid rgba(0,0,0,0.1);" onclick="return confirm('¿Eliminar definitivamente?');"><i class="fa-regular fa-trash-can"></i> Eliminar</a>
                                </div>
                                
                            <?php elseif ($cat == 'TRÍOS'): ?>
                            
                                <?php if ($sub == 'DGO'): ?>
                                    <div class="dgo-custom-header">
                                        <div class="dgo-top">
                                            <?php if (!empty($plan['logo_url'])): ?>
                                                <img src="../<?php echo htmlspecialchars($plan['logo_url']); ?>" alt="<?php echo htmlspecialchars($plan['titulo']); ?>" class="dgo-main-logo">
                                            <?php else: ?>
                                                <div class="c-logo" style="margin: 0; color: #1a1a1a; font-size: 1.5rem; font-weight: 900;"><span style="color: #1da1f2;">DGO</span> <?php echo htmlspecialchars($plan['titulo']); ?></div>
                                            <?php endif; ?>
                                            <div class="dgo-channels">Más de 30 canales</div>
                                        </div>
                                        <div class="dgo-bottom">
                                            <div class="dgo-badge">Incluye</div>
                                            <div class="dgo-bottom-logos">
                                                <img src="../images/l1max-white.png" alt="L1 MAX">
                                                <span class="dgo-plus">+</span>
                                                <img src="../images/prime_video.png" alt="Prime Video">
                                                <span class="dgo-plus">+</span>
                                                <img src="../images/dsports.png" alt="D Sports">
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="trio-card-header">
                                        <?php if (!empty($plan['logo_url'])): ?>
                                            <img src="../<?php echo htmlspecialchars($plan['logo_url']); ?>" alt="<?php echo htmlspecialchars($plan['titulo']); ?>" class="c-logo-img" style="margin:0; filter: none; max-height: 50px;">
                                        <?php else: ?>
                                            <div class="c-logo" style="margin: 0; color: #1a1a1a; font-size: 1.5rem; font-weight: 900;"><span>wintv.</span> <?php echo htmlspecialchars($plan['titulo']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="trio-card-body">
                                    <div class="c-pill">S/. <?php echo $plan['precio_regular']; ?> <span>al mes</span></div>
                                    <div class="c-extra"><?php echo $plan['beneficios_extra'] ?: 'Adicional a tu plan'; ?></div>
                                    <div class="btn-yellow">¡Quiero este plan!</div>
                                    
                                    <div class="card-actions">
                                        <a href="planes.php?editar=<?php echo $plan['id']; ?>#formulario" class="btn-action" style="color: #fff;"><i class="fa-solid fa-pen"></i> Editar</a>
                                        <a href="planes.php?eliminar=<?php echo $plan['id']; ?>" class="btn-action" style="color: #fca5a5; border-left: 1px solid rgba(255,255,255,0.2);" onclick="return confirm('¿Eliminar definitivamente?');"><i class="fa-regular fa-trash-can"></i> Eliminar</a>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 50px; background: white; border-radius: 12px; color: var(--text-muted);">
                        <p>No hay planes registrados. Crea el primero desde el formulario.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        const modoEdicionSub = "<?php echo $modo_edicion ? $plan_editar['subcategoria'] : ''; ?>";
        const modoEdicionLogo = "<?php echo $modo_edicion ? $plan_editar['logo_url'] : ''; ?>";
        
        function actualizarFormulario() {
            const cat = document.getElementById('select-categoria').value;
            const boxSub = document.getElementById('box-subcategoria');
            const selectSub = document.getElementById('select-subcategoria');
            const boxVel = document.getElementById('box-velocidad');
            const boxVelBase = document.getElementById('box-velocidad-base');
            const inputVel = document.getElementById('input-velocidad');
            const boxLogo = document.getElementById('box-logo');
            const selectLogo = document.getElementById('select-logo');
            
            // Subcategorías
            if (cat === 'DÚOS') {
                boxSub.style.display = 'block';
                if(!selectSub.innerHTML.includes('FONOWIN')) {
                    selectSub.innerHTML = `
                        <option value="WINTV">WINTV (Televisión)</option>
                        <option value="FONOWIN">FONOWIN (Telefonía)</option>
                    `;
                }
            } else if (cat === 'TRÍOS') {
                boxSub.style.display = 'block';
                if(!selectSub.innerHTML.includes('DGO')) {
                    selectSub.innerHTML = `
                        <option value="WINTV">WINTV (Fondo Naranja)</option>
                        <option value="DGO">DGO (Fondo Azul)</option>
                    `;
                }
            } else {
                boxSub.style.display = 'none';
                selectSub.innerHTML = `<option value="">Ninguna</option>`;
            }

            if (modoEdicionSub && selectSub.querySelector(`option[value="${modoEdicionSub}"]`) && !selectSub.dataset.loaded) {
                selectSub.value = modoEdicionSub;
                selectSub.dataset.loaded = "true";
            }

            // Selector dinámico de Logos de Imagen
            const sub = selectSub.value;
            if (cat === 'GAMER') {
                boxLogo.style.display = 'block';
                selectLogo.innerHTML = `
                    <option value="">Usar texto (Sin imagen)</option>
                    <option value="images/wingamer-96x72.png">Logo Gamer Oficial</option>
                `;
            } else if (sub === 'WINTV') {
                boxLogo.style.display = 'block';
                selectLogo.innerHTML = `
                    <option value="">Usar texto (Sin imagen)</option>
                    <option value="images/winTV.png">WinTV Básico</option>
                    <option value="images/wintvpremium.png">WinTV Premium</option>
                    <option value="images/wintv-l1nopremium--200x40.png">WinTV L1 MAX</option>
                    <option value="images/wintv-l1premium-200x40.png">WinTV L1 MAX Premium</option>
                `;
            } else if (sub === 'DGO') {
                boxLogo.style.display = 'block';
                selectLogo.innerHTML = `
                    <option value="">Usar texto (Sin imagen)</option>
                    <option value="images/DGO-logo.png">DGO Blanco Oficial</option>
                    <option value="images/dgo-full.png">DGO Full (Original)</option>
                    <option value="images/dgo_hogar.png">DGO Hogar (Original)</option>
                    <option value="images/DGO-logo (1).png">DGO Logo Alternativo</option>
                `;
            } else if (sub === 'FONOWIN') {
                boxLogo.style.display = 'block';
                selectLogo.innerHTML = `
                    <option value="">Usar texto (Sin imagen)</option>
                    <option value="images/logoFONO.webp">Logo Fonowin Oficial</option>
                `;
            } else {
                boxLogo.style.display = 'none';
                selectLogo.innerHTML = `<option value="">No aplica</option>`;
            }

            if (modoEdicionLogo && selectLogo.querySelector(`option[value="${modoEdicionLogo}"]`) && !selectLogo.dataset.loaded) {
                selectLogo.value = modoEdicionLogo;
                selectLogo.dataset.loaded = "true";
            }

            // Ocultar Velocidades en Tríos (Adicionales)
            if (cat === 'TRÍOS') {
                boxVel.style.display = 'none';
                boxVelBase.style.display = 'none';
                inputVel.required = false;
                inputVel.value = 0; 
            } else {
                boxVel.style.display = 'block';
                boxVelBase.style.display = 'block';
                inputVel.required = true;
            }
        }
        window.onload = actualizarFormulario;
    </script>
</body>
</html>