<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Global Services Taxonomy
 */
function register_global_services_taxonomy() {
    $labels = array(
        'name'              => _x('Global Services', 'taxonomy general name', 'textdomain'),
        'singular_name'     => _x('Global Service', 'taxonomy singular name', 'textdomain'),
        'search_items'      => __('Search Global Services', 'textdomain'),
        'all_items'         => __('All Global Services', 'textdomain'),
        'parent_item'       => __('Parent Global Service', 'textdomain'),
        'parent_item_colon' => __('Parent Global Service:', 'textdomain'),
        'edit_item'         => __('Edit Global Service', 'textdomain'),
        'update_item'       => __('Update Global Service', 'textdomain'),
        'add_new_item'      => __('Add New Global Service', 'textdomain'),
        'new_item_name'     => __('New Global Service Name', 'textdomain'),
        'menu_name'         => __('Global Services', 'textdomain'),
    );

    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true, // True for categories, false for tags
        'public'            => true,
        'show_ui'           => true,
        'show_in_menu'      => 'global-services-menu', // Standalone menu slug
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
        'show_in_rest'      => true, // For Gutenberg support
        'menu_icon'         => 'dashicons-networking', // Custom Dashicon
    );
    
    register_taxonomy('global_services', array('post'), $args);
}
add_action('init', 'register_global_services_taxonomy');

/**
 * Add Menu Page for Global Services
 */
function add_global_services_menu() {
    add_menu_page(
        __('Global Services', 'textdomain'),
        __('Global Services', 'textdomain'),
        'manage_options', // Capability required to access
        'edit-tags.php?taxonomy=global_services',
        '',
        'dashicons-networking', // Dashicon for the menu
        5                      // Position in admin menu
    );
}
add_action('admin_menu', 'add_global_services_menu');
