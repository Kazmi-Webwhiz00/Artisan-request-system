<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


require_once plugin_dir_path(__FILE__) . 'views/job-meta-box.php';


/**
 * Register "Service Job" Custom Post Type
 */
function register_service_job_cpt() {
    $labels = array(
        'name'               => __('Service Jobs', 'textdomain'),
        'singular_name'      => __('Service Job', 'textdomain'),
        'add_new'            => __('Add New Job', 'textdomain'),
        'add_new_item'       => __('Add New Service Job', 'textdomain'),
        'edit_item'          => __('Edit Service Job', 'textdomain'),
        'new_item'           => __('New Service Job', 'textdomain'),
        'all_items'          => __('All Service Jobs', 'textdomain'),
        'view_item'          => __('View Service Job', 'textdomain'),
        'search_items'       => __('Search Service Jobs', 'textdomain'),
        'not_found'          => __('No service jobs found', 'textdomain'),
        'not_found_in_trash' => __('No service jobs found in Trash', 'textdomain'),
        'menu_name'          => __('Service Jobs', 'textdomain'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false, // Not directly visible in frontend
        'show_ui'            => true,  // Show in admin panel
        'supports'           => array('title'), // Basic fields
        'capability_type'    => 'post',
        'has_archive'        => false,
        'rewrite'            => array('slug' => 'service-jobs'),
        'show_in_rest'       => true,  // Enable REST API support
        'menu_icon'          => 'dashicons-hammer', // Icon for WP Admin Menu
        'taxonomies'         => array('global_services'), // Assign to global_services taxonomy
    );

    register_post_type('service_job', $args);
}
add_action('init', 'register_service_job_cpt');

/**
 * Register meta fields for "service_job" CPT.
 */
function register_service_job_meta_fields() {
    $fields = array(
        // Job Details
        'job_description'    => array('type' => 'string', 'single' => true, 'show_in_rest' => true),
        'job_location'       => array('type' => 'string', 'single' => true, 'show_in_rest' => true),
        'job_zip_code'       => array('type' => 'string', 'single' => true, 'show_in_rest' => true),
        'job_details_json'   => array('type' => 'string', 'single' => true, 'show_in_rest' => true), // JSON Data

        // Client Details
        'client_name'        => array('type' => 'string', 'single' => true, 'show_in_rest' => true),
        'client_email'       => array('type' => 'string', 'single' => true, 'show_in_rest' => true),
        'client_phone'       => array('type' => 'string', 'single' => true, 'show_in_rest' => true),

        // Submission Date
        'submitted_at'       => array('type' => 'string', 'single' => true, 'show_in_rest' => true),
    );

    foreach ($fields as $meta_key => $args) {
        register_post_meta('service_job', $meta_key, $args);
    }
}
add_action('init', 'register_service_job_meta_fields');
