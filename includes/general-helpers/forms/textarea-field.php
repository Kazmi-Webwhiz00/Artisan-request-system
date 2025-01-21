<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Render a Bootstrap-styled textarea field with prefixed classes for uniqueness
 *
 * @param string $name Field name (required).
 * @param string $id Field ID (required).
 * @param string $label Field label (optional).
 * @param string $placeholder Placeholder text (optional).
 * @param string $value Default value (optional).
 * @param bool $required Whether the field is required (optional, default false).
 * @param array $additional_attrs Additional attributes like rows, cols, maxlength (optional).
 */
function render_textarea_field($name, $id, $label = '', $placeholder = '', $value = '', $required = false, $additional_attrs = []) {
    // Start rendering
    $field = '<div class="mb-3">'; // Bootstrap's margin-bottom utility class
    
    // Add label if provided
    if ($label) {
        $field .= '<label for="' . esc_attr($id) . '" class="form-label">' . esc_html($label);
        if ($required) {
            $field .= ' <span class="text-danger">*</span>'; // Bootstrap's text-danger class for required
        }
        $field .= '</label>';
    }
    
    // Add textarea field
    $field .= '<textarea class="form-control" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '"';
    if ($placeholder) {
        $field .= ' placeholder="' . esc_attr($placeholder) . '"';
    }
    if ($required) {
        $field .= ' required';
    }
    foreach ($additional_attrs as $attr_key => $attr_value) {
        $field .= ' ' . esc_attr($attr_key) . '="' . esc_attr($attr_value) . '"';
    }
    $field .= '>';
    $field .= esc_textarea($value); // Insert the default value between the opening and closing tags
    $field .= '</textarea>';
    
    // Close container
    $field .= '</div>';
    
    // Echo or return the field
    echo $field;
}

// Add Bootstrap CDN for styling (if not already included)
add_action('wp_head', function() {
    ?>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Reset styles to enforce Bootstrap defaults */
        .form-control {
            border: 1px solid #ced4da !important; /* Bootstrap's default light gray border */
            box-shadow: none !important;         /* Remove any custom box shadows */
            border-radius: .375rem !important;   /* Bootstrap's default border radius */
        }
        .form-control:focus {
            border-color: #86b7fe !important;    /* Bootstrap's focus border color */
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important; /* Bootstrap focus shadow */
        }
    </style>
    <?php
});
