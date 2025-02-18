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
                    $cleaned_options = stripslashes($field->field_options ?? '');
                    $field_options = json_decode($cleaned_options, true);
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
                                <option value="email" <?php selected($field_type, 'email'); ?>>Email</option>
                                <option value="number_input" <?php selected($field_type, 'number_input'); ?>>Number Input</option>
                                <option value="radio" <?php selected($field_type, 'radio'); ?>>Radio Button</option>
                                <option value="checkbox_simple" <?php selected($field_type, 'checkbox_simple'); ?>>Simple Checkbox</option>
                                <option value="checkbox_with_image" <?php selected($field_type, 'checkbox_with_image'); ?>>Checkbox with Image</option>
                                <option value="textarea" <?php selected($field_type, 'textarea'); ?>>Text Area</option>
                                <option value="file_input" <?php selected($field_type, 'file_input'); ?>>File Input</option>
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
                                <?php if ($field_type === 'text_input' || $field_type === 'number_input' || $field_type === 'textarea' || $field_type === 'email'): ?>
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
                                <?php elseif ($field_type === 'checkbox_simple'): ?>
                                    <div class="kz-checkbox-options">
                                        <span class="kz-add-checkbox kz-add-btn">+ Add Checkbox</span>
                                        <div class="kz-checkbox-list">
                                            <?php foreach ($field_options['options_list'] ?? [] as $option): ?>
                                                <div class="editable-checkbox-container kz-checkbox-item">
                                                    <label class="editable-checkbox">
                                                        <input type="checkbox" name="checkbox-group-<?php echo $field_id; ?>">
                                                        <span class="checkbox-label">
                                                            <input type="text" class="editable-input" 
                                                                placeholder="Type here..." 
                                                                value="<?php echo esc_attr($option['label'] ?? ''); ?>">
                                                        </span>
                                                    </label>
                                                    <span class="kz-remove-checkbox kz-remove-btn">✖</span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php elseif ($field_type === 'radio'): ?>
                                    <div class="kz-radio-options">
                                        <span class="kz-add-radio kz-add-btn">+ Add Option</span>
                                        <div class="kz-radio-list">
                                            <?php foreach ($field_options['options_list'] ?? [] as $option): ?>
                                                <div class="editable-radio-container kz-radio-item">
                                                    <label class="editable-radio">
                                                        <input type="radio" name="radio-group-<?php echo $field_id; ?>">
                                                        <span class="radio-label">
                                                            <input type="text" class="editable-input" 
                                                                placeholder="Type here..." 
                                                                value="<?php echo esc_attr($option['label'] ?? ''); ?>">
                                                        </span>
                                                    </label>
                                                    <span class="kz-remove-radio kz-remove-btn">✖</span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php elseif ($field_type === 'checkbox_with_image'): ?>
                                    <div class="kz-checkbox-with-image-options">
                                        <span class="kz-add-checkbox-with-image kz-add-btn">+ Add Option</span>
                                        <div class="kz-checkbox-with-image-list">
                                            <?php foreach ($field_options['options_list'] ?? [] as $option): ?>
                                                <div class="kz-checkbox-with-image-item" data-image-id="<?php echo esc_attr($option['imageId'] ?? ''); ?>">
                                                    <div class="image-upload-preview" style="border: 1px solid #ddd; padding: 10px; text-align: center;">
                                                        <?php if (!empty($option['imageId'])): ?>
                                                            <?php $image_url = wp_get_attachment_url($option['imageId']); ?>
                                                            <img src="<?php echo esc_url($image_url); ?>" alt="Preview" style="max-width: 100px;">
                                                        <?php else: ?>
                                                            <button type="button" class="upload-image-button">Upload Image</button>
                                                        <?php endif; ?>
                                                    </div>
                                                    <input type="text" class="editable-input" placeholder="Type here..." 
                                                        value="<?php echo esc_attr($option['label'] ?? ''); ?>">
                                                    <span class="kz-remove-checkbox-with-image kz-remove-btn">✖</span>
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
        wp_enqueue_media(); 
        wp_enqueue_style('custom-meta-box-style', plugin_dir_url(__FILE__) . 'assets/form-admin.css');
        wp_enqueue_script('custom-meta-box-script', plugin_dir_url(__FILE__) . 'assets/form-admin.js', ['jquery'], false, true);
        wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', [], null, true);

        wp_localize_script('custom-meta-box-script', 'FormMetaData', [
            'formId' => $post->ID ?? null,
        ]);

    }
}
add_action('admin_enqueue_scripts', 'enqueue_custom_meta_box_assets');

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

    global $wpdb;
    $table_name = $wpdb->prefix . 'form_fields';

    // Get all existing fields for this form
    $existing_fields = $wpdb->get_col($wpdb->prepare(
        "SELECT field_external_id FROM $table_name WHERE service_form_id = %d",
        $post_id
    ));

    // Fields sent via the form
    $submitted_fields = isset($_POST['fields']) ? array_keys($_POST['fields']) : [];

    // Identify fields to delete
    $fields_to_delete = array_diff($existing_fields, $submitted_fields);

    // Delete removed fields
    if (!empty($fields_to_delete)) {
        foreach ($fields_to_delete as $field_external_id) {
            $wpdb->delete($table_name, [
                'service_form_id' => $post_id,
                'field_external_id' => $field_external_id,
            ], [
                '%d',
                '%s',
            ]);
        }
    }

    // Loop through submitted fields and update/insert them
    foreach ($submitted_fields as $field_external_id) {
        $field_data = $_POST['fields'][$field_external_id];
        $field_type = sanitize_text_field($field_data['field_type']);
        $field_label = sanitize_text_field($field_data['field_label']);
        $field_description = isset($field_data['field_description']) ? sanitize_textarea_field($field_data['field_description']) : '';
        $is_required = isset($field_data['is_required']) ? (int) $field_data['is_required'] : 0;
        $field_order = isset($field_data['field_order']) ? (int) $field_data['field_order'] : 0;
        $field_options = isset($field_data['options']) ? $field_data['options'] : '{}';

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
                    '%s', '%s', '%s', '%d', '%d', '%s'
                ],
                ['%d']
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
                    '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%s'
                ]
            );
        }
    }
}
add_action('save_post', 'handle_form_fields_submission');
