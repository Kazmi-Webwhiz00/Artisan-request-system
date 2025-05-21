<?php
/**
 * Plugin Name: Kazverse Artisan Form Handler
 * Description: Handles submission from the multi-step artisan registration form and creates/updates the Artisan CPT.
 */

// Prevent direct access
if ( ! defined('ABSPATH') ) {
    exit;
}

/**
 * Hook for logged-out users (nopriv) and logged-in users.
 * The "action" matches the hidden input "action" in the form: kazverse_artisan_submit
 */
add_action('admin_post_nopriv_kazverse_artisan_submit', 'kazverse_artisan_process_form');
add_action('admin_post_kazverse_artisan_submit',        'kazverse_artisan_process_form');

/**
 * Processes the multi-step form data and creates/updates an Artisan CPT entry.
 */
function kazverse_artisan_process_form() {
    // 1. Security check (nonce)
    if ( ! isset($_POST['kazverse_artisan_nonce']) || 
         ! wp_verify_nonce($_POST['kazverse_artisan_nonce'], 'kazverse_artisan_submit_action') ) {
        wp_die( __('Security check failed. Please try again.', 'textdomain') );
    }

    // 2. Make sure we got the JSON data
    if ( empty($_POST['kazverse_data']) ) {
        wp_die( __('No form data received.', 'textdomain') );
    }

    // 3. Parse the JSON
    $data = json_decode( stripslashes($_POST['kazverse_data']), true );
    if ( ! is_array($data) ) {
        wp_die( __('Invalid form data.', 'textdomain') );
    }

    // 4. Decide what to use as the "post_title":
    //    We can use "company_name" from step8 or "first_name + last_name" from step2, etc.
    $post_title = 'New Artisan';
    if ( ! empty($data['step8']['company_name']) ) {
        $post_title = sanitize_text_field($data['step8']['company_name']);
    } elseif ( ! empty($data['step2']['first_name']) || ! empty($data['step2']['last_name']) ) {
        $full_name = trim( $data['step2']['first_name'] . ' ' . $data['step2']['last_name'] );
        if ( $full_name ) {
            $post_title = sanitize_text_field($full_name);
        }
    }

    $user_id = isset($data['step2']['userId']) && is_numeric($data['step2']['userId']) ? intval($data['step2']['userId']) : null;

    if (empty($user_id) || $user_id <= 0) {
        wp_send_json_error(['message' => 'Invalid or missing user ID.']);
    }
    
    // 5. Insert a new Artisan post (status: "publish" or "pending" as you wish)
    $post_id = wp_insert_post(array(
        'post_type'   => 'artisan',
        'post_title'  => $post_title,
        'post_author'  => $user_id,
        'post_status' => 'publish',  // or 'pending'
    ));

    if ( ! is_wp_error($post_id) ) {
        // Set the default status to "pending" on first creation
        update_post_meta( $post_id, 'artisan_status', 'pending' );
    } else {
        wp_die( __('Failed to create Artisan post.', 'textdomain') );
    }

    // ============ Store Meta Fields ============
    // We'll define a helper function to store meta safely

    // (A) Basic usage: store_artisan_meta($post_id, $key, $value, $sanitize_callback)
    function store_artisan_meta($post_id, $key, $value, $sanitize_callback = 'sanitize_text_field') {
        if ( ! is_null($value) ) {
            // Call the provided sanitization callback
            $clean_value = call_user_func($sanitize_callback, $value);
            update_post_meta($post_id, $key, $clean_value);
        }
    }

    // ========== Step 1 ==========
    if ( isset($data['step1']['trade']) )    store_artisan_meta($post_id, 'trade', $data['step1']['trade']);
    if ( isset($data['step1']['zip_code']) ) store_artisan_meta($post_id, 'zip_code', $data['step1']['zip_code']);
    if ( isset($data['step1']['email']) )    store_artisan_meta($post_id, 'email', $data['step1']['email'], 'sanitize_email');

    // ========== Step 2 ==========
    if ( isset($data['step2']['first_name']) ) store_artisan_meta($post_id, 'first_name', $data['step2']['first_name']);
    if ( isset($data['step2']['last_name']) )  store_artisan_meta($post_id, 'last_name',  $data['step2']['last_name']);
    if ( isset($data['step2']['phone']) )      store_artisan_meta($post_id, 'phone',      $data['step2']['phone']);
    // Password is excluded from CPT
    if ( isset($data['step2']['subscribe']) )  store_artisan_meta($post_id, 'subscribe',  $data['step2']['subscribe'], 'boolval');

    // ========== Step 4 (array) ==========
    if ( isset($data['step4']['selected_trades']) && is_array($data['step4']['selected_trades']) ) {
        // Sanitize the selected trades
        $sanitized_array = array_map('sanitize_text_field', $data['step4']['selected_trades']);

        // Update post meta
        $serialized = maybe_serialize($sanitized_array);
        update_post_meta($post_id, 'selected_trades', $serialized);

        // Assign selected trades to the post in the 'global_services' taxonomy
        $term_ids = [];
        foreach ($sanitized_array as $trade_name) {
            $term = get_term_by('name', $trade_name, 'global_services');
            if ($term && !is_wp_error($term)) {
                $term_ids[] = (int) $term->term_id;
            }
        }

        // Update the taxonomy terms for the post
        if (!empty($term_ids)) {
            wp_set_object_terms($post_id, $term_ids, 'global_services');
        }
    }

    // ========== Step 5 ==========
    if ( isset($data['step5']['distance']) ) {
        store_artisan_meta($post_id, 'distance', $data['step5']['distance'], 'intval');
    }

    if ( isset($data['step5']['work_throughout_netherlands']) ) {
        store_artisan_meta($post_id, 'work_throughout_netherlands', $data['step5']['work_throughout_netherlands'], 'boolval');
    }

    if ( isset($data['step5']['latitude']) ) {
        store_artisan_meta($post_id, 'latitude', $data['step5']['latitude'], 'floatval');
    }
    
    if ( isset($data['step5']['longitude']) ) {
        store_artisan_meta($post_id, 'longitude', $data['step5']['longitude'], 'floatval');
    }
    
    // ========== Step 6 ==========
    if ( isset($data['step6']['professional_status']) ) {
        store_artisan_meta($post_id, 'professional_status', $data['step6']['professional_status']);
    }

    // ========== Step 8 ==========
    if ( isset($data['step8']['gisa_number']) ) {
        store_artisan_meta($post_id, 'gisa_number', $data['step8']['gisa_number']);
    }
    if ( isset($data['step8']['company_name']) ) {
        store_artisan_meta($post_id, 'company_name', $data['step8']['company_name']);
    }
    if ( isset($data['step8']['address']) ) {
        store_artisan_meta($post_id, 'address', $data['step8']['address']);
    }
    if ( isset($data['step8']['zip_code']) ) {
        store_artisan_meta($post_id, 'business_zip_code', $data['step8']['zip_code']);
    }
    if ( isset($data['step8']['city']) ) {
        store_artisan_meta($post_id, 'city', $data['step8']['city']);
    }

    // ========== Step 9 ==========
    if ( isset($data['step9']['business_license_url']) ) {
        store_artisan_meta($post_id, 'business_license_url', esc_url_raw($data['step9']['business_license_url']));
    }

    // ========== Step 11 ==========
    if ( isset($data['step11']['description']) ) {
        store_artisan_meta($post_id, 'description', $data['step11']['description'], 'sanitize_textarea_field');
    }


    // wp_set_current_user($user_id);
    // wp_set_auth_cookie($user_id);
    // do_action('wp_login', get_userdata($user_id)->user_login, get_userdata($user_id));

    // 6. All done! Redirect to a thank-you page or show success
    // wp_redirect( home_url('/job-listing-page/') );

        // 6. All done! Redirect to a thank-you page or show success
    // if ( is_admin() ) {
    //     return;
    // }

    // Get the custom slug
    if ( ! empty( $_POST['redirect-url'] ) ) {

        // 1) Retrieve & sanitize the submitted URL
        $redirect_url = sanitize_text_field( wp_unslash( $_POST['redirect-url'] ) );
        error_log( 'Submitted Redirect URL: ' . $redirect_url );

        // 2) Get your custom slug (falling back to 'jobs-listing')
        $custom_slug = get_option( 'artisan_custom_redirect_url', 'job-listing-page' );
        error_log( 'Custom Slug: ' . $custom_slug );


        // 3) Extract just the path from the full URL
        $path = parse_url( $redirect_url, PHP_URL_PATH );
        $path = trim( $path, '/' );
        error_log( 'Path: ' . $path );

        // 4) Split into segments and grab the first one
        $segments = explode( '/', $path );
        $subdir   = $segments[0] ?? '';
        error_log( 'Subdirectory: ' . $subdir . ' | Segments: ' . print_r( $segments, true ) );

        // 5) If it's not wp-admin, redirect to /{subdir}/{custom_slug}/
        if ( $subdir || 'wp-admin' !== $subdir ) {
            $target_url = home_url( "/{$custom_slug}/" );
            error_log( 'Redirecting to: ' . $target_url );
            wp_redirect( $target_url );
            exit;
        }
    }
}
