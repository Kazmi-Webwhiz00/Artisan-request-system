<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Render a Bootstrap-styled phone field with input group
 *
 * @param string $name Field name (required).
 * @param string $id Field ID (required).
 * @param string $label Field label (optional).
 * @param string $placeholder Placeholder text (optional).
 * @param string $value Default value (optional).
 * @param bool $required Whether the field is required (optional, default false).
 * @param string $prefix Phone prefix (optional, default '+1').
 * @param array $additional_attrs Additional attributes (optional).
 */
function render_phone_field($name, $id, $label = '', $placeholder = '', $value = '', $required = false, $prefix = '+1', $additional_attrs = []) {
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

    // Add input group for phone field
    $field .= '<div class="input-group">';
    $field .= '<span class="input-group-text">' . esc_html($prefix) . '</span>';
    $field .= '<input type="tel" class="form-control" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '"';
    if ($placeholder) {
        $field .= ' placeholder="' . esc_attr($placeholder) . '"';
    }
    if ($value) {
        $field .= ' value="' . esc_attr($value) . '"';
    }
    if ($required) {
        $field .= ' required';
    }
    foreach ($additional_attrs as $attr_key => $attr_value) {
        $field .= ' ' . esc_attr($attr_key) . '="' . esc_attr($attr_value) . '"';
    }
    $field .= ' />';
    $field .= '</div>'; // Close input-group
    
    // Close container
    $field .= '</div>';
    
    // Echo or return the field
    echo $field;
}

// Add Bootstrap CDN and reset styles for the phone field
add_action('wp_head', function() {
    ?>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Reset styles for phone field to enforce Bootstrap defaults */
        .input-group {
            margin-bottom: 15px;
        }
        .input-group-text {
            background-color: #f8f9fa; /* Default light background */
            border: 1px solid #ced4da; /* Default border color */
        }
        .form-control:focus {
            border-color: #86b7fe !important;    /* Bootstrap's focus border color */
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important; /* Bootstrap focus shadow */
        }
    </style>
    <?php
});
