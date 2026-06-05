# WIN-FIBRA AIK 🚀 | Corporate Connectivity & Management CMS

[![PHP Version](https://img.shields.io/badge/php-%5E8.0-777bb4.svg?style=flat-square&logo=php)](https://www.php.net/) [![Vanilla JS](https://img.shields.io/badge/javascript-ES6+-f7df1e.svg?style=flat-square&logo=javascript)](https://developer.mozilla.org/es/docs/Web/JavaScript) [![CSS3 / UI](https://img.shields.io/badge/CSS3-Custom_Properties-1572B6.svg?style=flat-square&logo=css3)](https://developer.mozilla.org/es/docs/Web/CSS) [![MySQL](https://img.shields.io/badge/MySQL-PDO_Secured-4479A1.svg?style=flat-square&logo=mysql)](https://www.mysql.com/)

Este repositorio documenta la **arquitectura monolítica de alto rendimiento, el ecosistema de conversión UI/UX y el motor de administración (CMS) personalizado** desarrollado para **AIK SAC** (Agencia Autorizada WIN-FIBRA).

El proyecto abandona las dependencias de frameworks pesados en favor de una solución ágil (Vanilla Stack), orientada a maximizar la velocidad de carga (LCP), optimizar la experiencia de usuario corporativa y centralizar el control de activos digitales (Planes, Banners y Tarifarios legales) bajo una interfaz de administración *Glassmorphism* intuitiva.

🌟 Aspectos Destacados de Ingeniería y UX/UI
1. Inyección de Datos Híbrida (SSR a CSR) sin Latencia
Se desarrolló un motor de renderizado donde PHP actúa como puente de datos y JavaScript como constructor del DOM interactivo:

JSON State Hydration: El servidor (index.php) extrae la información de la base de datos (Planes, Portadas, Catálogos) y la inyecta directamente en el cliente mediante un objeto json_encode securizado.

Navegación Cero-Fricción: La transición entre categorías (Internet, Dúos, Tríos, Gamer) y la apertura de modales de beneficios ocurren de forma instantánea en la memoria del navegador, eliminando los tiempos de recarga del servidor y reduciendo la tasa de rebote.

2. Arquitectura UI Multitemática Contextual
Las tarjetas de planes (Cards) fueron diseñadas con un enfoque camaleónico para maximizar la comprensión visual del usuario corporativo:

Diseño "Two-Tone" (Tríos): Implementación de una interfaz dividida (blanco superior / color inferior sólido) que diferencia inmediatamente los paquetes DGO (Azul corporativo) y WIN TV (Naranja vibrante).

Dark Mode Aislado (Planes Gamer): Inversión de polaridad cromática (#0a0a0a de fondo con acentos neón) exclusiva para el segmento gaming, comunicando rendimiento y exclusividad de forma subconsciente.

3. Blindaje Back-End e Interceptores de Fallos (Senior Level)
Auto-Migración y Sanación Estructural: Los módulos principales (planes.php, catalogos.php) cuentan con lógica CREATE TABLE IF NOT EXISTS y ALTER TABLE embebida. El sistema detecta columnas faltantes y actualiza su propia estructura de base de datos de manera autónoma al ser ejecutado en nuevos entornos.

Interceptor de Caídas Silenciosas: Implementación de un detector avanzado de límites de servidor (post_max_size y upload_max_filesize). Si PHP vacía las variables globales por exceso de peso al subir un PDF tarifario, el sistema atrapa la anomalía y guía al administrador con instrucciones técnicas precisas.

4. Automatización de Embudo (WhatsApp Tracking)
Pre-formateo Dinámico de Leads: La UI de conversión concatena dinámicamente el nombre exacto del plan, el precio y la categoría visualizada en una cadena URL-encoded. Esto permite que el usuario inicie una conversación de WhatsApp con el equipo de ventas con la intención de compra pre-redactada, acortando el ciclo de venta.

🛠️ Stack Tecnológico y Seguridad
Lenguaje y Core: PHP Nativo 8.x.

Base de Datos y Seguridad: MySQL / MariaDB operado exclusivamente mediante la abstracción PDO.

Prevención total de Inyección SQL vía Prepared Statements.

Desactivación de emulación de consultas (PDO::ATTR_EMULATE_PREPARES = false) para seguridad a nivel driver.

Gestión de Sesiones: Hash de contraseñas unidireccional con mitigación de ataques de fijación de sesión (session_regenerate_id()).

Estilos y Maquetación: CSS Puro modularizado mediante Custom Properties (Variables) para temas consistentes y Media Queries fluidas.

Componentes de UI: FontAwesome 6 (Iconografía) y Google Fonts (Familia Poppins para legibilidad geométrica corporativa).

📂 Arquitectura del Proyecto
/
├── config/
│   └── conexion.php           # Core Data: Configuración DSN, PDO y manejador de excepciones
├── includes/
│   ├── header.php             # Master Layout: Metadatos, CSS UI System y Navegación Sticky
│   └── footer.php             # Cierre corporativo, Enlaces legales y Acceso Backdoor admin
├── admin/                     # Entorno de Gestión Privada (Panel Admin)
│   ├── includes/
│   │   └── sidebar.php        # Menú lateral responsivo (Glassmorphism & Overlay Mobile)
│   ├── catalogos.php          # Gestor CRUD de Tarifarios PDF con validador MIME-Type y peso
│   ├── dashboard.php          # Hub analítico con saludo dinámico según franja horaria
│   ├── login.php              # Autenticación segura y prevención de accesos no autorizados
│   ├── logout.php             # Destrucción controlada del array de sesión global
│   ├── planes.php             # Gestor CRUD Multitemático (Internet, Dúos, Tríos, Gamer)
│   └── portadas.php           # Gestor del Hero Slider con optimización WEBP/PNG/JPG
└── index.php                  # Landing Page SSR+CSR con hidratación asíncrona de colecciones

⚙️ Estándares de Despliegue y Mantenimiento
Requisitos de Servidor (php.ini):

El sistema de catálogos requiere límites de subida holgados para tarifarios densos en gráficos. Ajustar obligatoriamente:

upload_max_filesize = 50M

post_max_size = 50M

memory_limit = 128M (Recomendado)

Permisos de Sistema de Archivos:

Otorgar permisos de escritura (chmod 755 o 775 dependiendo del umask) a la carpeta raíz para la auto-generación de los repositorios dinámicos /uploads/portadas/ y /uploads/catalogos/.

Inicialización de Base de Datos:

Solo es necesario crear la base de datos definida en config/conexion.php y una tabla inicial para el administrador (admins). Las entidades de contenido (planes, portadas, catalogos) se autoconstruirán mediante la IA del backend durante la primera navegación del Superusuario.
