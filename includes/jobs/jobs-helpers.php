<?php


function create_service_job($user_details, $form_fields, $form_type) {
    // Validate required fields
    $user_lat = isset($user_details['zip_code_lat']) ? floatval($user_details['zip_code_lat']) : null;
    $user_lng = isset($user_details['zip_code_lng']) ? floatval($user_details['zip_code_lng']) : null;
    
    if (empty($user_details['name']) || empty($user_details['email']) || empty($user_details['phone']) || 
        empty($user_details['zip_code']) || empty($form_type) || empty($user_lat) || 
        empty($user_lng) || empty($user_details['zipPlace'])) {
        
        error_log("Missing required user details or form type.");
        return new WP_Error('missing_data', __('Required user details or form type are missing.', 'textdomain'));
    }

    // Prepare job post data
    $job_title = 'New Job: ' . esc_html($form_type) . ' (' . esc_html($user_details['zip_code']) . ')';
    
    $job_data = array(
        'post_title'    => $job_title,
        'post_content'  => '', // Can be used for additional description if needed
        'post_status'   => 'publish', // Change to 'draft' if admin approval is required
        'post_type'     => 'service_job',
        'tax_input'     => array('global_services' => array($form_type)), // Assign taxonomy
    );

    // Insert job post
    $job_id = wp_insert_post($job_data);

    // If insertion failed
    if (is_wp_error($job_id)) {
        error_log("Failed to create service job: " . $job_id->get_error_message());
        return $job_id;
    }

    // Store meta fields
    update_post_meta($job_id, 'client_name', sanitize_text_field($user_details['name']));
    update_post_meta($job_id, 'client_email', sanitize_email($user_details['email']));
    update_post_meta($job_id, 'client_phone', sanitize_text_field($user_details['phone']));
    update_post_meta($job_id, 'job_zip_code', sanitize_text_field($user_details['zip_code']));
    error_log("befoe asvnig formd filed::::::::::" .  print_r($form_fields,true));
    error_log("ater endcoide asvnig formd filed::::::::::" .  print_r( wp_json_encode($form_fields),true));
    $form_fields_json = json_encode($form_fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    error_log("gpt workd::::::::::" .  print_r( wp_json_encode($form_fields),true));

    update_post_meta($job_id, 'job_details_json', wp_json_encode($form_fields)); // Store Q&A as JSON
    update_post_meta($job_id, 'latitude', sanitize_text_field($user_lat));
    update_post_meta($job_id, 'longitude', sanitize_text_field($user_lng));
    update_post_meta($job_id, 'city', sanitize_text_field($user_details['zipPlace']));
    update_post_meta($job_id, 'submitted_at', current_time('mysql'));

    // Attach taxonomy term
    wp_set_object_terms($job_id, $form_type, 'global_services');

    // Log success
    error_log("Service job created successfully with ID: $job_id");

    return $job_id;
}



/**
 * Log in a user programmatically.
 *
 * @param int $user_id User ID to log in.
 * @return void
 */
function kz_login_user($user_id) {
    if (!is_wp_error($user_id) && $user_id > 0) {
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
    }
}