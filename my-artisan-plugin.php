<?php
/*
Plugin Name: Kazverse Artisan Plugin Disabled
Description: A custom WordPress plugin by Kazverse for managing artisan registration and service requests.
Version: 1.1.1
Author: Kazverse
Author URI: https://kazverse.com
Text Domain: kazverse-artisan-plugin
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Include artisan-related files
include_once plugin_dir_path( __FILE__ ) . 'includes/artisans/artisan-cpt.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/jobs/jobs-cpt.php';
include_once plugin_dir_path(__FILE__) .  'includes/jobs/jobs-listing.php';

include_once plugin_dir_path( __FILE__ ) . 'includes/artisans/artisan-registration-form.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/artisans/artisan-form-handler.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/artisans/artisan-approval.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/artisans/artisan-helpers.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/artisans/artisan-user-registration.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/general-helpers/image-upload.php';


include_once plugin_dir_path(__FILE__) . 'includes/taxonomies/global-services-taxonomy.php';
include_once plugin_dir_path(__FILE__) . 'includes/artisans/admin/artisan-settings.php';

include_once plugin_dir_path(__FILE__) . 'includes/artisans/artisan-login.php';

include_once plugin_dir_path(__FILE__) . 'includes/service-form/service-form-cpt.php';
include_once plugin_dir_path(__FILE__) . 'includes/service-form/serviceForm/backend/form-field-table-sql.php';
include_once plugin_dir_path(__FILE__) .  'includes/service-form/FieldTypes.php';
include_once plugin_dir_path(__FILE__) . 'includes/service-form/serviceForm/backend/admin-view.php';
include_once plugin_dir_path(__FILE__) . 'includes/service-form/serviceForm/frontend/form-renderer.php';
include_once plugin_dir_path(__FILE__) . 'includes/service-search/service-search.php';



// Plugin initialization function
// function kazverse_artisan_plugin_init() {
//     // Initialize the custom post type
//     kazverse_register_artisan_cpt();
// }
// add_action( 'init', 'kazverse_artisan_plugin_init' );

// Shortcode for rendering the artisan registration form
function kazverse_artisan_form_shortcode() {
    return kazverse_render_registration_form();
}
add_shortcode( 'artisan_registration_form', 'kazverse_artisan_form_shortcode' );



/**
 * Hook into plugin activation to create the table
 */
function form_field_table_activation_hook() {
    create_form_field_table();
}

register_activation_hook(__FILE__, 'form_field_table_activation_hook');


function enqueue_field_types($hook) {
    global $post;

    if ($hook === 'post.php' || $hook === 'post-new.php') {
        wp_enqueue_script('field-types', plugin_dir_url(__FILE__) . 'includes/service-form/FieldTypes.js', ['jquery'], false, true);
    }
}
add_action('admin_enqueue_scripts', 'enqueue_custom_meta_box_assets');


// Enqueue CSS for artisan registration form
function enqueue_helper_form_styles() {
    wp_enqueue_style(
        'helper-form-css', // Handle for the stylesheet
        plugin_dir_url(__FILE__) . 'includes/general-helpers/forms/css/form-helper.css', // Path to the CSS file
        array(), // Dependencies (if any)
        null // Version number (optional, set to null if not versioned)
    );

    wp_enqueue_script('field-types', plugin_dir_url(__FILE__) . 'includes/general-helpers/forms/css/form-helper.js', ['jquery'], false, true);
    wp_enqueue_script('zip-code-helper', plugin_dir_url(__FILE__) . 'includes/general-helpers/forms/css/zipcode-helper.js', ['jquery'], false, true);

}

add_action('wp_enqueue_scripts', 'enqueue_helper_form_styles');
