<?php
/**
 * Archivo: admin/dashboard.php
 * Descripción: Panel principal (Resumen) del Administrador.
 */

// 1. Iniciar sesión y validar seguridad
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 2. Incluir conexión a la base de datos
require_once '../config/conexion.php';

// 3. Consultas a la base de datos para obtener métricas reales
try {
    // Contar planes activos
    $stmt_planes = $conexion->query("SELECT COUNT(*) FROM planes WHERE estado = 1");
    $total_planes = $stmt_planes->fetchColumn();

    // Contar portadas (banners) activas
    $stmt_portadas = $conexion->query("SELECT COUNT(*) FROM portadas WHERE estado = 1");
    $total_portadas = $stmt_portadas->fetchColumn();

    // En un sistema real, aquí podrías contar leads, mensajes de contacto, etc.
    $total_visitas = 1254; // Dato simulado para la vista
    $leads_nuevos = 38;    // Dato simulado para la vista

} catch (PDOException $e) {
    // Si la tabla aún no existe o hay error, evitamos que la página se rompa
    $total_planes = 0;
    $total_portadas = 0;
}

// Saludo dinámico según la hora del día
$hora = date('H');
if ($hora < 12) { $saludo = "Buenos días"; } 
elseif ($hora < 19) { $saludo = "Buenas tardes"; } 
else { $saludo = "Buenas noches"; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Panel Administrador AIK</title>
    
    <!-- Google Fonts & FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* =========================================
           VARIABLES Y RESET GLOBALES (Panel)
           ========================================= */
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
        body { background-color: var(--admin-bg-page); color: var(--text-main); overflow-x: hidden; }

        /* =========================================
           LAYOUT PRINCIPAL (Grid con el Sidebar)
           ========================================= */
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 260px; /* Compensa el ancho del sidebar.php */
            padding: 30px 40px;
            transition: var(--transition);
        }

        /* =========================================
           TOPBAR (Cabecera del contenido)
           ========================================= */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .topbar-title h1 { font-size: 1.8rem; font-weight: 700; color: var(--admin-dark); }
        .topbar-title p { color: var(--text-muted); font-size: 0.9rem; }

        .topbar-actions { display: flex; gap: 15px; align-items: center; }
        .date-badge { background: white; padding: 8px 15px; border-radius: 8px; font-size: 0.85rem; font-weight: 500; color: var(--text-muted); box-shadow: 0 2px 10px rgba(0,0,0,0.02); }

        /* =========================================
           BANNER DE BIENVENIDA
           ========================================= */
        .welcome-banner {
            background: linear-gradient(135deg, var(--admin-dark) 0%, #2a2a4a 100%);
            border-radius: 15px;
            padding: 35px 40px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(26, 26, 46, 0.15);
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::after {
            content: '\f0e7'; /* Icono de rayo FontAwesome */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            right: 5%;
            top: -20%;
            font-size: 15rem;
            color: rgba(255, 255, 255, 0.03);
            transform: rotate(15deg);
        }

        .welcome-text h2 { font-size: 2rem; font-weight: 800; margin-bottom: 10px; }
        .welcome-text h2 span { color: var(--admin-primary); }
        .welcome-text p { font-size: 1rem; color: #cbd5e1; max-width: 600px; }

        .btn-banner {
            background-color: var(--admin-primary);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            z-index: 2;
        }
        .btn-banner:hover { background-color: var(--admin-primary-dark); transform: translateY(-2px); }

        /* =========================================
           TARJETAS DE MÉTRICAS (STAT CARDS)
           ========================================= */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background-color: var(--card-bg);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.02);
        }

        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.06); }

        .stat-icon {
            width: 65px;
            height: 65px;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.8rem;
        }

        .icon-orange { background: rgba(255, 90, 0, 0.1); color: var(--admin-primary); }
        .icon-blue { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .icon-green { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .icon-purple { background: rgba(168, 85, 247, 0.1); color: #a855f7; }

        .stat-info h3 { font-size: 1.8rem; font-weight: 800; color: var(--admin-dark); line-height: 1; margin-bottom: 5px; }
        .stat-info p { font-size: 0.85rem; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }

        /* =========================================
           ACCESOS DIRECTOS (QUICK ACTIONS)
           ========================================= */
        .quick-actions-section h3 {
            font-size: 1.2rem;
            color: var(--admin-dark);
            margin-bottom: 20px;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .action-card {
            background-color: var(--card-bg);
            padding: 25px;
            border-radius: 12px;
            text-decoration: none;
            color: var(--text-main);
            display: flex;
            align-items: flex-start;
            gap: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border: 1px solid transparent;
            transition: var(--transition);
        }

        .action-card:hover {
            border-color: var(--admin-primary);
            box-shadow: 0 10px 20px rgba(255, 90, 0, 0.05);
        }

        .action-card i {
            font-size: 1.5rem;
            color: var(--admin-primary);
            margin-top: 5px;
        }

        .action-text h4 { font-size: 1.05rem; font-weight: 700; margin-bottom: 5px; }
        .action-text p { font-size: 0.85rem; color: var(--text-muted); }

        /* =========================================
           RESPONSIVE DESIGN
           ========================================= */
        @media (max-width: 992px) {
            .main-content { margin-left: 0; width: 100%; padding: 20px; }
            .admin-sidebar { display: none; /* En producción requiere botón toggle via JS */ }
            .welcome-banner { flex-direction: column; text-align: center; gap: 20px; }
        }
    </style>
</head>
<body>

    <div class="admin-layout">
        <!-- 4. INCLUIR SIDEBAR -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="main-content">
            
            <!-- Topbar -->
            <div class="topbar">
                <div class="topbar-title">
                    <h1>Dashboard</h1>
                    <p>Panel de Control WIN-FIBRA Soluciones</p>
                </div>
                <div class="topbar-actions">
                    <div class="date-badge">
                        <i class="fa-regular fa-calendar"></i> 
                        <?php 
                        setlocale(LC_TIME, 'es_ES.UTF-8', 'esp');
                        echo strftime("%d de %B de %Y"); 
                        ?>
                    </div>
                </div>
            </div>

            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div class="welcome-text">
                    <h2><?php echo $saludo; ?>, <span><?php echo ucfirst($_SESSION['admin_username']); ?></span></h2>
                    <p>Bienvenido al administrador de contenidos. Aquí tienes un resumen del estado actual de tu plataforma web corporativa.</p>
                </div>
                <a href="../index.php" target="_blank" class="btn-banner">
                    <i class="fa-solid fa-eye"></i> Ver Sitio Web
                </a>
            </div>

            <!-- Tarjetas de Métricas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon icon-orange"><i class="fa-solid fa-wifi"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_planes; ?></h3>
                        <p>Planes Publicados</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon icon-blue"><i class="fa-regular fa-images"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_portadas; ?></h3>
                        <p>Banners Activos</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon icon-green"><i class="fa-solid fa-users-viewfinder"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_visitas; ?></h3>
                        <p>Visitas Totales</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon icon-purple"><i class="fa-solid fa-user-plus"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $leads_nuevos; ?></h3>
                        <p>Nuevos Leads</p>
                    </div>
                </div>
            </div>

            <!-- Accesos Directos (Quick Actions) -->
            <div class="quick-actions-section">
                <h3>Accesos Rápidos</h3>
                <div class="actions-grid">
                    <a href="planes.php" class="action-card">
                        <i class="fa-solid fa-square-plus"></i>
                        <div class="action-text">
                            <h4>Publicar Nuevo Plan</h4>
                            <p>Crea o edita planes de Internet, Dúos, Tríos o Gamer estableciendo precios y velocidades.</p>
                        </div>
                    </a>

                    <a href="portadas.php" class="action-card">
                        <i class="fa-solid fa-panorama"></i>
                        <div class="action-text">
                            <h4>Cambiar Portadas</h4>
                            <p>Sube nuevas imágenes para el carrusel principal y lanza tus campañas de marketing.</p>
                        </div>
                    </a>
                </div>
            </div>

        </main>
    </div>

</body>
</html>