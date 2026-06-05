<?php
/**
 * Archivo: index.php
 * Descripción: Página principal (Landing) de Soluciones AIK SAC.
 * Arquitectura: Modular y Renderizado Dinámico vía JS con BD.
 */

require_once 'includes/header.php';
require_once 'config/conexion.php';

$portadasDinamicas = [];
$planesDinamicos = [];
$catalogosDinamicos = [];

try {
    // 1. CARGAR PORTADAS
    $stmtPortadas = $conexion->query("SELECT imagen_url, alt_text FROM portadas WHERE estado = 1 ORDER BY id DESC");
    $resultadosPortadas = $stmtPortadas->fetchAll();
    foreach ($resultadosPortadas as $row) {
        $portadasDinamicas[] = [ "imagen" => $row['imagen_url'], "alt" => !empty($row['alt_text']) ? $row['alt_text'] : "Campaña Corporativa AIK" ];
    }

    // 2. CARGAR PLANES
    $stmtPlanes = $conexion->query("SELECT * FROM planes WHERE estado = 1 ORDER BY categoria, subcategoria, precio_regular ASC");
    $resultadosPlanes = $stmtPlanes->fetchAll();

    foreach ($resultadosPlanes as $plan) {
        $icono = "fa-wifi";
        switch (strtoupper($plan['categoria'])) {
            case 'DÚOS': $icono = "fa-network-wired"; break;
            case 'TRÍOS': $icono = "fa-server"; break;
            case 'GAMER': $icono = "fa-gamepad"; break;
        }

        $tienePromocion = !empty($plan['precio_promocional']) && $plan['precio_promocional'] > 0;
        $precioFinal = $tienePromocion ? (float)$plan['precio_promocional'] : (float)$plan['precio_regular'];
        $precioTachado = $tienePromocion ? (float)$plan['precio_regular'] : null;

        $planesDinamicos[] = [
            "id" => (int)$plan['id'],
            "nombre" => strtoupper($plan['titulo']),
            "categoria" => strtoupper($plan['categoria']),
            "subcategoria" => strtoupper($plan['subcategoria']),
            "icono" => $icono,
            "precio" => $precioFinal,
            "precioRegular" => $precioTachado,
            "mesesPromocion" => !empty($plan['meses_promocion']) ? (int)$plan['meses_promocion'] : null,
            "beneficioExtra" => !empty($plan['beneficios_extra']) ? strtoupper($plan['beneficios_extra']) : null,
            "logoUrl" => !empty($plan['logo_url']) ? $plan['logo_url'] : null,
            "velocidad" => (int)$plan['velocidad_mbps'],
            "velocidadBase" => !empty($plan['velocidad_base_mbps']) ? (int)$plan['velocidad_base_mbps'] : null,
            "unidad" => "Mbps"
        ];
    }

    // 3. CARGAR CATÁLOGOS / TARIFARIOS PDF
    $stmtCatalogos = $conexion->query("SELECT titulo, archivo_url FROM catalogos ORDER BY fecha_subida DESC");
    $resultadosCatalogos = $stmtCatalogos->fetchAll();
    foreach ($resultadosCatalogos as $cat) {
        $catalogosDinamicos[] = [
            "titulo" => $cat['titulo'],
            "archivo_url" => $cat['archivo_url']
        ];
    }
} catch (PDOException $e) {}

if (empty($planesDinamicos)) {
    $planesDinamicos = [
        [ "id" => 1, "nombre" => "INTERNET FULL", "categoria" => "INTERNET", "subcategoria" => "", "icono" => "fa-wifi", "precio" => 99, "precioRegular" => 119, "mesesPromocion" => 6, "beneficioExtra" => null, "logoUrl" => null, "velocidad" => 400, "velocidadBase" => 200, "unidad" => "Mbps" ],
        [ "id" => 2, "nombre" => "L1 MAX Premium", "categoria" => "DÚOS", "subcategoria" => "WINTV", "icono" => "fa-network-wired", "precio" => 139.90, "precioRegular" => 169.90, "mesesPromocion" => 3, "beneficioExtra" => "Incluye 2 equipos", "logoUrl" => "images/wintvpremium.png", "velocidad" => 1000, "velocidadBase" => 550, "unidad" => "Mbps" ],
        [ "id" => 3, "nombre" => "PLAN GAMER EXTREME", "categoria" => "GAMER", "subcategoria" => "", "icono" => "fa-gamepad", "precio" => 129, "precioRegular" => 159, "mesesPromocion" => 3, "beneficioExtra" => "+2 Mesh", "logoUrl" => "images/wingamer-96x72.png", "velocidad" => 600, "velocidadBase" => null, "unidad" => "Mbps" ],
        [ "id" => 4, "nombre" => "GO FULL", "categoria" => "TRÍOS", "subcategoria" => "DGO", "icono" => "fa-server", "precio" => 76, "precioRegular" => null, "mesesPromocion" => null, "beneficioExtra" => "Adicional a tu plan", "logoUrl" => "images/DGO-logo.png", "velocidad" => 0, "velocidadBase" => null, "unidad" => "Mbps" ]
    ];
}

$datosIndexPHP = [
    "portadas" => $portadasDinamicas,
    "planes"   => $planesDinamicos,
    "catalogos" => $catalogosDinamicos,
    "hero" => [ "etiqueta" => "ESTO SÍ ES", "etiquetaResaltada" => "SOLUCIÓN INTEGRAL", "titulo1" => "Cámbiate a AIK", "titulo2" => "Que sí es 100% Conectividad", "formTitulo" => "¡Déjanos tus datos y te llamamos!", "disclaimer" => "Este sitio comercializa servicios corporativos." ],
    "planesInfo" => [ "descripcion" => "¡CÁMBIATE A UNO DE NUESTROS PLANES DE INTERNET 100% FIBRA ÓPTICA!</strong>" ],
    "legalesBanner" => [ "texto1" => "Incluye equipos corporativos ONT y los precios incluyen IGV. | La instalación del servicio tiene un costo de s/120 y lo puedes dividir hasta en 6 cuotas. Planes dedicados no tienen costo de instalación. Válido solo para Lima.", "linkTerminos" => "#", "textoTerminos" => "Ver términos y condiciones", "texto2" => "Equipo no terminal (Mesh) y Servicios de Valor Añadido sujetos a", "linkCondiciones" => "#", "textoCondiciones" => "términos y condiciones", "imagen" => "images/laptop-woman.png", "bannerLinea1" => "Escríbenos al", "bannerLinea2" => "927 671 862", "bannerLinea3" => "ARMA TU DUO O TRÍO CON WIN" ],
    "beneficiosDestacados" => [ "titulo" => "BRINDAMOS LOS MEJORES BENEFICIOS PARA TU EMPRESA", "items" => [ [ "imagen" => "images/tv.png", "titulo" => "Dúos y Tríos", "descripcion" => "Televisión digital y telefonía fija para completar tu plan." ], [ "imagen" => "images/velocidad1.png", "titulo" => "Velocidad Simétrica", "descripcion" => "Descarga y sube archivos a la misma velocidad sin interrupciones." ], [ "imagen" => "images/hola1.png", "titulo" => "Mayor Estabilidad", "descripcion" => "Conexión 100% fibra óptica dedicada hasta tu empresa (FTTB)." ] ], "textoBoton" => "Quiero estos beneficios" ],
    "modalLead" => [ "titulo" => "¡Déjanos tus datos y te llamamos!", "labelCelular" => "Ingrese su número de celular", "labelDNI" => "Ingrese su DNI/CEX/RUC", "labelTerminos" => "He leído y acepto los <a href='#' class='open-terms-link'>Términos y Condiciones</a>", "btnSubmit" => "Llámame", "btnCancel" => "Cancel" ],
    "politicaPrivacidad" => [ "titulo" => "Política de Privacidad", "parrafos" => [ "La presente política se encuentra sujeta a lo dispuesto por la Ley N° 29733, Ley de Protección de Datos Personales, y su Reglamento...", "Al dejarnos sus datos autoriza que el horario de contacto será en el rango horario de lunes a domingo entre las 8am y 10pm." ], "btnCerrar" => "Listo" ],
    "cobertura" => [ "titulo" => "Cobertura Nacional", "descripcion" => "Disponibilidad en todo el territorio nacional para proyectos corporativos. Valida la factibilidad de tu zona.", "textoBoton" => "¿No estás seguro? Escríbenos por WhatsApp y lo confirmamos por ti.", "linkWpp" => "https://wa.me/51927671862" ],
    "terminosFrecuentes" => [ "titulo" => "Términos y Condiciones", "subtitulo" => "Vigencia del 02/05/2024 hasta el 31/05/2024", "items" => [ [ "pregunta" => "MONO / DÚO / TRÍO WIN", "respuesta" => "<p>Promoción para clientes que contraten una suscripción para aplicativo de streaming...</p><ul><li>Aplica para altas nuevas.</li></ul>" ] ] ]
];
?>

