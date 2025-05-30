<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Add Meta Box for Service Job Details
 */
function add_service_job_meta_box() {
    add_meta_box(
        'service_job_details_meta_box',
        __('Service Job Details', 'textdomain'),
        'render_service_job_meta_box',
        'service_job',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_service_job_meta_box');

/**
 * Render the Service Job Meta Box
 */
function render_service_job_meta_box($post) {
    // Use nonce for verification
    wp_nonce_field('service_job_meta_box_nonce', 'service_job_meta_box_nonce_field');

    // Retrieve existing values
    $job_zip_code = get_post_meta($post->ID, 'job_zip_code', true);
    $latitude = get_post_meta($post->ID, 'latitude', true);
    $longitude = get_post_meta($post->ID, 'longitude', true);
    $city = get_post_meta($post->ID, 'city', true);
    $client_name = get_post_meta($post->ID, 'client_name', true);
    $client_email = get_post_meta($post->ID, 'client_email', true);
    $client_phone = get_post_meta($post->ID, 'client_phone', true);
    $job_details_json = get_post_meta($post->ID, 'job_details_json', true);
    $submitted_at = get_post_meta($post->ID, 'submitted_at', true);

    // Convert JSON to an array for display
    $job_questions = !empty($job_details_json) ? json_decode($job_details_json, true) : [];

    ?>
    <div class="service-job-meta-container">
        <h3>Client Details</h3>
        <label><strong>Name:</strong></label>
        <input type="text" name="client_name" value="<?php echo esc_attr($client_name); ?>" class="widefat">

        <label><strong>Email:</strong></label>
        <input type="email" name="client_email" value="<?php echo esc_attr($client_email); ?>" class="widefat">

        <label><strong>Phone:</strong></label>
        <input type="text" name="client_phone" value="<?php echo esc_attr($client_phone); ?>" class="widefat">

        <h3>Job Details</h3>
        <label><strong>Zip Code:</strong></label>
        <input type="text" name="job_zip_code" value="<?php echo esc_attr($job_zip_code); ?>" class="widefat">

        <label><strong>City:</strong></label>
        <input type="text" name="city" value="<?php echo esc_attr($city); ?>" class="widefat">

        <label><strong>Latitude:</strong></label>
        <input type="text" name="latitude" value="<?php echo esc_attr($latitude); ?>" class="widefat">

        <label><strong>Longitude:</strong></label>
        <input type="text" name="longitude" value="<?php echo esc_attr($longitude); ?>" class="widefat">

        <label><strong>Submitted At:</strong></label>
        <input type="text" value="<?php echo esc_attr($submitted_at); ?>" class="widefat" readonly>

        <h3>Job Questions & Answers</h3>
        <?php if (!empty($job_questions)) : ?>
            <table class="form-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 8px;">Question</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Answer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($job_questions as $question) : ?>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo esc_html($question['question']); ?></td>
                            <td style="border: 1px solid #ddd; padding: 8px;">
                                <?php
                                    if (filter_var($question['answer'], FILTER_VALIDATE_URL)) {
                                        echo '<a href="' . esc_url($question['answer']) . '" target="_blank" class="button">View File</a>';
                                    } else {
                                        echo esc_html($question['answer']);
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No job questions added.</p>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Save Service Job Meta Box Data
 */
function save_service_job_meta_box_data($post_id) {
    // Verify nonce
    if (!isset($_POST['service_job_meta_box_nonce_field']) ||
        !wp_verify_nonce($_POST['service_job_meta_box_nonce_field'], 'service_job_meta_box_nonce')) {
        return;
    }

    // Check for autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) return;

    // Save client details
    update_post_meta($post_id, 'client_name', sanitize_text_field($_POST['client_name']));
    update_post_meta($post_id, 'client_email', sanitize_email($_POST['client_email']));
    update_post_meta($post_id, 'client_phone', sanitize_text_field($_POST['client_phone']));

    // Save job details
    update_post_meta($post_id, 'job_zip_code', sanitize_text_field($_POST['job_zip_code']));
    update_post_meta($post_id, 'city', sanitize_text_field($_POST['city']));
    update_post_meta($post_id, 'latitude', sanitize_text_field($_POST['latitude']));
    update_post_meta($post_id, 'longitude', sanitize_text_field($_POST['longitude']));
}
add_action('save_post', 'save_service_job_meta_box_data');