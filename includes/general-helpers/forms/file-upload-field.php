<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Render a Bootstrap-styled file upload field with a preview above the "Choose File" button
 *
 * @param string $name Field name (required).
 * @param string $id Field ID (required).
 * @param string $label Field label (optional).
 * @param bool $required Whether the field is required (optional, default false).
 * @param array $additional_attrs Additional attributes (optional).
 */
function render_file_upload_field($name, $id, $label = '', $required = false, $additional_attrs = []) {
    // Start rendering the field container
    $field = '<div class="mb-3 form-group kz-file-upload">'; // Add a specific class for the container

    // Add label if provided
    if ($label) {
        $field .= '<label for="' . esc_attr($id) . '" class="form-label">' . esc_html($label) . '</label>';
    }

    // Add preview container above the input field
    $field .= '<div id="' . esc_attr($id) . '_preview" class="file-preview" style="display:none;">';
    $field .= '<img src="" alt="File Preview" class="img-thumbnail" style="max-width: 100%; display:none;" />';
    $field .= '</div>';

    // Add file input field
    $field .= '<input type="file" class="form-control kz-file-input" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '"';
    if ($required) {
        $field .= ' required';
    }
    foreach ($additional_attrs as $attr_key => $attr_value) {
        $field .= ' ' . esc_attr($attr_key) . '="' . esc_attr($attr_value) . '"';
    }
    $field .= ' />';

    // Close the field container
    $field .= '</div>';

    // Echo the field HTML
    echo $field;
}

// Add Bootstrap CDN for styling
add_action('wp_head', function() {
    ?>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-group input[type="file"] {
            padding: 5px;
            font-size: 1rem;
        }
    </style>
    <?php
});
