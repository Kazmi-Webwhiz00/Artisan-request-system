<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Render a Bootstrap-styled select field with prefixed classes for uniqueness
 *
 * @param string $name Field name (required).
 * @param string $id Field ID (required).
 * @param string $label Field label (optional).
 * @param array $options Array of options in 'value' => 'label' format (required).
 * @param string $selected Default selected value (optional).
 * @param bool $required Whether the field is required (optional, default false).
 * @param array $additional_attrs Additional attributes (optional).
 */
function render_select_field($name, $id, $label = '', $options = [], $selected = '', $required = false, $additional_attrs = []) {
    // Start rendering
    $field = '<div class="sf-select-field mb-3">'; // Add a unique container class with sf- prefix
    
    // Add label if provided
    if ($label) {
        $field .= '<label for="' . esc_attr($id) . '" class="sf-select-label form-label">' . esc_html($label);
        if ($required) {
            $field .= ' <span class="sf-required text-danger">*</span>';
        }
        $field .= '</label>';
    }
    
    // Add select field
    $field .= '<select class="sf-select-input form-select" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '"';
    if ($required) {
        $field .= ' required';
    }
    foreach ($additional_attrs as $attr_key => $attr_value) {
        $field .= ' ' . esc_attr($attr_key) . '="' . esc_attr($attr_value) . '"';
    }
    $field .= '>';
    
    // Add options
    foreach ($options as $value => $label) {
        $field .= '<option value="' . esc_attr($value) . '"';
        if ($value === $selected) {
            $field .= ' selected';
        }
        $field .= '>' . esc_html($label) . '</option>';
    }
    
    $field .= '</select>';
    
    // Close container
    $field .= '</div>';
    
    // Echo or return the field
    echo $field;
}

// Add Bootstrap CDN and reset styles for select fields
add_action('wp_head', function() {
    ?>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Reset styles for select fields to enforce Bootstrap defaults */
        .sf-select-input {
            border: 1px solid #ced4da !important; /* Bootstrap's default light gray border */
            box-shadow: none !important;         /* Remove any custom box shadows */
            border-radius: .375rem !important;   /* Bootstrap's default border radius */
            padding: .375rem .75rem !important; /* Default padding for selects */
            font-size: 1rem !important;          /* Default font size */
            line-height: 1.5 !important;         /* Default line height */
        }
        .sf-select-input:focus {
            border-color: #86b7fe !important;    /* Bootstrap's focus border color */
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important; /* Bootstrap focus shadow */
        }
        .sf-select-label {
            font-family: Arial, sans-serif;
        }
        .sf-required {
            color: red;
        }
    </style>
    <?php
});

