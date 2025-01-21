<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

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

/**
 * Shortcode to Render Form Fields Dynamically for Service Forms
 */
function render_service_form_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts([
        'post_id' => null,
    ], $atts);

    // Get the current post ID if not provided
    $post_id = $atts['post_id'] ? intval($atts['post_id']) : get_the_ID();

    // Ensure the post is of type 'service_form'
    if (get_post_type($post_id) !== 'service_form') {
        return '<p>This form is not available for the current post.</p>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'form_fields';

    // Fetch fields dynamically from the database
    $fields = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE service_form_id = %d ORDER BY field_order ASC",
        $post_id
    ));

    // Get the form description
    $form_description = get_post_meta($post_id, '_form_description', true);

    // Begin output buffering
    ob_start();
    ?>
    <div class="service-form">
        <!-- Post title -->
        <h2><?php echo esc_html(get_the_title($post_id)); ?></h2>

        <!-- Form description -->
        <p><?php echo esc_html($form_description); ?></p>

        <!-- Dynamic form fields -->
        <form action="" method="post">
            <?php if (!empty($fields)): ?>
                <?php foreach ($fields as $field): ?>
                    <?php
                    $field_id = esc_attr($field->field_external_id);
                    $field_label = esc_html($field->field_label);
                    $field_description = esc_html($field->field_description);
                    $field_type = $field->field_type;
                    $field_options = json_decode($field->field_options, true) ?? [];
                    $is_required = (int)$field->is_required;

                    // Render field label
                    echo '<div class="form-group">';
                    echo '<label for="' . $field_id . '">' . $field_label . ($is_required ? ' *' : '') . '</label>';
                    if ($field_description) {
                        echo '<p class="field-description">' . $field_description . '</p>';
                    }

                    // Render fields based on type
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
                            } else {
                                render_checkbox_field(
                                    $field_id,
                                    $field_id,
                                    $field_label,
                                    false,
                                    $is_required
                                );
                            }
                            break;

                        case 'checkbox_with_image':
                            if (!empty($field_options['options_list'])) {
                                foreach ($field_options['options_list'] as $option) {
                                    ?>
                                    <div class="checkbox-with-image">
                                        <?php if (!empty($option['image'])): ?>
                                            <img src="<?php echo esc_url($option['image']); ?>" alt="<?php echo esc_attr($option['label']); ?>" style="max-width: 100px; max-height: 100px;">
                                        <?php endif; ?>
                                        <label>
                                            <input type="checkbox" name="<?php echo esc_attr($field_id); ?>[]" value="<?php echo esc_attr($option['label']); ?>">
                                            <?php echo esc_html($option['label']); ?>
                                        </label>
                                    </div>
                                    <?php
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
                    echo '</div>';
                    ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No fields found for this form.</p>
            <?php endif; ?>

            <!-- Submit button -->
            <button type="submit" class="submit-button">Submit</button>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('service_form', 'render_service_form_shortcode');
