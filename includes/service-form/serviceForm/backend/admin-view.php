<?php
// admin-view.php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Add custom meta box to post type
function add_custom_meta_box() {
    add_meta_box(
        'custom_meta_box',
        __('Form Fields', 'textdomain'),
        'render_custom_meta_box',
        'service_form',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_custom_meta_box');

function render_custom_meta_box($post) {
    global $wpdb;

    // Get existing fields for this post
    $table_name = $wpdb->prefix . 'form_fields';
    $fields = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE service_form_id = %d ORDER BY field_order ASC",
        $post->ID
    ));

    // Generate nonce field for security
    wp_nonce_field('save_custom_meta_box', 'kz_custom_meta_box_nonce');
    ?>
    <div>
        <span id="kz-add-new-field">Add New Field</span>
        <div id="kz-fields-container">
            <?php if (!empty($fields)): ?>
                <?php foreach ($fields as $field): ?>
                    <?php 
                    $field_id = esc_attr($field->field_external_id);
                    $field_label = esc_attr($field->field_label);
                    $field_type = esc_attr($field->field_type);
                    $is_required = (int) $field->is_required;
                    $field_order = (int) $field->field_order;
                    $field_options = json_decode($field->field_options, true) ?? [];
                    ?>
                    <div class="kz-field-container" id="field-<?php echo $field_id; ?>">
                        <div class="kz-field-header">
                            <span class="kz-drag-handle">☰</span>
                            <span class="kz-toggle-collapse">Q<?php echo $field_order; ?>: <?php echo $field_label ?: 'New Question'; ?></span>
                            <span class="kz-remove-field">✖</span>
                        </div>
                        <div class="kz-field-body">
                            <label>Input Field Label:</label>
                            <input type="text" 
                                   placeholder="Enter field label here" 
                                   class="kz-field-label-input" 
                                   name="fields[<?php echo $field_id; ?>][field_label]" 
                                   value="<?php echo $field_label; ?>">
                            
                            <label>Field Type:</label>
                            <select class="kz-field-type-selector" name="fields[<?php echo $field_id; ?>][field_type]">
                                <option value="text_input" <?php selected($field_type, 'text_input'); ?>>Text Input</option>
                                <option value="number_input" <?php selected($field_type, 'number_input'); ?>>Number Input</option>
                                <option value="radio" <?php selected($field_type, 'radio'); ?>>Radio Button</option>
                                <option value="checkbox_simple" <?php selected($field_type, 'checkbox_simple'); ?>>Simple Checkbox</option>
                                <option value="textarea" <?php selected($field_type, 'textarea'); ?>>Text Area</option>
                            </select>
        
                            <label>Is Required:</label>
                            <div class="kz-radio-group">
                                <label>
                                    <input type="radio" name="fields[<?php echo $field_id; ?>][is_required]" value="1" <?php checked($is_required, 1); ?>> Yes
                                </label>
                                <label>
                                    <input type="radio" name="fields[<?php echo $field_id; ?>][is_required]" value="0" <?php checked($is_required, 0); ?>> No
                                </label>
                            </div>
        
                            <div class="kz-dynamic-options">
                                <?php if ($field_type === 'text_input' || $field_type === 'number_input' || $field_type === 'textarea'): ?>
                                    <label>Placeholder:</label>
                                    <input type="text" class="kz-placeholder-input" placeholder="Enter placeholder text"
                                           value="<?php echo esc_attr($field_options['placeholder'] ?? ''); ?>">
                                    <?php if ($field_type === 'number_input' || $field_type === 'textarea'): ?>
                                        <label>Min Value:</label>
                                        <input type="number" class="kz-min-value" placeholder="Enter minimum value"
                                               value="<?php echo esc_attr($field_options['min'] ?? ''); ?>">
                                        <label>Max Value:</label>
                                        <input type="number" class="kz-max-value" placeholder="Enter maximum value"
                                               value="<?php echo esc_attr($field_options['max'] ?? ''); ?>">
                                    <?php endif; ?>
                                <?php elseif ($field_type === 'radio' || $field_type === 'checkbox_simple'): ?>
                                    <div class="kz-<?php echo $field_type; ?>-options">
                                        <span class="kz-add-<?php echo $field_type; ?> kz-add-btn">+ Add Option</span>
                                        <div class="kz-<?php echo $field_type; ?>-list">
                                            <?php foreach ($field_options['options_list'] ?? [] as $option): ?>
                                                <div class="editable-<?php echo $field_type; ?>-container kz-<?php echo $field_type; ?>-item">
                                                    <label class="editable-<?php echo $field_type; ?>">
                                                        <input type="<?php echo $field_type === 'radio' ? 'radio' : 'checkbox'; ?>"
                                                               name="<?php echo $field_type; ?>-group-<?php echo $field_id; ?>">
                                                        <span class="checkbox-label">
                                                            <input type="text" class="editable-input" 
                                                                   placeholder="Type here..." 
                                                                   value="<?php echo esc_attr($option['label'] ?? ''); ?>">
                                                        </span>
                                                    </label>
                                                    <span class="kz-remove-<?php echo $field_type; ?> kz-remove-btn">✖</span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
        
                            <!-- Hidden JSON Field -->
                            <input type="hidden" class="kz-options-json" name="fields[<?php echo $field_id; ?>][options]" value="<?php echo esc_attr(json_encode($field_options)); ?>">
                            
                            <!-- Hidden Field Order -->
                            <input type="hidden" class="kz-field-order-input" name="fields[<?php echo $field_id; ?>][field_order]" value="<?php echo $field_order; ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php
}


