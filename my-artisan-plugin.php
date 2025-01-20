<?php
/*
Plugin Name: Kazverse Artisan Plugin
Description: A custom WordPress plugin by Kazverse for managing artisan registration and service requests.
Version: 1.0
Author: Kazverse
Author URI: https://kazverse.com
Text Domain: kazverse-artisan-plugin
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Include artisan-related files
include_once plugin_dir_path( __FILE__ ) . 'includes/artisans/artisan-cpt.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/artisans/artisan-registration-form.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/artisans/artisan-form-handler.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/artisans/artisan-helpers.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/artisans/artisan-user-registration.php';

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
