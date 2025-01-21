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

/**
 * Shortcode to Render a Multi-Step Form for Service Forms
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
        <!-- Form Title -->
        <h2><?php echo esc_html(get_the_title($post_id)); ?></h2>

        <!-- Form Description -->
        <p><?php echo esc_html($form_description); ?></p>

        <!-- Multi-Step Form -->
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
                            $field_options = json_decode($field->field_options, true) ?? [];
                            $is_required = (int)$field->is_required;
                            ?>
                            <!-- Each Step -->
                            <div class="form-step" data-step="<?php echo $index + 1; ?>">
                                <div class="form-group">
                                    <label for="<?php echo $field_id; ?>">
                                        <?php echo $field_label; ?><?php echo $is_required ? ' *' : ''; ?>
                                    </label>
                                    <?php if ($field_description): ?>
                                        <p class="field-description"><?php echo $field_description; ?></p>
                                    <?php endif; ?>

                                    <?php
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
                                <!-- Navigation Buttons -->
                                <div class="step-navigation">
                                    <?php if ($index > 0): ?>
                                        <button type="button" class="prev-step">Back</button>
                                    <?php endif; ?>
                                    <?php if ($index < count($fields) - 1): ?>
                                        <button type="button" class="next-step">Further</button>
                                    <?php else: ?>
                                        <button type="submit" class="submit-button">Submit</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No fields found for this form.</p>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Include JavaScript for Multi-Step Form -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const steps = document.querySelectorAll('.form-step');
            const nextButtons = document.querySelectorAll('.next-step');
            const prevButtons = document.querySelectorAll('.prev-step');

            let currentStep = 0;

            function showStep(step) {
                steps.forEach((s, index) => {
                    s.style.display = index === step ? 'block' : 'none';
                });
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            nextButtons.forEach((button, index) => {
                button.addEventListener('click', function () {
                    if (index < steps.length - 1) {
                        currentStep++;
                        showStep(currentStep);
                    }
                });
            });

            prevButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    if (currentStep > 0) {
                        currentStep--;
                        showStep(currentStep);
                    }
                });
            });

            // Show the first step initially
            showStep(currentStep);
        });
    </script>

    <style>
        .form-step {
            display: none;
            animation-duration: 0.5s;
            animation-timing-function: ease-in-out;
        }

        .form-step[data-step="1"] {
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .step-navigation {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .step-navigation .prev-step,
        .step-navigation .next-step,
        .step-navigation .submit-button {
            padding: 10px 20px;
            border: none;
            background: #007bff;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }

        .step-navigation .prev-step:hover,
        .step-navigation .next-step:hover,
        .step-navigation .submit-button:hover {
            background: #0056b3;
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('service_form', 'render_service_form_shortcode');
