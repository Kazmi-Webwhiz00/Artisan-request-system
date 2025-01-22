<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Include form helpers
require_once plugin_dir_path(__FILE__) . '../../../general-helpers/forms/text-field.php';
require_once plugin_dir_path(__FILE__) . '../../../general-helpers/forms/select-field.php';
require_once plugin_dir_path(__FILE__) . '../../../general-helpers/forms/email-field.php';
require_once plugin_dir_path(__FILE__) . '../../../general-helpers/forms/phone-field.php';
require_once plugin_dir_path(__FILE__) . '../../../general-helpers/forms/password-field.php';
require_once plugin_dir_path(__FILE__) . '../../../general-helpers/forms/checkbox-field.php';
require_once plugin_dir_path(__FILE__) . '../../../general-helpers/forms/radio-field.php';
require_once plugin_dir_path(__FILE__) . '../../../general-helpers/forms/file-upload-field.php';
require_once plugin_dir_path(__FILE__) . '../../../general-helpers/forms/number-field.php';
require_once plugin_dir_path(__FILE__) . '../../../general-helpers/forms/textarea-field.php';
require_once plugin_dir_path(__FILE__) . '../../../general-helpers/forms/checkbox-with-image.php';


function enqueue_service_form_assets() {
    if (is_singular('service_form')) {
        // Enqueue CSS
        wp_enqueue_style('frontend-form-css', plugin_dir_url(__FILE__) . 'assets/css/frontend-form.css');

        // Enqueue jQuery and JS
        wp_enqueue_script('frontend-form-js', plugin_dir_url(__FILE__) . 'assets/js/frontend-form.js', ['jquery'], false, true);

        // Add custom AJAX script
         wp_localize_script('frontend-form-js', 'ajax_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_service_form_assets');


function render_service_form_shortcode($atts) {
    $atts = shortcode_atts([
        'post_id' => null,
    ], $atts);

    $post_id = $atts['post_id'] ? intval($atts['post_id']) : get_the_ID();

    if (get_post_type($post_id) !== 'service_form') {
        return '<p>This form is not available for the current post.</p>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'form_fields';

    $fields = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE service_form_id = %d ORDER BY field_order ASC",
        $post_id
    ));

    $form_description = get_post_meta($post_id, '_form_description', true);

    $form_name = get_the_title($post_id); // Form name

    ob_start();
    ?>
    <div class="service-form">

        <!-- Form Name in Small Text -->
        <p class="form-name"><?php echo esc_html($form_name); ?></p>
        <hr class="form-divider">

        <div class="kz-form-wrapper">
            <!-- Main Form Title -->
            <h1 class="form-main-title">Describe your job and contact craftsmen in your area</h1>

            <!-- Form Description -->
            <p><?php echo esc_html($form_description); ?></p>

            <div class="multi-step-form">
                <form action="" method="post" id="dynamic-multi-step-form">
                    <div class="steps-container">
                        <?php if (!empty($fields)): ?>
                            <?php foreach ($fields as $index => $field): ?>
                                <?php
                                $field_id = esc_attr($field->field_external_id);
                                $field_label = esc_html($field->field_label);
                                $field_description = esc_html($field->field_description);
                                $field_type = $field->field_type;
                                $cleaned_options = stripslashes($field->field_options ?? '');
                                $field_options = json_decode($cleaned_options, true);
                                $is_required = (int)$field->is_required;
                                ?>
                                <div class="form-step" data-step="<?php echo $index + 1; ?>">
                                    <div class="form-group">
                                        <label for="<?php echo $field_id; ?>">
                                            <?php echo $field_label; ?>
                                            <?php echo $is_required ? ' *' : ' (optional)'; ?>
                                        </label>
                                        <?php if ($field_description): ?>
                                            <p class="field-description"><?php echo $field_description; ?></p>
                                        <?php endif; ?>

                                        <div class="field-wrapper field-<?php echo $field_type; ?>-wrapper">
                                            <?php
                                            switch ($field_type) {
                                                case 'text_input':
                                                    render_text_field(
                                                        $field_id,
                                                        $field_id,
                                                        '',
                                                        $field_options['placeholder'] ?? '',
                                                        '',
                                                        $is_required
                                                    );
                                                    break;
                                                case 'email':
                                                    render_email_field(
                                                        $field_id,
                                                        $field_id,
                                                        '',
                                                        $field_options['placeholder'] ?? '',
                                                        '',
                                                        $is_required
                                                    );
                                                    break;
                                                case 'number_input':
                                                    render_number_field(
                                                        $field_id,
                                                        $field_id,
                                                        '',
                                                        $field_options['placeholder'] ?? '',
                                                        '',
                                                        $is_required
                                                    );
                                                    break;
                                                case 'radio':
                                                    if (!empty($field_options['options_list'])) {
                                                        foreach ($field_options['options_list'] as $option) {
                                                            render_radio_button_field(
                                                                $field_id,
                                                                $field_id . '_' . sanitize_title($option['label']),
                                                                $option['label'] ?? '',
                                                                false,
                                                                $is_required
                                                            );
                                                        }
                                                    }
                                                    break;
                                                case 'checkbox_simple':
                                                    if (!empty($field_options['options_list'])) {
                                                        foreach ($field_options['options_list'] as $option) {
                                                            render_checkbox_field(
                                                                $field_id . '_' . sanitize_title($option['label']),
                                                                $field_id,
                                                                $option['label'] ?? '',
                                                                false,
                                                                $is_required
                                                            );
                                                        }
                                                    }
                                                    break;
                                                case 'checkbox_with_image':
                                                    if (!empty($field_options['options_list'])) {
                                                        foreach ($field_options['options_list'] as $option) {
                                                            $image_url = wp_get_attachment_url($option['imageId']);
                                                            render_dynamic_checkbox_field(
                                                                $field_id . '_' . sanitize_title($option['label']),
                                                                $field_id,
                                                                $option['label'] ?? '',
                                                                $image_url,
                                                                false
                                                            );
                                                        }
                                                    }
                                                    break;
                                                case 'textarea':
                                                    render_textarea_field(
                                                        $field_id,
                                                        $field_id,
                                                        '',
                                                        $field_options['placeholder'] ?? '',
                                                        '',
                                                        $is_required
                                                    );
                                                    break;
                                                default:
                                                    echo '<p>Unsupported field type: ' . esc_html($field_type) . '</p>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Zip Code Field (Second Last Step) -->
                            <div class="form-step" data-step="<?php echo count($fields) + 1; ?>">
                                <div class="form-group">
                                    <label for="zip_code">Zip code of your order*</label>
                                    <?php render_text_field('zip_code', 'zip_code', '', 'Enter your zip code', '', true); ?>
                                </div>
                            </div>

                            <!-- Email, Name, and Phone Fields (Last Step) -->
                            <div class="form-step" data-step="<?php echo count($fields) + 2; ?>">
                                <div class="form-group">
                                    <label for="email">Get an answer from tradesmen in your area.</label>
                                    <span>Your data will only be visible to tradesmen once you contact them.</span>
                                    <?php render_email_field('email', 'email', '', 'Enter your email address', '', true); ?>
                                </div>
                                <div class="form-group">
                                    <label for="name">Your Name*</label>
                                    <?php render_text_field('name', 'name', '', 'Enter your name', '', true); ?>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number*</label>
                                    <?php render_phone_field('phone', 'phone', '', 'Enter your phone number', '', true); ?>
                                </div>
                            </div>

                            <!-- Navigation Buttons -->
                            <div class="kz-step-navigation">
                                <button type="button" class="prev-step" disabled>Back</button>
                                <button type="button" class="next-step">Next</button>
                                <button type="submit" id="form-submit-button" style="display: none;">Submit</button>
                            </div>
                        <?php else: ?>
                            <p>No fields found for this form.</p>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('service_form', 'render_service_form_shortcode');

// AJAX handler for form submission
add_action('wp_ajax_submit_service_form', 'handle_service_form_submission');
add_action('wp_ajax_nopriv_submit_service_form', 'handle_service_form_submission');

function handle_service_form_submission() {
    if (!isset($_POST['form_data']) || empty($_POST['form_data'])) {
        wp_send_json_error(['message' => 'No data received.']);
    }

    // Decode JSON-formatted form data
    $form_data = json_decode(stripslashes($_POST['form_data']), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(['message' => 'Invalid JSON data received.']);
    }

    // Extract data from form_data
    $form_name = $form_data['form_name'] ?? 'Unknown Form';
    $form_fields = $form_data['form_fields'] ?? [];
    $user_details = $form_data['user_details'] ?? [];
    $user_name = $user_details['name'] ?? 'N/A';
    $user_email = $user_details['email'] ?? 'N/A';
    $user_phone = $user_details['phone'] ?? 'N/A';
    $zip_code = $user_details['zip_code'] ?? 'N/A';

    // Create email body
    ob_start();
    ?>
    <h2>New Job Requested in Your Area for <?php echo esc_html($form_name); ?></h2>
    <p>A new job has been posted in the area with postal code <strong><?php echo esc_html($zip_code); ?></strong> for <strong><?php echo esc_html($form_name); ?></strong> that matches your profile.</p>
    
    <h3>Client Details</h3>
    <p><strong>Name:</strong> <?php echo esc_html($user_name); ?></p>
    <p><strong>Email:</strong> <?php echo esc_html($user_email); ?></p>
    <p><strong>Phone:</strong> <?php echo esc_html($user_phone); ?></p>

    <h3>Job Details</h3>
    <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th style="border: 1px solid #ccc; padding: 8px;">Question</th>
                <th style="border: 1px solid #ccc; padding: 8px;">Answer</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($form_fields as $field): ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 8px;"><?php echo esc_html($field['question']); ?></td>
                    <td style="border: 1px solid #ccc; padding: 8px;"><?php echo esc_html($field['answer']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    $email_body = ob_get_clean();

    // Email settings
    $to = 'admin@artisan.com';
    $subject = 'New Job Request in Your Area for ' . $form_name;
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    // Send email
    $sent = wp_mail($to, $subject, $email_body, $headers);

    if ($sent) {
        wp_send_json_success(['message' => 'Form submitted successfully.']);
    } else {
        wp_send_json_error(['message' => 'Failed to send email.']);
    }
}
?>
