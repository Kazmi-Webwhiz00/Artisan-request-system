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
                                <div class="form-group mb-5">
                                    <label for="zip_code">Zip code of your order*</label>
                                    <?php render_text_field('zip_code', 'zip_code', '', 'Enter your zip code', '', true); ?>
                                </div>
                            </div>

                            <!-- Email, Name, and Phone Fields (Last Step) -->
                            <div class="form-step" data-step="<?php echo count($fields) + 2; ?>">
                                <div class="form-group mb-5">
                                    <label for="email">Get an answer from tradesmen in your area.</label>
                                    <br>
                                    <span class="mb-4">Your data will only be visible to tradesmen once you contact them.</span>
                                    <?php render_email_field('email', 'email', '', 'Enter your email address', '', true); ?>
                                </div>
                                <div class="form-group mb-5">
                                    <label for="name">Your Name*</label>
                                    <?php render_text_field('name', 'name', '', 'Enter your name', '', true); ?>
                                </div>
                                <div class="form-group mb-5">
                                    <label for="phone">Phone Number*</label>
                                    <?php render_phone_field('phone', 'phone', '', 'Enter your phone number', '+49', true); ?>
                                </div>
                            </div>

                            <!-- Navigation Buttons -->
                            <div class="kz-step-navigation">
                                <button type="button" class="prev-step" disabled>Back</button>
                                <button type="button" class="next-step">Next</button>
                                <button type="submit" class="submit-button" style="display: none;">Submit</button>
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
