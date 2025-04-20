<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ==========================================================
 * Register the Settings, Sections, and Fields
 * ==========================================================
 * This function registers the settings, sections, and fields 
 * for the "General Settings" page using the page slug.
 */
function artisan_register_general_settings() {
    // Register the setting
    register_setting(
        'artisan_general_settings', // Option group
        'artisan_custom_redirect_url',  // Option name
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'artisan',
        )
    );

    // Register the section
    add_settings_section(
        'redirectUrl_section', // Section ID
        __('Custom Redirect Url Settings', 'textdomain'), // Section Title
        'artisan_render_slugurl_section', // Callback for rendering the section
        'kw-artisan-general-settings-page' // Page slug
    );
}
add_action('admin_init', 'artisan_register_general_settings');

/**
 * ==========================================================
 * Render the Settings Page
 * ==========================================================
 * This function renders the "General Settings" page for 
 * wordsearch settings.
 */
function ws_render_general_settings_page() {
    ?>
    <div class="kw-settings-wrap">
        <h1><?php esc_html_e('General Settings', 'textdomain'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('artisan_general_settings'); // Option group
            do_settings_sections('kw-artisan-general-settings-page'); // Render sections
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * ==========================================================
 * Render the Slug Section
 * ==========================================================
 * This function includes the template for the "Custom Slug Settings" section.
 */
function artisan_render_slugurl_section() {
    include plugin_dir_path(__FILE__) . '../templates/slugurl-section.php';
}
