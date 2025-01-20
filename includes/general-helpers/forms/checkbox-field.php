<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Render a styled full-width checkbox field with right-aligned checkbox.
 *
 * @param string $name Field name (required).
 * @param string $id Field ID (required).
 * @param string $label Field label (optional).
 * @param bool $checked Whether the checkbox is checked (optional, default false).
 * @param bool $required Whether the field is required (optional, default false).
 * @param array $additional_attrs Additional attributes (optional).
 */
function render_checkbox_field($name, $id, $label = '', $checked = false, $required = false, $additional_attrs = []) {
    // Start rendering
    $field = '<div class="kz-checkbox-container">'; // Prefixed container class
    
    // Add label if provided
    if ($label) {
        $field .= '<label for="' . esc_attr($id) . '" class="kz-checkbox-label">' . esc_html($label) . '</label>';
    }

    // Add checkbox input
    $field .= '<input type="checkbox" class="kz-checkbox" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '"';
    if ($checked) {
        $field .= ' checked';
    }
    if ($required) {
        $field .= ' required';
    }
    foreach ($additional_attrs as $attr_key => $attr_value) {
        $field .= ' ' . esc_attr($attr_key) . '="' . esc_attr($attr_value) . '"';
    }
    $field .= ' />';
    
    // Close container
    $field .= '</div>';
    
    // Echo or return the field
    echo $field;
}


// Add Bootstrap CDN for styling
add_action('wp_head', function() {
    ?>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-check-label {
            font-size: 1rem;
            margin-left: 8px;
        }
    </style>
    <?php
});