<main id="app-index">
    <section id="dynamic-hero" class="hero"></section>
    <section id="dynamic-planes" class="planes-section"></section>
    <section id="dynamic-legales-banner" class="legales-banner-section"></section>
    <section id="dynamic-beneficios-destacados" class="beneficios-destacados-section"></section>
    <div id="dynamic-modal-container"></div>
    <div id="dynamic-terms-modal-container"></div>
    <section id="dynamic-cobertura" class="cobertura-section"></section>
    <section id="dynamic-terminos" class="faq-section"></section>
    <a href="https://wa.me/51927671862" class="whatsapp-flotante" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp"></i></a>
</main>

<style>
    .section-title { font-size: 2.2rem; font-weight: 800; margin-bottom: 40px; position: relative; display: inline-block; }
    .section-title::after { content: ''; position: absolute; left: 0; bottom: -5px; width: 40px; height: 4px; background-color: var(--win-orange); }
    .section-title-center { text-align: center; display: block; margin-bottom: 15px; }
    .section-title-center::after { left: 50%; transform: translateX(-50%); }

    /* SLIDER Y HERO */
    .hero { position: relative; overflow: hidden; padding: 0; min-height: 550px; background-color: var(--win-orange); display: flex; align-items: center; }
    .carousel-container { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; }
    .carousel-track { display: flex; height: 100%; transition: transform 0.5s ease-in-out; }
    .carousel-slide { min-width: 100%; height: 100%; background-size: cover; background-position: center; background-repeat: no-repeat; }
    .carousel-arrow { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.4); border: none; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; z-index: 10; color: white; font-size: 1.2rem; transition: 0.3s; display: flex; justify-content: center; align-items: center; }
    .carousel-arrow:hover { background: var(--win-orange); }
    .carousel-arrow.left { left: 20px; }
    .carousel-arrow.right { right: 20px; }
    .carousel-dots { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 10px; z-index: 10; }
    .carousel-dot { width: 12px; height: 12px; border-radius: 50%; background: rgba(255,255,255,0.5); cursor: pointer; transition: 0.3s; }
    .carousel-dot.active { background: white; transform: scale(1.2); }

    .hero-overlay-container { position: relative; z-index: 5; display: flex; justify-content: flex-end; align-items: center; width: 100%; height: 100%; padding: 40px 0; margin-right: 60px; }
    .hero-form-white { background: white; border-radius: 15px; padding: 30px; width: 100%; max-width: 350px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); text-align: center; }
    .hero-form-white h3 { color: var(--win-dark); font-size: 1.3rem; font-weight: 800; margin-bottom: 20px; line-height: 1.2; }
    .hero-form-white input { background: #f5f5f5; border: 1px solid #eee; padding: 12px 15px; border-radius: 8px; width: 100%; margin-bottom: 15px; font-size: 0.95rem; outline: none; }
    .hero-form-white .terms-check { display: flex; align-items: flex-start; gap: 8px; text-align: left; margin-bottom: 20px; }
    .hero-form-white .terms-check input { width: 16px; height: 16px; margin-top: 3px; accent-color: var(--win-orange); }
    .hero-form-white .terms-check label { font-size: 0.8rem; color: var(--text-gray); }
    .hero-form-white .terms-check a { color: var(--win-orange); text-decoration: underline; }
    .btn-form-enviar { background: var(--win-dark); color: white; border: none; width: 100%; padding: 12px; border-radius: 8px; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: 0.3s; display: flex; justify-content: center; align-items: center; gap: 10px; }
    .btn-form-enviar:hover { background: var(--win-orange); }

    .hero-default-bg { background-image: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 10px 10px; padding: 60px 0; }
    .hero-container { display: flex; justify-content: space-between; align-items: center; width: 100%; }
    .hero-text { flex: 1; padding-right: 50px; }
    .hero-title-box { background-color: white; display: inline-block; padding: 5px 15px; border-radius: 8px; transform: rotate(-3deg); margin-bottom: 15px; }
    .hero-title-box h2 { color: var(--text-dark); font-size: 1.5rem; font-weight: 800; margin: 0; }
    .hero-title-box h2 span { color: var(--win-orange); }
    .hero-main-text h1 { color: white; font-size: 4rem; font-weight: 900; line-height: 1.1; text-transform: uppercase; text-shadow: 3px 3px 0px rgba(0,0,0,0.2); margin-bottom: 20px; }
    .hero-main-text h1 span { color: var(--win-dark); text-shadow: none; }
    .hero-form-card { background-color: var(--win-dark); border-radius: 20px; padding: 40px 30px; width: 100%; max-width: 400px; color: white; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
    .hero-form-card h3 { font-size: 1.5rem; font-weight: 700; margin-bottom: 25px; }
    .hero-form-card input { width: 100%; padding: 15px; border-radius: 10px; border: none; font-size: 1rem; margin-bottom: 20px; outline: none; }
    .btn-form-submit { background-color: var(--win-orange); color: white; border: none; width: 100%; padding: 15px; border-radius: 30px; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: 0.3s; margin-bottom: 15px; }

    /* ESTILOS DEL DROPDOWN DE CATÁLOGOS (NUEVO) */
    .catalogos-wrapper { position: absolute; top: 0; right: 15px; z-index: 20; }
    .btn-catalogos { background: #fff; color: var(--win-dark); border: 2px solid #eaeaea; padding: 10px 20px; border-radius: 30px; font-weight: 800; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .btn-catalogos:hover { border-color: var(--win-orange); color: var(--win-orange); transform: translateY(-2px); box-shadow: 0 6px 15px rgba(255,90,0,0.15); }
    .catalogos-dropdown { position: absolute; top: 110%; right: 0; background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid #eaeaea; width: 280px; text-align: left; display: none; flex-direction: column; overflow: hidden; z-index: 100; }
    .catalogos-dropdown.active { display: flex; animation: fadeIn 0.2s ease; }
    .catalogo-item { padding: 15px 20px; border-bottom: 1px solid #f5f5f5; color: var(--win-dark); text-decoration: none; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 12px; transition: 0.2s; }
    .catalogo-item:hover { background: #fff8f5; color: var(--win-orange); padding-left: 25px; }
    .catalogo-item i { color: #ef4444; font-size: 1.2rem; }
    .catalogo-item:last-child { border-bottom: none; }

    /* TABS Y PLANES */
    .planes-section { padding: 80px 0 20px; text-align: center; }
    .planes-header h2 { font-size: 2rem; color: var(--win-orange); font-weight: 900; margin-bottom: 10px; letter-spacing: -1px; }
    .planes-header p { font-size: 1.1rem; color: var(--win-dark); font-weight: 500; margin-bottom: 40px; }
    
    .planes-nav { display: flex; justify-content: center; gap: 40px; border-bottom: 1px solid #ddd; margin-bottom: 40px; flex-wrap: wrap; }
    .plan-tab { cursor: pointer; padding: 15px 10px; font-size: 1.1rem; font-weight: 700; color: #1a1a1a; position: relative; transition: 0.3s; }
    .plan-tab.active { color: var(--win-orange); }
    .plan-tab.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 3px; background-color: var(--win-orange); }
    .plan-tab:hover { color: var(--win-orange); }
    
    .planes-tab-content { display: none; animation: fadeIn 0.4s; }
    .planes-tab-content.active { display: block; }
    
    .planes-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; padding: 0 10px; align-items: flex-start; }

    .sub-tabs-mockup { display: flex; justify-content: center; margin-bottom: 40px; }
    .sub-tabs-mockup .sub-tab { padding: 12px 35px; font-weight: 800; font-size: 0.9rem; cursor: pointer; border: 1px solid #ddd; color: #666; transition: 0.3s; }
    .sub-tabs-mockup .sub-tab:first-child { border-radius: 8px 0 0 8px; border-right: none; }
    .sub-tabs-mockup .sub-tab:last-child { border-radius: 0 8px 8px 0; }
    .sub-tabs-mockup .sub-tab.active { background: #321e17; color: var(--win-orange); border-color: #321e17; }
    .sub-tabs-mockup.blue-theme .sub-tab.active { background: #0088cc; color: white; border-color: #0088cc; }

    /* TARJETAS WIN UI */
    .win-card { width: 100%; max-width: 310px; border: 2px solid var(--win-orange); border-radius: 20px; padding: 40px 0 0 0; background: white; position: relative; text-align: center; transition: 0.3s; overflow: hidden; display: flex; flex-direction: column; }
    .win-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
    .win-card-title { font-weight: 900; font-size: 1.1rem; margin-bottom: 5px; padding: 0 20px; }
    .win-card-speed-base { font-size: 0.95rem; font-weight: 800; color: #888; text-decoration: line-through; margin-bottom: -5px; min-height: 20px; padding: 0 20px; }
    .win-card-speed { font-size: 3.5rem; font-weight: 900; line-height: 1; margin-bottom: 5px; letter-spacing: -2px; padding: 0 20px; }
    .win-card-speed span { font-size: 1.5rem; letter-spacing: 0; }
    .win-card-promo { font-size: 0.85rem; font-weight: 700; margin-bottom: 25px; text-transform: uppercase; padding: 0 20px; }
    
    .duos-logo-text { font-size: 1.5rem; font-weight: 900; color: #1a1a1a; margin: 15px 0 25px; padding: 0 20px; }
    .duos-logo-text span { color: var(--win-orange); font-family: 'Arial Black', sans-serif; }
    .gamer-logo-text { font-size: 1.2rem; font-weight: 900; color: white; margin: 15px 0 25px; line-height: 1.1; font-family: 'Arial Black', sans-serif; padding: 0 20px; }
    .gamer-logo-text span { font-size: 2.2rem; color: var(--win-orange); display: block; letter-spacing: -1px; }

    .win-card-logo-img { height: 55px; width: auto; max-width: 90%; margin: 15px auto 25px; object-fit: contain; display: block; }
    
    .win-card.border-solid-orange .win-card-logo-img { filter: brightness(0) invert(1); }

    .win-mesh-badge { color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; position: absolute; right: 10px; top: 25%; transform: translateY(-50%); z-index: 2; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
    
    .win-card-price-pill { border-radius: 40px; padding: 12px 20px; display: inline-block; font-size: 2.2rem; font-weight: 900; margin-bottom: 10px; letter-spacing: -1px; width: 90%; margin-left: auto; margin-right: auto; }
    .win-card-price-pill span { font-size: 1rem; font-weight: 600; letter-spacing: 0; }
    .win-card-reg-price { font-size: 0.9rem; color: #666; font-weight: 600; margin-bottom: 25px; min-height: 20px; padding: 0 20px; }
    .win-card-btn { color: white; border: none; padding: 15px 20px; border-radius: 30px; font-size: 1.1rem; font-weight: 800; cursor: pointer; transition: 0.3s; width: 90%; margin: 0 auto 20px auto; display: block; }
    .win-card-btn:hover { opacity: 0.9; transform: scale(1.05); }
    
    /* ACORDEÓN DE BENEFICIOS */
    .win-card-benefits { font-weight: 700; font-size: 0.95rem; cursor: pointer; padding: 15px 20px; display: flex; align-items: center; justify-content: center; gap: 8px; border-top: 1px solid #eee; margin-top: auto; }
    .win-card-benefits i { transition: 0.3s ease; }
    .win-card-benefits.active i { transform: rotate(180deg); }
    
    .win-card.border-solid-orange .win-card-benefits,
    .win-card.border-solid-blue .win-card-benefits { border-top-color: rgba(255, 255, 255, 0.2); }

    .win-card-benefits-drawer { max-height: 0; overflow: hidden; transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1); background: transparent; padding: 0 20px; }
    .win-card-benefits-drawer.open { max-height: 600px; padding-bottom: 25px; }
    
    .benefits-drawer-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px 10px; justify-content: center; margin-top: 15px; }
    .benefit-drawer-item { display: flex; flex-direction: column; align-items: center; text-align: center; }
    
    .benefit-icon-box { background: white; width: 60px; height: 60px; border-radius: 12px; display: flex; justify-content: center; align-items: center; box-shadow: 0 5px 15px rgba(0,0,0,0.06); margin-bottom: 8px; border: 1px solid #f0f0f0; }
    .benefit-icon-box img { width: 42px; height: 42px; object-fit: contain; }
    
    .benefit-text-label { font-size: 0.72rem; font-weight: 700; line-height: 1.25; color: #333; }
    
    .win-card.border-solid-orange .benefit-text-label,
    .win-card.border-solid-blue .benefit-text-label,
    .gamer-card .benefit-text-label { color: white; }

    .win-card.border-solid-orange { background: var(--win-orange); color: white; border-color: var(--win-orange); }
    .win-card.border-solid-blue { background: #1da1f2; color: white; border-color: #1da1f2; }
    
    .gamer-wrapper { background: #0a0a0a; padding: 60px 40px; border-radius: 20px; margin-top: -10px; }
    .gamer-card { width: 100%; max-width: 310px; border: 1px solid var(--win-orange); background: #0a0a0a; border-radius: 20px; padding: 40px 0 0 0; position: relative; text-align: center; transition: 0.3s; display: flex; flex-direction: column; overflow: hidden; }
    .gamer-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(255,90,0,0.15); }
    
    @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

    /* SECCIONES ESTÁTICAS */
    .legales-banner-section { position: relative; padding-top: 40px; margin-bottom: 0; }
    .legales-content { display: flex; justify-content: flex-end; margin-bottom: 40px; }
    .legales-text-box { max-width: 55%; font-size: 0.9rem; font-weight: 500; line-height: 1.6; color: #1a1a1a; }
    .legales-text-box p { margin-bottom: 15px; }
    .legales-text-box a { color: var(--win-orange); text-decoration: underline; cursor: pointer; }
    .orange-full-banner { background-color: var(--win-orange); padding: 40px 0; width: 100%; position: relative; z-index: 1; }
    .orange-banner-content { display: flex; justify-content: flex-end; }
    .orange-text-box { max-width: 55%; color: white; }
    .orange-text-box .line1 { font-size: 1.8rem; display: block; line-height: 1.2; font-weight: 500;}
    .orange-text-box .line2 { font-size: 3.5rem; font-weight: 800; display: block; line-height: 1; margin: 0;}
    .orange-text-box .line3 { font-size: 1.5rem; font-weight: 700; display: block; line-height: 1.2; text-transform: uppercase;}
    .floating-model { position: absolute; bottom: 0; left: 10%; height: auto; max-height: 380px; z-index: 10; object-fit: contain; pointer-events: none; }

    .beneficios-destacados-section { padding: 80px 0; text-align: center; background-color: #fafafa; }
    .beneficios-destacados-section h2 { font-size: 2rem; font-weight: 900; color: var(--win-dark-light); margin-bottom: 50px; text-transform: uppercase; }
    .beneficios-tarjetas { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-bottom: 40px; }
    .tarjeta-beneficio { background: white; border: 1px solid #e0e0e0; border-radius: 12px; padding: 40px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; flex-direction: column; align-items: center; transition: transform 0.3s ease; }
    .tarjeta-beneficio:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
    .beneficio-img-icon { height: 70px; width: auto; object-fit: contain; margin-bottom: 20px; }
    .tarjeta-beneficio h4 { font-size: 1.2rem; font-weight: 900; color: var(--win-dark); margin-bottom: 15px; }
    .divider-line { width: 80%; height: 3px; background-color: var(--win-orange); margin: 0 auto 20px auto; border-radius: 2px; }
    .tarjeta-beneficio p { font-size: 0.95rem; color: var(--text-gray); line-height: 1.5; font-weight: 500; }
    .btn-quiero-beneficios { background-color: var(--win-orange); color: white; font-size: 1.2rem; font-weight: 800; padding: 15px 40px; border-radius: 50px; border: none; cursor: pointer; transition: 0.3s; box-shadow: 0 5px 15px rgba(255, 90, 0, 0.3); }
    .btn-quiero-beneficios:hover { background-color: var(--win-orange-dark); transform: scale(1.05); }

    .modal-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(0, 0, 0, 0.6); display: flex; justify-content: center; align-items: center; z-index: 2000; opacity: 0; visibility: hidden; transition: all 0.3s ease; }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    #terms-modal { z-index: 2100; }
    .modal-content { background-color: white; padding: 40px; border-radius: 12px; width: 90%; max-width: 450px; text-align: center; transform: translateY(-20px); transition: transform 0.3s ease; box-shadow: 0 20px 50px rgba(0,0,0,0.2); }
    .modal-overlay.active .modal-content { transform: translateY(0); }
    .modal-content h3 { font-size: 1.6rem; color: var(--win-dark-light); font-weight: 800; margin-bottom: 25px; line-height: 1.2; }
    .form-group { text-align: left; margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; font-size: 0.95rem; }
    .form-group input[type="text"] { width: 100%; background-color: #f5f5f5; border: 1px solid #e0e0e0; padding: 12px 15px; border-radius: 8px; font-size: 1rem; outline: none; }
    .checkbox-container { display: flex; align-items: center; gap: 10px; margin-bottom: 25px; text-align: left; }
    .checkbox-container input { accent-color: var(--win-orange); width: 18px; height: 18px; }
    .checkbox-container label { font-size: 0.85rem; color: var(--text-dark); font-weight: 500; }
    .checkbox-container label a { color: var(--win-orange); text-decoration: underline; cursor: pointer;}
    .modal-buttons { display: flex; gap: 15px; justify-content: center; }
    .modal-btn { padding: 12px 30px; border-radius: 8px; font-weight: 700; font-size: 1.1rem; border: none; cursor: pointer; transition: 0.3s; flex: 1; }
    .modal-btn-submit { background-color: var(--win-orange); color: white; }
    .modal-btn-submit:hover { background-color: var(--win-orange-dark); }
    .modal-btn-cancel { background-color: var(--win-dark-light); color: white; }
    .modal-btn-cancel:hover { background-color: #1a120f; }

    .terms-modal-content { max-width: 600px; padding: 40px 30px; }
    .terms-text-container { max-height: 50vh; overflow-y: auto; text-align: justify; margin-bottom: 25px; padding-right: 15px; color: var(--text-gray); font-size: 0.9rem; line-height: 1.6; }
    .terms-text-container p { margin-bottom: 15px; }
    .terms-text-container::-webkit-scrollbar { width: 6px; }
    .terms-text-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .terms-text-container::-webkit-scrollbar-thumb { background: var(--win-orange); border-radius: 10px; }
    .btn-listo-terms { background-color: var(--win-orange); color: white; padding: 12px 40px; border-radius: 8px; font-weight: 700; font-size: 1.1rem; border: none; cursor: pointer; transition: 0.3s; }
    .btn-listo-terms:hover { background-color: var(--win-orange-dark); }

    .cobertura-section { background-color: var(--win-dark); color: white; padding: 60px 0; }
    .cobertura-section .section-title { color: white; margin-bottom: 20px; }
    .cobertura-section p { color: #cccccc; margin-bottom: 30px; font-size: 1.1rem; }
    .btn-cobertura { background-color: var(--win-dark-light); color: white; border: 1px solid rgba(255,255,255,0.1); padding: 15px 30px; border-radius: 8px; font-weight: 600; display: inline-block; transition: 0.3s; }
    .faq-section { background-color: white; padding: 80px 0; }
    .faq-subtitle { text-align: center; color: var(--win-dark-light); font-weight: 600; margin-top: 15px; margin-bottom: 40px; font-size: 1rem; }
    .faq-container { display: flex; flex-direction: column; gap: 15px; max-width: 900px; margin: 0 auto; }
    .faq-item { border: none; border-radius: 8px; overflow: hidden; transition: 0.3s; background-color: #f6f6f6; margin-bottom: 10px; }
    .faq-header { padding: 20px 25px; background-color: #f6f6f6; display: flex; justify-content: space-between; align-items: center; cursor: pointer; font-weight: 800; color: #33241f; font-size: 1.1rem; }
    .faq-item.active .faq-header { color: var(--win-orange); }
    .faq-body { padding: 0 25px; max-height: 0; overflow-y: hidden; transition: max-height 0.4s ease; color: var(--text-gray); font-size: 0.95rem; text-align: left; }
    .faq-item.active .faq-body { padding: 0 25px 25px 25px; max-height: 2500px; } 
    .faq-body p { margin-bottom: 15px; }
    .faq-body ul { padding-left: 20px; margin-bottom: 20px; }
    .faq-body li { margin-bottom: 8px; }
    .faq-body strong { color: var(--win-dark-light); font-size: 1.05rem; display: block; margin-top: 25px; margin-bottom: 10px; }

    .whatsapp-flotante { position: fixed; bottom: 30px; right: 30px; background-color: #25d366; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 2rem; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4); z-index: 1500; transition: all 0.3s ease; animation: pulse-wa 2s infinite; }
    .whatsapp-flotante:hover { background-color: #128c7e; transform: scale(1.1); color: white; }
    @keyframes pulse-wa { 0% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); } 70% { box-shadow: 0 0 0 15px rgba(37, 211, 102, 0); } 100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); } }

    @media (max-width: 992px) {
        .hero-overlay-container { justify-content: center; margin-right: 0; }
        .hero-container { flex-direction: column; gap: 40px; }
        .hero-text { padding-right: 0; text-align: center; }
        .beneficios-tarjetas { grid-template-columns: 1fr; }
        .floating-model { left: -5%; opacity: 0.15; max-height: 300px; }
        .legales-text-box, .orange-text-box { max-width: 90%; }
        .carousel-arrow { display: none; }
        .planes-nav { gap: 15px; }
        .gamer-wrapper { padding: 40px 20px; }
    }
    
    @media (max-width: 768px) {
        .hero { min-height: auto; }
        .legales-content, .orange-banner-content { justify-content: center; text-align: center; }
        .legales-text-box, .orange-text-box { max-width: 100%; }
        .floating-model { position: static; display: block; margin: 0 auto -10px auto; max-height: 250px; opacity: 1; }
        .whatsapp-flotante { bottom: 20px; right: 20px; width: 50px; height: 50px; font-size: 1.8rem; }
        .catalogos-wrapper { position: relative; right: auto; top: auto; display: flex; justify-content: center; margin-bottom: 30px; }
        .catalogos-dropdown { right: auto; left: 50%; transform: translateX(-50%); width: 90%; }
    }

    /* =========================================================================
       NUEVOS AJUSTES DE DISEÑO DGO Y WIN TV "TWO-TONE" (SOLO FRONT)
       ========================================================================= */
    .trio-card { padding: 0 !important; border-width: 2px; border-style: solid; justify-content: space-between; }
    .trio-card-header { background: white; padding: 30px 20px; display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 120px; border-radius: 18px 18px 0 0; }
    .trio-card-body { padding: 30px 0 0 0; color: white; flex-grow: 1; display: flex; flex-direction: column; border-radius: 0 0 18px 18px; }
    .trio-price-container { display: flex; justify-content: center; align-items: baseline; gap: 4px; font-weight: 900; line-height: 1; margin-bottom: 5px; }
    .trio-price-currency { font-size: 1.8rem; font-weight: 900; }
    .trio-price-amount { font-size: 4.5rem; letter-spacing: -2px; }
    .trio-price-period { font-size: 1.2rem; font-weight: 700; margin-left: 2px; }
    .trio-card-btn { background: #ffcc00; color: #1a1a1a; border: none; padding: 15px 20px; border-radius: 30px; font-size: 1.1rem; font-weight: 900; cursor: pointer; transition: 0.3s; width: 85%; margin: 15px auto 25px auto; display: block; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .trio-card-btn:hover { transform: scale(1.05); background: #ffb800; }
    .trio-card .benefit-text-label { color: white; }
    .trio-card .win-card-benefits { margin-top: auto; border-top: 1px solid rgba(255,255,255,0.2) !important; }

    /* NUEVO: ESTILOS DGO HEADER PERSONALIZADO */
    .dgo-custom-header { width: 100%; display: flex; flex-direction: column; border-radius: 16px 16px 0 0; overflow: hidden; background: white; }
    .dgo-top { padding: 25px 15px 35px 15px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; }
    .dgo-top .dgo-main-logo { max-height: 45px; margin-bottom: 8px; }
    .dgo-top .dgo-channels { font-size: 1.15rem; font-weight: 600; color: #1a1a1a; letter-spacing: -0.3px; font-family: 'Arial', sans-serif;}
    .dgo-bottom { background: #080808; padding: 22px 10px 18px 10px; position: relative; }
    .dgo-badge { background: #ff5a00; color: white; padding: 4px 18px; border-radius: 20px; font-size: 0.85rem; font-weight: 700; position: absolute; top: -14px; left: 50%; transform: translateX(-50%); box-shadow: 0 4px 8px rgba(0,0,0,0.4); }
    .dgo-bottom-logos { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; }
    .dgo-bottom-logos img { height: 18px; object-fit: contain; }
    .dgo-plus { color: #ff5a00; font-size: 1.2rem; font-weight: 900; line-height: 1; margin: 0 2px;}
</style>

<script>
    const cmsData = <?php echo json_encode($datosIndexPHP, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;

    let currentSlide = 0;
    let slideInterval;

    window.switchTab = function(tabId, element) {
        document.querySelectorAll('.planes-tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.plan-tab').forEach(el => el.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        element.classList.add('active');
    };

    window.switchSubTab = function(parentPrefix, subId, element) {
        const parent = element.closest('.planes-tab-content');
        parent.querySelectorAll('.sub-content').forEach(el => el.style.display = 'none');
        parent.querySelectorAll('.sub-tab').forEach(el => el.classList.remove('active'));
        document.getElementById(`sub-${parentPrefix}-${subId}`).style.display = 'block';
        element.classList.add('active');
    };

    window.toggleBenefitsDrawer = function(planId, btnElement) {
        const drawer = document.getElementById(`drawer-${planId}`);
        if(drawer) {
            const isOpen = drawer.classList.contains('open');
            btnElement.closest('.planes-grid').querySelectorAll('.win-card-benefits-drawer').forEach(d => d.classList.remove('open'));
            btnElement.closest('.planes-grid').querySelectorAll('.win-card-benefits').forEach(b => b.classList.remove('active'));
            
            if(!isOpen) {
                drawer.classList.add('open');
                btnElement.classList.add('active');
            }
        }
    };

    // LÓGICA PARA OCULTAR EL DROPDOWN DE CATÁLOGOS SI SE HACE CLIC AFUERA
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('cat-dropdown');
        const btn = document.querySelector('.btn-catalogos');
        if (dropdown && btn && !dropdown.contains(e.target) && !btn.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });

    function renderIndex() {
        renderHero();
        renderRestOfPage();
        activarAccordion();
        activarModales();
    }

    function renderHero() {
        const heroSection = document.getElementById('dynamic-hero');
        if (cmsData.portadas && cmsData.portadas.length > 0) {
            heroSection.classList.remove('hero-default-bg');
            const slidesHTML = cmsData.portadas.map(portada => `<div class="carousel-slide" style="background-image: url('${portada.imagen}');" title="${portada.alt}"></div>`).join('');
            const dotsHTML = cmsData.portadas.map((_, index) => `<div class="carousel-dot ${index === 0 ? 'active' : ''}" onclick="goToSlide(${index})"></div>`).join('');

            heroSection.innerHTML = `
                <div class="carousel-container">
                    <div class="carousel-track" id="carousel-track">${slidesHTML}</div>
                    <button class="carousel-arrow left" onclick="moveSlide(-1)"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="carousel-arrow right" onclick="moveSlide(1)"><i class="fa-solid fa-chevron-right"></i></button>
                    <div class="carousel-dots" id="carousel-dots">${dotsHTML}</div>
                </div>
                <div class="container hero-overlay-container">
                    <div class="hero-form-white">
                        <h3>¡Déjanos tus datos y te llamamos!</h3>
                        <form onsubmit="event.preventDefault(); let cel = this.querySelectorAll('input[type=\\'text\\']')[0].value; let doc = this.querySelectorAll('input[type=\\'text\\']')[1].value; window.open('https://wa.me/51927671862?text=' + encodeURIComponent('Hola, por favor llámenme. Mi número de celular es ' + cel + ' y mi DNI/RUC es ' + doc), '_blank');">
                            <input type="text" placeholder="Número de Cel" required>
                            <input type="text" placeholder="DNI/CEX/RUC" required>
                            <div class="terms-check">
                                <input type="checkbox" id="slider-terms" required>
                                <label for="slider-terms">He leído y acepto los <a href="#" class="open-terms-link">Términos y Condiciones</a></label>
                            </div>
                            <button type="submit" class="btn-form-enviar"><i class="fa-regular fa-paper-plane"></i> Enviar</button>
                        </form>
                    </div>
                </div>
            `;
            startSlider();
        } else {
            heroSection.classList.add('hero-default-bg');
            heroSection.innerHTML = `
                <div class="container hero-container">
                    <div class="hero-text">
                        <div class="hero-title-box"><h2>${cmsData.hero.etiqueta} <span>${cmsData.hero.etiquetaResaltada}</span></h2></div>
                        <div class="hero-main-text"><h1>${cmsData.hero.titulo1}<br><span>${cmsData.hero.titulo2}</span></h1></div>
                    </div>
                    <div class="hero-form-card">
                        <h3>${cmsData.hero.formTitulo}</h3>
                        <form onsubmit="event.preventDefault(); let cel = this.querySelector('input[type=\\'text\\']').value; window.open('https://wa.me/51927671862?text=' + encodeURIComponent('Hola, quiero contratar un plan corporativo. Mi celular es ' + cel), '_blank');">
                            <input type="text" placeholder="Número de celular*" required>
                            <button type="submit" class="btn-form-submit">Llámame</button>
                        </form>
                        <p style="font-size: 0.7rem; color: #888; margin-top:10px;">${cmsData.hero.disclaimer}</p>
                    </div>
                </div>
            `;
        }
    }

    function moveSlide(direction) {
        const total = cmsData.portadas.length;
        currentSlide = (currentSlide + direction + total) % total;
        updateSliderUI();
    }
    function goToSlide(index) { currentSlide = index; updateSliderUI(); }
    function updateSliderUI() {
        const track = document.getElementById('carousel-track');
        const dots = document.querySelectorAll('.carousel-dot');
        if (track) track.style.transform = `translateX(-${currentSlide * 100}%)`;
        dots.forEach((dot, index) => { dot.classList.toggle('active', index === currentSlide); });
        startSlider();
    }
    function startSlider() {
        clearInterval(slideInterval);
        if(cmsData.portadas && cmsData.portadas.length > 1) { slideInterval = setInterval(() => { moveSlide(1); }, 5000); }
    }

    function renderRestOfPage() {
        const planesInternet = cmsData.planes.filter(p => p.categoria === 'INTERNET');
        const planesDuosWinTV = cmsData.planes.filter(p => p.categoria === 'DÚOS' && p.subcategoria === 'WINTV');
        const planesDuosFono = cmsData.planes.filter(p => p.categoria === 'DÚOS' && p.subcategoria === 'FONOWIN');
        const planesTriosDGO = cmsData.planes.filter(p => p.categoria === 'TRÍOS' && p.subcategoria === 'DGO');
        const planesTriosWinTV = cmsData.planes.filter(p => p.categoria === 'TRÍOS' && p.subcategoria === 'WINTV');
        const planesGamer = cmsData.planes.filter(p => p.categoria === 'GAMER');

        const buildWinCard = (plan, theme = 'default') => {
            let cardClass = 'win-card';
            let titleColor = 'var(--win-orange)';
            let speedColor = 'var(--win-orange)';
            let pricePillBg = 'var(--win-orange)';
            let pricePillColor = 'white';
            let btnBg = 'var(--win-orange)';
            let textColor = '#333';

            if (theme === 'gamer') {
                cardClass = 'gamer-card';
                textColor = 'white';
            }

            // Lógica para determinar el Título de la Tarjeta (cardTitleText)
            let cardTitleText = 'INTERNET 100% FIBRA';
            if (plan.categoria === 'DÚOS') {
                if (plan.subcategoria === 'WINTV') {
                    cardTitleText = 'INTERNET + WIN TV';
                } else if (plan.subcategoria === 'FONOWIN') {
                    cardTitleText = 'INTERNET + FONOWIN';
                }
            } else if (plan.categoria === 'TRÍOS') {
                if (plan.subcategoria === 'DGO') {
                    cardTitleText = 'DGO';
                } else if (plan.subcategoria === 'WINTV') {
                    cardTitleText = 'WIN TV';
                }
            } else if (plan.categoria === 'GAMER') {
                cardTitleText = 'INTERNET 100% FIBRA PLAN GAMER';
            }

            let logoCenter = '';
            if (plan.logoUrl) {
                logoCenter = `<img src="${plan.logoUrl}" alt="${plan.nombre}" class="win-card-logo-img">`;
            } else {
                if (theme === 'gamer') {
                    logoCenter = `<div class="gamer-logo-text">PLANES<br><span>G4MER</span>WIN</div>`;
                } else if (plan.subcategoria === 'FONOWIN') {
                    logoCenter = `<div class="duos-logo-text" style="font-size: 1.1rem; color:#555; text-transform:uppercase; margin-bottom: 30px;">TELEFONÍA<br><span style="font-size:1.8rem; color:#333; letter-spacing:-1px; text-transform:lowercase;">fonowin</span></div>`;
                } else if (plan.subcategoria === 'WINTV' && plan.categoria === 'DÚOS') {
                    logoCenter = `<div class="duos-logo-text"><span>wintv.</span> ${plan.nombre}</div>`;
                }
            }

            if (plan.categoria === 'TRÍOS') {
                if (plan.subcategoria === 'DGO') {
                    if (!plan.logoUrl) logoCenter = `<div class="duos-logo-text" style="color:#0088cc;"><span>DGO</span> ${plan.nombre}</div>`;
                    cardClass = 'win-card border-solid-blue';
                    titleColor = '#1178b5'; speedColor = '#0088cc'; pricePillBg = '#0088cc'; btnBg = '#ffcc00'; textColor = 'white';
                } else {
                    if (!plan.logoUrl) logoCenter = `<div class="duos-logo-text" style="color:white;">wintv. <span>${plan.nombre}</span></div>`;
                    cardClass = 'win-card border-solid-orange';
                    btnBg = '#ffcc00'; textColor = 'white';
                }
            }

            const extraBadge = plan.beneficioExtra && plan.beneficioExtra !== 'ESTABILIDAD GARANTIZADA' 
                ? `<div class="win-mesh-badge" style="background:${theme==='gamer'?'#333':'#111'};">${plan.beneficioExtra}</div>` 
                : '';
            
            const regPrice = plan.precioRegular ? `Precio regular: S/ ${plan.precioRegular}` : `<br>`;
            
            let textoPromoVelocidad = plan.mesesPromocion ? `DUPLICA TU VELOCIDAD X${plan.mesesPromocion} MESES` : `VELOCIDAD 100% FIBRA`;
            let textoMesesPrecio = plan.mesesPromocion ? `x${plan.mesesPromocion} meses` : `al mes`;
            const baseSpeedHTML = plan.velocidadBase ? `<div class="win-card-speed-base"><s>${plan.velocidadBase} Mbps</s></div>` : `<div class="win-card-speed-base">&nbsp;</div>`;

            // Enlace de WhatsApp Dinámico para los botones
            const wppMessage = `Hola, quiero contratar el plan ${plan.nombre} (${cardTitleText}) por S/. ${plan.precio}.`;
            const wppLink = `https://wa.me/51927671862?text=${encodeURIComponent(wppMessage)}`;
            const btnOnClick = `onclick="window.open('${wppLink}', '_blank')"`;

            let drawerHTML = '';
            if (plan.subcategoria === 'DGO') {
                drawerHTML = `
                <div class="win-card-benefits-drawer" id="drawer-${plan.id}">
                    <div class="benefits-drawer-grid">
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconFavorito.png" alt="Graba"></div>
                            <div class="benefit-text-label">Graba tu programa favorito</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconCanales.png" alt="Canales"></div>
                            <div class="benefit-text-label">+60 Canales en vivo</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconRetrocede.png" alt="Retrocede"></div>
                            <div class="benefit-text-label">Retrocede en vivo</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconDispositivos.png" alt="Dispositivos"></div>
                            <div class="benefit-text-label">4 dispositivos conectados</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconCampeonato.png" alt="Local"></div>
                            <div class="benefit-text-label">Lo mejor del campeonato local</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconCampeonato (1).png" alt="Internacional"></div>
                            <div class="benefit-text-label">Lo mejor del campeonato internacional</div>
                        </div>
                    </div>
                </div>`;
            } else if (plan.subcategoria === 'WINTV' && plan.categoria === 'TRÍOS') {
                drawerHTML = `
                <div class="win-card-benefits-drawer" id="drawer-${plan.id}">
                    <div class="benefits-drawer-grid">
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconCanales.png" alt="Canales"></div>
                            <div class="benefit-text-label">+90 Canales en vivo</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconFavorito.png" alt="Graba"></div>
                            <div class="benefit-text-label">Graba tu programa favorito</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconRetrocede.png" alt="Retrocede"></div>
                            <div class="benefit-text-label">Retrocede en vivo</div>
                        </div>
                    </div>
                </div>`;
            } else if (plan.subcategoria === 'WINTV' && plan.categoria === 'DÚOS') {
                drawerHTML = `
                <div class="win-card-benefits-drawer" id="drawer-${plan.id}">
                    <div class="benefits-drawer-grid">
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconFibra.png" alt="Fibra"></div>
                            <div class="benefit-text-label">100% Fibra Óptica</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconSimetria.png" alt="Simétrico"></div>
                            <div class="benefit-text-label">Internet Ilimitado y Simétrico</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconVelocidad.png" alt="Velocidad"></div>
                            <div class="benefit-text-label">Mayor Velocidad</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconCanales.png" alt="Canales"></div>
                            <div class="benefit-text-label">+90 Canales</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconFavorito.png" alt="Graba"></div>
                            <div class="benefit-text-label">Graba tu programa favorito</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconRetrocede.png" alt="Retrocede"></div>
                            <div class="benefit-text-label">Retrocede en vivo</div>
                        </div>
                    </div>
                </div>`;
            } else if (plan.subcategoria === 'FONOWIN') {
                drawerHTML = `
                <div class="win-card-benefits-drawer" id="drawer-${plan.id}">
                    <div class="benefits-drawer-grid">
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconFibra.png" alt="Fibra"></div>
                            <div class="benefit-text-label">100% Fibra Óptica</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconSimetria.png" alt="Simétrico"></div>
                            <div class="benefit-text-label">Internet Ilimitado y Simétrico</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconVelocidad.png" alt="Velocidad"></div>
                            <div class="benefit-text-label">Mayor Velocidad</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconTelefono.png" alt="Teléfono"></div>
                            <div class="benefit-text-label">Tu teléfono fijo estés donde estés</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconPeru.png" alt="Perú"></div>
                            <div class="benefit-text-label">Llama a todo el Perú</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconConecta.png" alt="Conecta"></div>
                            <div class="benefit-text-label">Cónecta y úsalo</div>
                        </div>
                    </div>
                </div>`;
            } else if (plan.categoria === 'GAMER') {
                drawerHTML = `
                <div class="win-card-benefits-drawer" id="drawer-${plan.id}">
                    <div class="benefits-drawer-grid">
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconNitro.png" alt="Nitro"></div>
                            <div class="benefit-text-label">120 horas de Nitro</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconIp.png" alt="IP"></div>
                            <div class="benefit-text-label">Cambio de IP</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconNat.png" alt="Nat"></div>
                            <div class="benefit-text-label">Nat 1 y Nat 2</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconFibra.png" alt="Fibra"></div>
                            <div class="benefit-text-label">100% Fibra Óptica</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconSimetria.png" alt="Simétrico"></div>
                            <div class="benefit-text-label">Internet Ilimitado y Simétrico</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconVelocidad.png" alt="Velocidad"></div>
                            <div class="benefit-text-label">Mayor velocidad</div>
                        </div>
                    </div>
                </div>`;
            } else {
                drawerHTML = `
                <div class="win-card-benefits-drawer" id="drawer-${plan.id}">
                    <div class="benefits-drawer-grid" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconFibra.png" alt="Fibra"></div>
                            <div class="benefit-text-label">100% Fibra Óptica</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconSimetria.png" alt="Simétrico"></div>
                            <div class="benefit-text-label">Internet Ilimitado y Simétrico</div>
                        </div>
                        <div class="benefit-drawer-item">
                            <div class="benefit-icon-box"><img src="images/iconVelocidad.png" alt="Velocidad"></div>
                            <div class="benefit-text-label">Mayor Velocidad</div>
                        </div>
                    </div>
                </div>`;
            }

            // ====================================================================================
            // BLOQUE REEMPLAZADO PARA LAS TARJETAS "TRÍOS" (DGO / WIN TV - ESTILO DOS COLORES)
            // ====================================================================================
            if (plan.categoria === 'TRÍOS') {
                let borderColor = plan.subcategoria === 'DGO' ? '#1da1f2' : 'var(--win-orange)';
                let bodyBg = plan.subcategoria === 'DGO' ? '#1da1f2' : 'var(--win-orange)';
                
                let headerHTML = '';

                if (plan.subcategoria === 'DGO') {
                    // Diseño personalizado inyectado para la cabecera del plan DGO
                    let dgoLogoHTML = plan.logoUrl ? `<img src="${plan.logoUrl}" alt="${plan.nombre}" class="dgo-main-logo">` : `<div class="duos-logo-text" style="margin: 0; color: #1a1a1a;"><span style="color: #1da1f2;">DGO</span> ${plan.nombre}</div>`;
                    headerHTML = `
                        <div class="dgo-custom-header">
                            <div class="dgo-top">
                                ${dgoLogoHTML}
                                <div class="dgo-channels">Más de 30 canales</div>
                            </div>
                            <div class="dgo-bottom">
                                <div class="dgo-badge">Incluye</div>
                                <div class="dgo-bottom-logos">
                                    <img src="images/l1max-white.png" alt="L1 MAX">
                                    <span class="dgo-plus">+</span>
                                    <img src="images/prime_video.png" alt="Prime Video">
                                    <span class="dgo-plus">+</span>
                                    <img src="images/dsports.png" alt="D Sports">
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    let logoHTML = '';
                    if (plan.logoUrl) {
                        logoHTML = `<img src="${plan.logoUrl}" alt="${plan.nombre}" class="win-card-logo-img" style="margin: 0; filter: none; max-height: 55px;">`;
                    } else {
                        logoHTML = `<div class="duos-logo-text" style="margin: 0; color: #1a1a1a;"><span>wintv.</span> ${plan.nombre}</div>`;
                    }
                    headerHTML = `<div class="trio-card-header">${logoHTML}</div>`;
                }

                return `
                    <div class="win-card trio-card" style="border-color: ${borderColor};">
                        ${headerHTML}
                        <div class="trio-card-body" style="background: ${bodyBg};">
                            <div class="trio-price-container">
                                <span class="trio-price-currency">S/.</span>
                                <span class="trio-price-amount">${plan.precio}</span>
                                <span class="trio-price-period">al mes</span>
                            </div>
                            <div class="win-card-reg-price" style="color:white; font-size:1.05rem; font-weight:700; margin-bottom: 10px; min-height: auto;">${plan.beneficioExtra || 'Adicional a tu plan'}</div>
                            <button class="trio-card-btn" ${btnOnClick}>¡Quiero este plan!</button>
                            ${drawerHTML}
                            <div class="win-card-benefits" style="color:white; border-top: 1px solid rgba(255,255,255,0.2) !important;" onclick="toggleBenefitsDrawer(${plan.id}, this)">Conoce los beneficios <i class="fa-solid fa-chevron-down"></i></div>
                        </div>
                    </div>
                `;
            }

            return `
                <div class="${cardClass}">
                    <div class="win-card-title" style="color:${titleColor}">${cardTitleText}</div>
                    ${baseSpeedHTML}
                    <div class="win-card-speed" style="color:${speedColor}">${plan.velocidad} <span>Mbps</span></div>
                    <div class="win-card-promo" style="color:${textColor}">${textoPromoVelocidad}</div>
                    ${logoCenter}
                    ${extraBadge}
                    <div class="win-card-price-pill" style="background:${pricePillBg}; color:${pricePillColor}">S/. ${plan.precio} <span>${textoMesesPrecio}</span></div>
                    <div class="win-card-reg-price">${regPrice}</div>
                    <button class="win-card-btn" style="background:${btnBg}" ${btnOnClick}>¡Quiero este plan!</button>
                    ${drawerHTML}
                    <div class="win-card-benefits" style="color:${titleColor};" onclick="toggleBenefitsDrawer(${plan.id}, this)">Conoce los beneficios <i class="fa-solid fa-chevron-down"></i></div>
                </div>
            `;
        };

        const renderGrid = (planes, theme = 'default') => {
            if (planes.length === 0) return '<p style="text-align:center; color:#888; padding: 40px; font-weight: 500;">Próximamente publicaremos planes para esta categoría.</p>';
            return `<div class="planes-grid">${planes.map(p => buildWinCard(p, theme)).join('')}</div>`;
        };

        // INTEGRACIÓN DEL MENÚ DESPLEGABLE DE CATÁLOGOS PDF
        let catalogosHTML = '';
        if (cmsData.catalogos && cmsData.catalogos.length > 0) {
            const listItems = cmsData.catalogos.map(cat => `
                <a href="${cat.archivo_url}" target="_blank" class="catalogo-item">
                    <i class="fa-solid fa-file-pdf"></i> ${cat.titulo}
                </a>
            `).join('');
            
            catalogosHTML = `
                <div class="catalogos-wrapper">
                    <button class="btn-catalogos" onclick="document.getElementById('cat-dropdown').classList.toggle('active')">
                        <i class="fa-solid fa-file-pdf" style="color:#ef4444;"></i> Ver Tarifarios y Catálogos <i class="fa-solid fa-chevron-down" style="font-size:0.7rem; margin-left:5px;"></i>
                    </button>
                    <div class="catalogos-dropdown" id="cat-dropdown">
                        ${listItems}
                    </div>
                </div>
            `;
        }

        document.getElementById('dynamic-planes').innerHTML = `
            <div class="container" style="position: relative;">
                ${catalogosHTML}
                <div class="planes-header">
                    <h2>¡Con WIN arma tu plan como tú quieras!</h2>
                    <p>${cmsData.planesInfo.descripcion}</p>
                </div>
                
                <div class="planes-nav">
                    <div class="plan-tab active" onclick="switchTab('tab-internet', this)">Internet</div>
                    <div class="plan-tab" onclick="switchTab('tab-duos', this)">Dúos</div>
                    <div class="plan-tab" onclick="switchTab('tab-trios', this)">Convierte a Dúo o Trío</div>
                    <div class="plan-tab" onclick="switchTab('tab-gamer', this)">Planes Gamer WIN</div>
                </div>

                <div id="tab-internet" class="planes-tab-content active">
                    ${renderGrid(planesInternet, 'default')}
                </div>

                <div id="tab-duos" class="planes-tab-content">
                    <div class="sub-tabs-mockup">
                        <div class="sub-tab active" onclick="switchSubTab('duos', 'wintv', this)">INTERNET + WIN TV</div>
                        <div class="sub-tab" onclick="switchSubTab('duos', 'fono', this)">INTERNET + FONOWIN</div>
                    </div>
                    <div id="sub-duos-wintv" class="sub-content active">${renderGrid(planesDuosWinTV, 'default')}</div>
                    <div id="sub-duos-fono" class="sub-content" style="display:none;">${renderGrid(planesDuosFono, 'default')}</div>
                </div>

                <div id="tab-trios" class="planes-tab-content">
                    <div class="sub-tabs-mockup blue-theme">
                        <div class="sub-tab active" onclick="switchSubTab('trios', 'dgo', this)">DGO</div>
                        <div class="sub-tab" onclick="switchSubTab('trios', 'wintv', this)">WIN TV</div>
                    </div>
                    <div id="sub-trios-dgo" class="sub-content active">${renderGrid(planesTriosDGO, 'trios')}</div>
                    <div id="sub-trios-wintv" class="sub-content" style="display:none;">${renderGrid(planesTriosWinTV, 'trios')}</div>
                </div>

                <div id="tab-gamer" class="planes-tab-content gamer-wrapper">
                    ${renderGrid(planesGamer, 'gamer')}
                </div>
            </div>
        `;

        document.getElementById('dynamic-legales-banner').innerHTML = `
            <div class="container">
                <div class="legales-content">
                    <div class="legales-text-box">
                        <p>${cmsData.legalesBanner.texto1}</p>
                        <p><strong><a href="#" class="open-terms-link">${cmsData.legalesBanner.textoTerminos}</a></strong></p>
                        <p>${cmsData.legalesBanner.texto2} <a href="#" class="open-terms-link">${cmsData.legalesBanner.textoCondiciones}</a>.</p>
                    </div>
                </div>
            </div>
            <div class="orange-full-banner">
                <div class="container">
                    <div class="orange-banner-content">
                        <div class="orange-text-box">
                            <span class="line1">${cmsData.legalesBanner.bannerLinea1}</span>
                            <strong class="line2">${cmsData.legalesBanner.bannerLinea2}</strong>
                            <span class="line3">${cmsData.legalesBanner.bannerLinea3}</span>
                        </div>
                    </div>
                </div>
            </div>
            <img src="${cmsData.legalesBanner.imagen}" alt="Asesora AIK" class="floating-model">
        `;

        const beneficiosHTML = cmsData.beneficiosDestacados.items.map(item => `
            <div class="tarjeta-beneficio">
                <img src="${item.imagen}" alt="${item.titulo}" class="beneficio-img-icon">
                <h4>${item.titulo}</h4>
                <div class="divider-line"></div>
                <p>${item.descripcion}</p>
            </div>
        `).join('');

        document.getElementById('dynamic-beneficios-destacados').innerHTML = `
            <div class="container">
                <h2>${cmsData.beneficiosDestacados.titulo}</h2>
                <div class="beneficios-tarjetas">${beneficiosHTML}</div>
                <button class="btn-quiero-beneficios" id="btn-open-lead-modal">${cmsData.beneficiosDestacados.textoBoton}</button>
            </div>
        `;

        document.getElementById('dynamic-modal-container').innerHTML = `
            <div class="modal-overlay" id="lead-modal">
                <div class="modal-content">
                    <h3>${cmsData.modalLead.titulo}</h3>
                    <form id="form-beneficios">
                        <div class="form-group"><label>${cmsData.modalLead.labelCelular}</label><input type="text" required></div>
                        <div class="form-group"><label>${cmsData.modalLead.labelDNI}</label><input type="text" required></div>
                        <div class="checkbox-container"><input type="checkbox" id="terms-modal-checkbox" required><label for="terms-modal-checkbox">${cmsData.modalLead.labelTerminos}</label></div>
                        <div class="modal-buttons">
                            <button type="submit" class="modal-btn modal-btn-submit">${cmsData.modalLead.btnSubmit}</button>
                            <button type="button" class="modal-btn modal-btn-cancel" id="btn-close-lead-modal">${cmsData.modalLead.btnCancel}</button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        const parrafosTerms = cmsData.politicaPrivacidad.parrafos.map(p => `<p>${p}</p>`).join('');
        document.getElementById('dynamic-terms-modal-container').innerHTML = `
            <div class="modal-overlay" id="terms-modal">
                <div class="modal-content terms-modal-content">
                    <h3>${cmsData.politicaPrivacidad.titulo}</h3>
                    <div class="terms-text-container">${parrafosTerms}</div>
                    <button type="button" class="btn-listo-terms" id="btn-close-terms">${cmsData.politicaPrivacidad.btnCerrar}</button>
                </div>
            </div>
        `;

        document.getElementById('dynamic-cobertura').innerHTML = `
            <div class="container">
                <h2 class="section-title">${cmsData.cobertura.titulo}</h2>
                <p>${cmsData.cobertura.descripcion}</p>
                <a href="${cmsData.cobertura.linkWpp}" class="btn-cobertura" target="_blank">${cmsData.cobertura.textoBoton}</a>
            </div>
        `;

        const terminosHTML = cmsData.terminosFrecuentes.items.map((term, index) => {
            const isActive = index === 0 ? 'active' : '';
            const icon = index === 0 ? 'fa-xmark' : 'fa-plus';
            return `
                <div class="faq-item ${isActive}">
                    <div class="faq-header">${term.pregunta} <i class="fa-solid ${icon}"></i></div>
                    <div class="faq-body">${term.respuesta}</div>
                </div>
            `;
        }).join('');
        document.getElementById('dynamic-terminos').innerHTML = `
            <div class="container">
                <h2 class="section-title section-title-center">${cmsData.terminosFrecuentes.titulo}</h2>
                <p class="faq-subtitle">${cmsData.terminosFrecuentes.subtitulo}</p>
                <div class="faq-container">${terminosHTML}</div>
            </div>
        `;
    }

    function activarAccordion() {
        const faqItems = document.querySelectorAll('.faq-item');
        faqItems.forEach(item => {
            const header = item.querySelector('.faq-header');
            header.addEventListener('click', () => {
                const isActive = item.classList.contains('active');
                faqItems.forEach(faq => { faq.classList.remove('active'); faq.querySelector('i').classList.replace('fa-xmark', 'fa-plus'); });
                if (!isActive) { item.classList.add('active'); item.querySelector('i').classList.replace('fa-plus', 'fa-xmark'); }
            });
        });
    }

    function activarModales() {
        const leadModal = document.getElementById('lead-modal');
        const btnOpenLead = document.getElementById('btn-open-lead-modal');
        const btnCloseLead = document.getElementById('btn-close-lead-modal');
        const formLead = document.getElementById('form-beneficios');
        if(btnOpenLead) btnOpenLead.addEventListener('click', () => leadModal.classList.add('active'));
        if(btnCloseLead) btnCloseLead.addEventListener('click', () => leadModal.classList.remove('active'));
        if(leadModal) leadModal.addEventListener('click', (e) => { if (e.target === leadModal) leadModal.classList.remove('active'); });
        if(formLead) formLead.addEventListener('submit', (e) => {
            e.preventDefault();
            const cel = formLead.querySelectorAll('input[type="text"]')[0].value;
            const doc = formLead.querySelectorAll('input[type="text"]')[1].value;
            window.open('https://wa.me/51927671862?text=' + encodeURIComponent('Hola, me interesan los beneficios para mi empresa. Celular: ' + cel + ', DNI/RUC: ' + doc), '_blank');
            leadModal.classList.remove('active');
            formLead.reset();
        });

        const termsModal = document.getElementById('terms-modal');
        const btnCloseTerms = document.getElementById('btn-close-terms');
        const openTermsLinks = document.querySelectorAll('.open-terms-link');
        openTermsLinks.forEach(link => { link.addEventListener('click', (e) => { e.preventDefault(); termsModal.classList.add('active'); }); });
        if(btnCloseTerms) btnCloseTerms.addEventListener('click', () => termsModal.classList.remove('active'));
        if(termsModal) termsModal.addEventListener('click', (e) => { if (e.target === termsModal) termsModal.classList.remove('active'); });
    }

    document.addEventListener('DOMContentLoaded', renderIndex);
</script>

<?php require_once 'includes/footer.php'; ?>