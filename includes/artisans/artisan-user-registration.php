<?php
// artisan-user-registration.php
if ( ! defined('ABSPATH') ) exit;

// 1) Hook for AJAX (logged out + logged in)
add_action('wp_ajax_nopriv_create_artisan_user', 'ajax_create_artisan_user');
add_action('wp_ajax_create_artisan_user',        'ajax_create_artisan_user');

/**
 * AJAX callback to create WP user with role "artisan".
 */
function ajax_create_artisan_user() {
    // Parse incoming data from $_POST
    // (or use $_REQUEST if you prefer)
    $email      = isset($_POST['email'])      ? sanitize_email($_POST['email']) : '';
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name  = isset($_POST['last_name'])  ? sanitize_text_field($_POST['last_name'])  : '';
    $password   = isset($_POST['password'])   ? sanitize_text_field($_POST['password'])   : '';
    $phone      = isset($_POST['phone'])      ? sanitize_text_field($_POST['phone'])      : '';

    // Basic checks
    if ( ! $email || ! $first_name || ! $last_name || ! $password ) {
        wp_send_json_error('Missing required fields.');
    }

    // Check if user exists
    if ( email_exists($email) ) {
        wp_send_json_error("A user with email {$email} already exists.");
    }

    // Attempt user creation
    $user_id = wp_create_user( $email, $password, $email );
    if ( is_wp_error($user_id) ) {
        wp_send_json_error('Failed to create user: ' . $user_id->get_error_message());
    }

    // Assign role
    $user = new WP_User($user_id);
    $user->set_role('artisan');

    // Save first name, last name, phone
    wp_update_user( array(
        'ID'         => $user_id,
        'first_name' => $first_name,
        'last_name'  => $last_name,
    ));
    update_user_meta($user_id, 'phone', $phone);

    wp_send_json_success('User created successfully.');
}
