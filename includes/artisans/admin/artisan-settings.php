<?php
require_once plugin_dir_path(__FILE__) . '/pages/docs-page.php'; // Include Docs page
require_once plugin_dir_path(__FILE__) . '/pages/general-settings.php';



function artisan_register_admin_settings_page() {
    add_submenu_page(
        'edit.php?post_type=artisan', // Parent menu (Artisans CPT)
        __( 'Settings', 'textdomain' ), // Page title
        __( 'Settings', 'textdomain' ), // Menu title
        'manage_options', // Capability required
        'manage_settings', // Menu slug
        'render_artisan_admin_settings_page' // Callback function
    );

    
}
add_action( 'admin_menu', 'artisan_register_admin_settings_page' );

function render_artisan_admin_settings_page() {
    // Define tabs - only AI and Docs tabs are active; the others are commented out.
    $tabs = array(
        'docs'           => __('Docs', 'textdomain'),
        'general'        => __('General Settings', 'textdomain'),
    );

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Artisan Settings', 'textdomain'); ?></h1>

        <!-- Tab Navigation -->
        <h2 class="kw-artisan-nav-tab-wrapper">
            <?php foreach ($tabs as $tab_key => $tab_label): ?>
                <a href="#<?php echo esc_attr($tab_key); ?>" class="kw-artisan-nav-tab" data-tab="<?php echo esc_attr($tab_key); ?>">
                    <?php echo esc_html($tab_label); ?>
                </a>
            <?php endforeach; ?>
        </h2>
        <!-- Render Tab Content -->
        <div class="kw-artisan-tab-content">
        <div id="kw-artisan-docs" class="kw-artisan-tab-pane" style="display: none;">
                <?php ws_render_docs_page(); ?>
            </div>
            <div id="kw-artisan-general" class="kw-artisan-tab-pane" style="display: none;">
                <?php ws_render_general_settings_page(); ?>
            </div>
        </div>
    </div>
    <?php
    }