// Enqueue styles and scripts
function enqueue_custom_meta_box_assets($hook) {
    global $post;

    if ($hook === 'post.php' || $hook === 'post-new.php') {
        wp_enqueue_style('custom-meta-box-style', plugin_dir_url(__FILE__) . 'assets/form-admin.css');
        wp_enqueue_script('custom-meta-box-script', plugin_dir_url(__FILE__) . 'assets/form-admin.js', ['jquery'], false, true);
        wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', [], null, true);

        wp_localize_script('custom-meta-box-script', 'FormMetaData', [
            'formId' => $post->ID ?? null,
        ]);

    }
}
add_action('admin_enqueue_scripts', 'enqueue_custom_meta_box_assets');

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Hook into save_post to handle form field submission
add_action('save_post', 'handle_form_fields_submission');

/**
 * Handle the form fields submission and save to the database
 *
 * @param int $post_id The ID of the post being saved.
 */
function handle_form_fields_submission($post_id) {
    // Verify nonce
    if (!isset($_POST['kz_custom_meta_box_nonce']) || 
        !wp_verify_nonce($_POST['kz_custom_meta_box_nonce'], 'save_custom_meta_box')) {
        return;
    }

    // Prevent auto-saving
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check if the user has the capability to edit posts
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check if the post type matches your expected post type
    if (get_post_type($post_id) !== 'service_form') { // Replace 'service_form' with your actual post type
        return;
    }

    // Verify if the form fields are present in POST
    if (!isset($_POST['fields']) || !is_array($_POST['fields'])) {
        return;
    }

    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'form_fields';

    // Loop through the submitted fields
    foreach ($_POST['fields'] as $field_external_id => $field_data) {
        // Sanitize and prepare the data
        $field_type = sanitize_text_field($field_data['field_type']);
        $field_label = sanitize_text_field($field_data['field_label']);
        $field_description = isset($field_data['field_description']) ? sanitize_textarea_field($field_data['field_description']) : '';
        $is_required = isset($field_data['is_required']) ? (int) $field_data['is_required'] : 0;
        $field_order = isset($field_data['field_order']) ? (int) $field_data['field_order'] : 0;
        $field_options = isset($field_data['options']) ? wp_json_encode($field_data['options']) : '{}';

        // Check if the field already exists by `field_external_id`
        $existing_field = $wpdb->get_row($wpdb->prepare(
            "SELECT field_id FROM $table_name WHERE service_form_id = %d AND field_external_id = %s",
            $post_id,
            $field_external_id
        ));

        if ($existing_field) {
            // Update the existing field
            $wpdb->update(
                $table_name,
                [
                    'field_type' => $field_type,
                    'field_label' => $field_label,
                    'field_description' => $field_description,
                    'is_required' => $is_required,
                    'field_order' => $field_order,
                    'field_options' => $field_options,
                ],
                ['field_id' => $existing_field->field_id],
                [
                    '%s', // field_type
                    '%s', // field_label
                    '%s', // field_description
                    '%d', // is_required
                    '%d', // field_order
                    '%s', // field_options
                ],
                ['%d'] // field_id
            );
        } else {
            // Insert a new field
            $wpdb->insert(
                $table_name,
                [
                    'field_external_id' => $field_external_id,
                    'service_form_id' => $post_id,
                    'field_type' => $field_type,
                    'field_label' => $field_label,
                    'field_description' => $field_description,
                    'is_required' => $is_required,
                    'field_order' => $field_order,
                    'field_options' => $field_options,
                ],
                [
                    '%s', // field_external_id
                    '%d', // service_form_id
                    '%s', // field_type
                    '%s', // field_label
                    '%s', // field_description
                    '%d', // is_required
                    '%d', // field_order
                    '%s', // field_options
                ]
            );
        }
    }
}
add_action('save_post', 'handle_form_fields_submission');
