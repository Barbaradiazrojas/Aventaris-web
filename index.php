<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?><?php bloginfo('name'); ?></title>

    <!-- Favicon dinámico (Icono de la pestaña del navegador) -->
    <link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/assets/logos/aventaris-icono.png">
    <link rel="apple-touch-icon" href="<?php echo get_template_directory_uri(); ?>/assets/logos/aventaris-icono.png">

    <!-- Carga de la hoja de estilo principal de WordPress -->
    <link rel="stylesheet" href="<?php echo esc_url(get_stylesheet_uri()); ?>">

    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

    <!-- Cabecera con Logo Principal -->
    <header class="site-header">
        <div class="logo-container">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/logos/aventaris-logo.png" alt="Aventaris Logo" class="main-logo">
            </a>
        </div>
    </header>

    <main class="site-content">
        <!-- Sección de Cotización -->
        <section id="cotizar" class="quote-section">
            <h2>Solicita tu Cotización</h2>

            <form action="<?php echo get_template_directory_uri(); ?>/procesar-cotizacion.php" method="POST" id="form-cotizacion">
                <!-- Campo trampa Anti-Spam (Honeypot) -->
                <div style="display:none;">
                    <input type="text" name="sitio_web" tabindex="-1" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="nombre">Nombre completo *</label>
                    <input type="text" id="nombre" name="nombre" required maxlength="100">
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono *</label>
                    <input type="tel" id="telefono" name="telefono" required maxlength="20">
                </div>

                <div class="form-group">
                    <label for="servicio">Tipo de Servicio *</label>
                    <select id="servicio" name="servicio" required>
                        <option value="">Selecciona un servicio</option>
                        <option value="Transporte de pasajeros">Transporte de pasajeros</option>
                        <option value="Servicio de turismo">Servicio de turismo</option>
                        <option value="Transporte de carga">Transporte de carga</option>
                        <option value="Asesoría en licitaciones">Asesoría en licitaciones</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="origen">Origen *</label>
                    <input type="text" id="origen" name="origen" required maxlength="200">
                </div>

                <div class="form-group">
                    <label for="destino">Destino (Opcional)</label>
                    <input type="text" id="destino" name="destino" maxlength="200">
                </div>

                <div class="form-group">
                    <label for="descripcion">Detalle de la solicitud *</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required maxlength="2000"></textarea>
                </div>

                <button type="submit" class="btn-submit">Enviar Cotización</button>
            </form>
        </section>
    </main>

    <!-- Pie de página con Sellos e Insignias -->
    <footer class="site-footer">
        <div class="badges-container">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/logos/logo-chilecompra-original.png" alt="ChileCompra Mercado Público" class="badge-img">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/logos/ic_sello-mujer.png" alt="Sello Empresa Mujer" class="badge-img">
        </div>
        <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. Todos los derechos reservados.</p>
    </footer>

    <?php wp_footer(); ?>
</body>
</html>