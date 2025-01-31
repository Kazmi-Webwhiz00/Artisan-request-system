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
    $email      = isset($_POST['email'])      ? sanitize_email($_POST['email']) : '';
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name  = isset($_POST['last_name'])  ? sanitize_text_field($_POST['last_name'])  : '';
    $password   = isset($_POST['password'])   ? sanitize_text_field($_POST['password'])   : '';
    $phone      = isset($_POST['phone'])      ? sanitize_text_field($_POST['phone'])      : '';

    // Validate required fields
    if (!validate_user_fields($email, $first_name, $last_name, $password)) {
        wp_send_json_error('Missing required fields.');
    }

    // Check if user exists
    if (email_exists($email)) {
        wp_send_json_error("A user with email {$email} already exists.");
    }

    // Create user
    $user_id = create_new_user($email, $password, $first_name, $last_name, $phone);
    if (is_wp_error($user_id)) {
        wp_send_json_error('Failed to create user: ' . $user_id->get_error_message());
    }

    // Send email to the user
    send_user_email($email, $first_name, $last_name, $password);


    wp_send_json_success('User created successfully, and email sent.');
}

/**
 * Validate required user fields
 */
function validate_user_fields($email, $first_name, $last_name, $password) {
    return !empty($email) && !empty($first_name) && !empty($last_name) && !empty($password);
}

/**
 * Create a new user and assign the 'artisan' role
 */
function create_new_user($email, $password, $first_name, $last_name, $phone) {
    $user_id = wp_create_user($email, $password, $email);

    if (!is_wp_error($user_id)) {
        $user = new WP_User($user_id);
        $user->set_role('artisan');

        // Update user meta data
        wp_update_user(array(
            'ID'         => $user_id,
            'first_name' => $first_name,
            'last_name'  => $last_name,
        ));
        update_user_meta($user_id, 'phone', $phone);
    }

    return $user_id;
}

/**
 * Send login details email to the user
 */
function send_user_email($email, $first_name, $last_name, $password) {
    $login_url = wp_login_url();
    $subject = "Your Artisan Account Details";
    $message = "Hello {$first_name} {$last_name},\n\n";
    $message .= "Your artisan account has been successfully created.\n\n";
    $message .= "Here are your login details:\n";
    $message .= "Username: {$email}\n";
    $message .= "Password: {$password}\n\n";
    $message .= "You can log in here: {$login_url}\n\n";

    // Email headers
    $headers = array('Content-Type: text/plain; charset=UTF-8');

    // Send the email
    wp_mail($email, $subject, $message, $headers);
}
