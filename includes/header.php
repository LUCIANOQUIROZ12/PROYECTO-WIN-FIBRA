<?php
/**
 * Archivo: includes/header.php
 * Descripción: Cabecera global de la página. Contiene el <head>, CSS global, 
 * variables de configuración de la empresa y el menú de navegación (Header).
 */

// 1. SIMULACIÓN DE CONSULTA A BASE DE DATOS PARA LA CONFIGURACIÓN GLOBAL
// En el futuro, esto vendrá de tu base de datos mediante PDO.
$configGlobal = [
    "nombreMarca" => "WIN",
    "subMarca"    => "FIBRA",
    "telefono"    => "(01) 7012367",
    "whatsapp"    => "51927671862",
    "menu"        => [
        [ "texto" => "Planes",     "link" => "#dynamic-planes" ],
        [ "texto" => "Beneficios", "link" => "#dynamic-beneficios" ],
        [ "texto" => "Cobertura",  "link" => "#dynamic-cobertura" ],
        [ "texto" => "Preguntas",  "link" => "#dynamic-faqs" ]
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <meta name="description" content="Soluciones AIK SAC - Proveedor integral de telecomunicaciones y conectividad para empresas.">
    <meta name="theme-color" content="#ff5a00">
    
    <title><?php echo $configGlobal['nombreMarca'] . " " . $configGlobal['subMarca']; ?> | Conectividad Corporativa</title>
    <link rel="icon" href="images/favicon.ico" type="image/png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* VARIABLES GLOBALES (Estilo WIN) */
        :root {
            --win-orange: #ff5a00;
            --win-orange-dark: #e04f00;
            --win-dark: #221815; 
            --win-dark-light: #33241f;
            --win-bg-cream: #fff9f5;
            --win-green: #25d366;
            --text-dark: #1a1a1a;
            --text-gray: #666666;
            --font-main: 'Poppins', sans-serif;
            --transition: all 0.3s ease;
        }

        /* RESET GLOBAL */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: var(--font-main);
        }

        body {
            background-color: #ffffff;
            color: var(--text-dark);
            overflow-x: hidden;
            scroll-behavior: smooth; /* Scroll suave nativo */
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* =========================================
           DISEÑO DEL HEADER (NAVEGACIÓN)
           ========================================= */
        header {
            background-color: #ffffff;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: var(--transition);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Logo Tipográfico */
        .logo {
            display: flex;
            flex-direction: column;
            color: var(--win-orange);
            font-weight: 900;
            font-size: 2.2rem;
            line-height: 1;
            letter-spacing: -1px;
            cursor: pointer;
        }

        .logo span {
            font-size: 0.6rem;
            color: var(--text-gray);
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Enlaces de Navegación */
        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-links a {
            font-weight: 600;
            color: var(--text-gray);
            font-size: 0.95rem;
            padding-bottom: 5px;
            position: relative;
            transition: var(--transition);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--win-orange);
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--win-orange);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        /* Botones de Acción (Llamada, WhatsApp y ADMIN) */
        .header-buttons {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Botón Administrador Discreto */
        .btn-admin-header {
            background-color: #f1f5f9;
            color: #64748b;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.1rem;
            transition: var(--transition);
            border: 1px solid #e2e8f0;
        }

        .btn-admin-header:hover {
            background-color: var(--win-dark);
            color: white;
            border-color: var(--win-dark);
            transform: rotate(90deg); /* Pequeña animación de engranaje al pasar el mouse */
        }

        .btn-call-header {
            background-color: var(--win-orange);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(255, 90, 0, 0.3);
            transition: var(--transition);
        }

        .btn-call-header:hover {
            background-color: var(--win-orange-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 90, 0, 0.4);
        }

        .btn-wpp-header {
            background-color: var(--win-green);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3);
            transition: var(--transition);
        }

        .btn-wpp-header:hover {
            background-color: #128c7e;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(37, 211, 102, 0.4);
        }

        /* Menú Hamburguesa (Oculto en Escritorio) */
        .mobile-menu-toggle {
            display: none;
            font-size: 1.8rem;
            color: var(--win-orange);
            cursor: pointer;
        }

        /* =========================================
           RESPONSIVE DESIGN (Móviles y Tablets)
           ========================================= */
        @media (max-width: 992px) {
            .nav-links {
                display: none; /* Ocultamos los links de texto en tablets/móviles para ganar espacio */
            }
        }

        @media (max-width: 768px) {
            .header-container {
                padding: 0 5px;
            }
            .logo {
                font-size: 1.5rem; /* Levemente reducido para dar espacio a los iconos */
            }
            .header-buttons {
                gap: 10px; /* Reducimos la brecha para móviles */
            }
            
            /* En lugar de ocultar el botón, lo transformamos en un botón circular sin texto */
            .btn-call-header,
            .btn-wpp-header {
                display: flex;
                width: 40px;
                height: 40px;
                padding: 0;
                justify-content: center;
                border-radius: 50%; /* Círculo perfecto */
            }
            
            /* Ocultamos el texto de los botones en la versión móvil */
            .btn-text {
                display: none;
            }
            
            /* Ajustamos los iconos para que queden centrados */
            .btn-call-header i,
            .btn-wpp-header i {
                font-size: 1.2rem;
                margin: 0;
            }
            
            .btn-admin-header {
                width: 35px;
                height: 35px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

    <header id="main-header">
        <div class="container header-container">
            
            <a href="index.php" class="logo">
                <?php echo $configGlobal['nombreMarca']; ?>
                <span><?php echo $configGlobal['subMarca']; ?></span>
            </a>
            
            <nav class="nav-links">
                <?php foreach($configGlobal['menu'] as $item): ?>
                    <a href="<?php echo $item['link']; ?>"><?php echo $item['texto']; ?></a>
                <?php endforeach; ?>
            </nav>

            <div class="header-buttons">

                <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $configGlobal['telefono']); ?>" class="btn-call-header">
                    <i class="fa-solid fa-phone"></i> <span class="btn-text">Llámanos: <?php echo $configGlobal['telefono']; ?></span>
                </a>
                
                <a href="https://wa.me/<?php echo $configGlobal['whatsapp']; ?>" class="btn-wpp-header" target="_blank" rel="noopener noreferrer">
                    <i class="fa-brands fa-whatsapp"></i> <span class="btn-text">WhatsApp</span>
                </a>
            </div>

        </div>
    </header>