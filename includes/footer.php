<?php
/**
 * Archivo: includes/footer.php
 * Descripción: Pie de página de la web con acceso discreto al panel administrador.
 */
?>
    <!-- =========================================
         FOOTER PRINCIPAL
         ========================================= -->
    <footer class="main-footer">
        <div class="container footer-container">
            
            <!-- Columna 1: Logo WIN Partner -->
            <div class="footer-col footer-logo-box">
                <span class="partner-text">PARTNER</span>
                <!-- CSS puro para emular el logo de WIN en blanco si no tienes la imagen -->
                <div class="win-logo-emulation">WIN - FIBRA</div>
            </div>

            <!-- Columna 2: Agencia Autorizada -->
            <div class="footer-col footer-agency-box">
                <div class="agency-badge">
                    WIN - FIBRA | AGENCIA AUTORIZADA -
                </div>
            </div>

            <!-- Columna 3: Enlaces Legales -->
            <div class="footer-col footer-links-box">

                <!-- Enlaza al modal dinámico del index.php -->
                <a href="#" class="open-terms-link">POLÍTICA DE PRIVACIDAD</a>
            </div>

            <!-- Columna 4: Contacto WhatsApp -->
            <div class="footer-col footer-contact-box">
                <div class="contact-top">
                    Escríbenos al <i class="fa-brands fa-whatsapp wpp-icon"></i>
                </div>
                <a href="https://wa.me/51927671862" class="contact-number" target="_blank" rel="noopener noreferrer">
                    +51 927 671 862
                </a>
            </div>

        </div>

        <!-- =========================================
             BOTÓN DISCRETO PANEL ADMINISTRADOR
             ========================================= -->
        <div class="admin-access-bar">
            <div class="container admin-bar-container">
                <p class="copyright-text">© <?php echo date('Y'); ?> WIN - FIBRA. Todos los derechos reservados.</p>
                
                <!-- Botón oculto/discreto -->
                <a href="admin/dashboard.php" class="btn-admin-access" title="Acceder al Panel Administrador">
                    <i class="fa-solid fa-lock"></i> <span>Acceso Partner</span>
                </a>
            </div>
        </div>
    </footer>

    <!-- =========================================
         ESTILOS DEL FOOTER
         ========================================= -->
    <style>
        .main-footer {
            background-color: #291c16; /* Color marrón oscuro corporativo de WIN */
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
            margin-top: auto;
            position: relative;
        }

        .footer-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 40px 15px;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* 1. Logo Box */
        .footer-logo-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .partner-text {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: -5px;
        }
        .win-logo-emulation {
            font-size: 3.5rem;
            font-weight: 800;
            letter-spacing: -2px;
            line-height: 1;
            margin-bottom: 2px;
            font-family: 'Arial Black', sans-serif;
        }
        .slogan-text {
            font-size: 0.6rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: #d1d5db;
        }

        /* 2. Agency Badge */
        .footer-agency-box {
            flex: 1;
            display: flex;
            justify-content: center;
        }
        .agency-badge {
            background-color: #ffffff;
            color: var(--win-orange, #ff5a00);
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 0.95rem;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            text-transform: uppercase;
        }

        /* 3. Links Legales */
        .footer-links-box {
            display: flex;
            flex-direction: column;
            text-align: right;
            gap: 8px;
        }
        .footer-links-box a {
            color: #ffffff;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            transition: 0.3s ease;
        }
        .footer-links-box a:hover {
            color: var(--win-orange, #ff5a00);
            text-decoration: underline;
        }

        /* 4. Contacto */
        .footer-contact-box {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        .contact-top {
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 2px;
        }
        .wpp-icon {
            color: #25d366; /* Verde WhatsApp */
            font-size: 1.6rem;
        }
        .contact-number {
            color: var(--win-orange, #ff5a00);
            font-size: 2.2rem;
            font-weight: 900;
            text-decoration: none;
            line-height: 1;
            transition: 0.3s;
        }
        .contact-number:hover {
            opacity: 0.8;
        }

        /* =========================================
           BARRA SECRETA Y BOTÓN DE ADMIN
           ========================================= */
        .admin-access-bar {
            background-color: #1a110d; /* Un tono aún más oscuro para separar */
            padding: 15px 0;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        .admin-bar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 15px;
        }
        .copyright-text {
            font-size: 0.75rem;
            color: #888;
            margin: 0;
        }
        
        /* El Botón "Mágico" */
        .btn-admin-access {
            background-color: rgba(255,255,255,0.05);
            color: #666; /* Texto apagado para pasar desapercibido */
            border: 1px solid rgba(255,255,255,0.05);
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        /* Efecto Sorpresa al Hover */
        .btn-admin-access:hover {
            background-color: var(--win-orange, #ff5a00);
            color: #ffffff;
            border-color: var(--win-orange, #ff5a00);
            box-shadow: 0 4px 15px rgba(255, 90, 0, 0.3);
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .footer-container {
                flex-direction: column;
                text-align: center;
                gap: 30px;
            }
            .footer-agency-box { width: 100%; order: -1; margin-bottom: 10px; }
            .footer-logo-box, .footer-links-box, .footer-contact-box { align-items: center; text-align: center; }
            .agency-badge { font-size: 0.8rem; padding: 10px 15px; }
            .admin-bar-container { flex-direction: column; gap: 15px; text-align: center; }
        }
    </style>

</body>
</html>