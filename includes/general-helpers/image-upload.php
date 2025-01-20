<?php
/**
 * Handles image uploads via AJAX.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_action( 'wp_ajax_upload_business_license', 'kazverse_upload_business_license' );
add_action( 'wp_ajax_nopriv_upload_business_license', 'kazverse_upload_business_license' );

function kazverse_upload_business_license() {
    // Check nonce for security
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'kazverse_upload_nonce' ) ) {
        wp_send_json_error( 'Invalid nonce.' );
    }

    if ( empty( $_FILES['business_license'] ) ) {
        wp_send_json_error( 'No file uploaded.' );
    }

    $file = $_FILES['business_license'];

    // Validate file type
    $allowed_types = ['image/png', 'image/jpeg', 'application/pdf'];
    if ( ! in_array( $file['type'], $allowed_types ) ) {
        wp_send_json_error( 'Invalid file type. Only PNG, JPG, and PDF are allowed.' );
    }

    // Validate file size (15 MB)
    if ( $file['size'] > 15 * 1024 * 1024 ) {
        wp_send_json_error( 'File size exceeds 15 MB.' );
    }

    // Handle the upload
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    $upload_overrides = ['test_form' => false];
    $movefile = wp_handle_upload( $file, $upload_overrides );

    if ( isset( $movefile['error'] ) ) {
        wp_send_json_error( $movefile['error'] );
    }

    // Create attachment
    $attachment = [
        'guid'           => $movefile['url'],
        'post_mime_type' => $movefile['type'],
        'post_title'     => basename( $movefile['file'] ),
        'post_content'   => '',
        'post_status'    => 'inherit'
    ];

    $attach_id = wp_insert_attachment( $attachment, $movefile['file'] );
    if ( is_wp_error( $attach_id ) ) {
        wp_send_json_error( 'Failed to create attachment.' );
    }

    // Generate metadata
    $attach_data = wp_generate_attachment_metadata( $attach_id, $movefile['file'] );
    wp_update_attachment_metadata( $attach_id, $attach_data );

    wp_send_json_success( [
        'attachment_id' => $attach_id,
        'url'           => $movefile['url']
    ] );
}
