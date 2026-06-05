<?php
/**
 * Archivo: admin/includes/sidebar.php
 * Descripción: Barra lateral de navegación del panel administrador (Totalmente Responsiva).
 */

// Obtenemos el nombre del archivo actual para marcar el menú activo automáticamente
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
    :root {
        --admin-bg-dark: #1a1a2e;
        --admin-bg-darker: #11111f;
        --admin-primary: #ff5a00;
        --admin-text: #a0aec0;
        --admin-text-light: #ffffff;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* =========================================
       1. CABECERA MÓVIL (Nativa) - Oculta en PC
       ========================================= */
    .mobile-admin-header {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 70px;
        background-color: var(--admin-bg-dark);
        z-index: 998;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        padding: 0 20px;
        align-items: center;
        justify-content: space-between;
    }

    .mobile-logo {
        color: var(--admin-primary);
        font-size: 1.3rem;
        font-weight: 800;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        line-height: 1;
    }
    .mobile-logo span {
        font-size: 0.6rem;
        color: var(--admin-text);
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-top: 3px;
    }

    .mobile-toggle-btn {
        background: transparent;
        border: none;
        color: var(--admin-text-light);
        font-size: 1.8rem;
        cursor: pointer;
        padding: 5px;
        transition: var(--transition);
    }

    .mobile-toggle-btn:focus { outline: none; }
    .mobile-toggle-btn:active { transform: scale(0.9); color: var(--admin-primary); }

    /* =========================================
       2. CAPA DE DESENFOQUE (Fondo oscuro móvil)
       ========================================= */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(17, 17, 31, 0.7);
        backdrop-filter: blur(4px); /* Efecto Glassmorphism */
        z-index: 999;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    /* =========================================
       3. BARRA LATERAL (Sidebar)
       ========================================= */
    .admin-sidebar {
        width: 260px;
        background-color: var(--admin-bg-dark);
        color: var(--admin-text);
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        display: flex;
        flex-direction: column;
        box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        z-index: 1000;
        transition: var(--transition);
    }

    .sidebar-header {
        padding: 25px 20px;
        background-color: var(--admin-bg-darker);
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .sidebar-logo {
        color: var(--admin-primary);
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1;
        text-decoration: none;
        display: block;
    }

    .sidebar-logo span {
        display: block;
        font-size: 0.7rem;
        color: var(--admin-text);
        font-weight: 500;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-top: 5px;
    }

    .sidebar-user {
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .user-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: rgba(255, 90, 0, 0.2);
        color: var(--admin-primary);
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 1.2rem;
        border: 2px solid var(--admin-primary);
    }

    .user-info h4 {
        color: var(--admin-text-light);
        font-size: 0.95rem;
        margin-bottom: 2px;
        font-weight: 600;
    }

    .user-info p {
        font-size: 0.75rem;
        color: #25d366; 
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .user-info p::before {
        content: '';
        width: 8px;
        height: 8px;
        background-color: #25d366;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 8px rgba(37, 211, 102, 0.8); /* Glow de en línea */
    }

    .sidebar-nav {
        padding: 20px 0;
        flex: 1;
        overflow-y: auto;
    }
    
    /* Scrollbar estilizado para la barra lateral */
    .sidebar-nav::-webkit-scrollbar { width: 5px; }
    .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
    .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    .sidebar-nav::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

    .nav-label {
        padding: 0 20px;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #666;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .sidebar-nav ul { list-style: none; padding: 0; margin: 0; }
    .sidebar-nav li { margin-bottom: 5px; }

    .sidebar-nav a {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px 20px;
        color: var(--admin-text);
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 500;
        border-left: 4px solid transparent;
        transition: var(--transition);
    }

    .sidebar-nav a i {
        font-size: 1.1rem;
        width: 20px;
        text-align: center;
        transition: var(--transition);
    }

    .sidebar-nav a:hover, 
    .sidebar-nav a.active {
        background-color: rgba(255, 255, 255, 0.05);
        color: var(--admin-text-light);
        border-left-color: var(--admin-primary);
    }

    .sidebar-nav a:hover i, 
    .sidebar-nav a.active i {
        color: var(--admin-primary);
    }

    .sidebar-footer {
        padding: 20px;
        border-top: 1px solid rgba(255,255,255,0.05);
    }

    .btn-logout {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 12px;
        background-color: rgba(220, 38, 38, 0.1);
        color: #ef4444;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: var(--transition);
    }

    .btn-logout:hover {
        background-color: #dc2626;
        color: white;
    }

    /* Botón cerrar lateral en móvil */
    .btn-close-sidebar {
        display: none;
        position: absolute;
        top: 20px;
        right: 20px;
        background: transparent;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
    }

    /* =========================================
       4. MEDIA QUERIES (Magia Responsiva)
       ========================================= */
    @media (max-width: 992px) {
        /* Ocultar barra lateral fuera de pantalla */
        .admin-sidebar {
            transform: translateX(-100%);
        }

        /* Cuando está activa, vuelve a su posición original */
        .admin-sidebar.show {
            transform: translateX(0);
        }

        /* Mostrar cabecera móvil */
        .mobile-admin-header {
            display: flex;
        }

        /* Mostrar overlay cuando el menú está abierto */
        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        /* Mostrar botón de cerrar dentro del sidebar */
        .btn-close-sidebar {
            display: block;
        }

        /* Ajuste de seguridad para el main-content:
           Evitamos que el topbar del admin se esconda detrás de la cabecera móvil */
        body .main-content {
            margin-left: 0 !important;
            padding-top: 90px !important; /* Espacio para el mobile-header */
        }
    }
</style>

<!-- =========================================
     COMPONENTE MÓVIL (Cabecera Superior)
     ========================================= -->
<div class="mobile-admin-header">
    <a href="dashboard.php" class="mobile-logo">
        WIN-FIBRA<span>Panel Admin</span>
    </a>
    <button class="mobile-toggle-btn" id="btnOpenSidebar">
        <i class="fa-solid fa-bars-staggered"></i>
    </button>
</div>

<!-- =========================================
     COMPONENTE MÓVIL (Overlay Desenfocado)
     ========================================= -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- =========================================
     BARRA LATERAL PRINCIPAL
     ========================================= -->
<aside class="admin-sidebar" id="adminSidebar">
    <!-- Botón visible solo en móvil para cerrar -->
    <button class="btn-close-sidebar" id="btnCloseSidebar">
        <i class="fa-solid fa-xmark"></i>
    </button>

    <div class="sidebar-header">
        <a href="dashboard.php" class="sidebar-logo">
            WIN-FIBRA<span>Admin Panel</span>
        </a>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar">
            <i class="fa-solid fa-user-tie"></i>
        </div>
        <div class="user-info">
            <h4><?php echo isset($_SESSION['admin_username']) ? ucfirst($_SESSION['admin_username']) : 'Administrador'; ?></h4>
            <p>En línea</p>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Principal</div>
        <ul>
            <li>
                <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-chart-pie"></i> Resumen
                </a>
            </li>
        </ul>

        <div class="nav-label" style="margin-top: 20px;">Gestor de Contenido</div>
        <ul>
            <li>
                <a href="portadas.php" class="<?php echo ($current_page == 'portadas.php') ? 'active' : ''; ?>">
                    <i class="fa-regular fa-images"></i> Banners / Portadas
                </a>
            </li>
            <li>
                <a href="planes.php" class="<?php echo ($current_page == 'planes.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-wifi"></i> Gestor de Planes
                </a>
            </li>
        </ul>

        <div class="nav-label" style="margin-top: 20px;">Configuración</div>
        <ul>
            <li>
                <!-- CORRECCIÓN APLICADA: Ahora marca "active" correctamente cuando entras a catálogos -->
                <a href="catalogos.php" class="<?php echo ($current_page == 'catalogos.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-file-pdf"></i> Catálogos
                </a>
            </li>
            
            <li>
                <a href="../index.php" target="_blank">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Ver Sitio Web
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout">
            <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
        </a>
    </div>
</aside>

<!-- =========================================
     JAVASCRIPT (Lógica de Interacción Móvil)
     ========================================= -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnOpen = document.getElementById('btnOpenSidebar');
        const btnClose = document.getElementById('btnCloseSidebar');
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        // Función para abrir la barra lateral
        function openSidebar() {
            sidebar.classList.add('show');
            overlay.classList.add('show');
            // Bloquea el scroll del fondo cuando el menú está abierto en móvil
            document.body.style.overflow = 'hidden'; 
        }

        // Función para cerrar la barra lateral
        function closeSidebar() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            // Devuelve el scroll
            document.body.style.overflow = 'auto'; 
        }

        // Eventos de clic
        if(btnOpen) btnOpen.addEventListener('click', openSidebar);
        if(btnClose) btnClose.addEventListener('click', closeSidebar);
        
        // Cerrar al hacer clic en la zona negra (overlay)
        if(overlay) overlay.addEventListener('click', closeSidebar);
    });
</script>