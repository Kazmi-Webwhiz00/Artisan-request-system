<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create or update the custom database table for form fields
 */
function create_form_field_table() {
    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'form_fields'; // Table name with WordPress prefix

    $charset_collate = $wpdb->get_charset_collate();

    // SQL statement for creating the table with the new `field_external_id` column
    $sql = "CREATE TABLE $table_name (
        field_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        service_form_id BIGINT(20) UNSIGNED NOT NULL,
        field_external_id VARCHAR(255) NOT NULL,
        field_type VARCHAR(50) NOT NULL,
        field_label VARCHAR(255) NOT NULL,
        field_description TEXT NULL,
        is_required BOOLEAN NOT NULL DEFAULT 0,
        field_order INT(11) NOT NULL DEFAULT 0,
        field_options LONGTEXT NULL,
        PRIMARY KEY (field_id),
        KEY service_form_id (service_form_id)
    ) $charset_collate;";

    // Include the upgrade file and execute dbDelta
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Debugging: Log any errors
    if (!empty($wpdb->last_error)) {
        error_log('Error creating or updating form_fields table: ' . $wpdb->last_error);
    }
}

// Hook into the WordPress initialization to ensure the table is created/updated
add_action('init', 'create_form_field_table');
