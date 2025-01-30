<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Render a Bootstrap-styled radio button field
 *
 * @param string $name Field name (required).
 * @param string $id Field ID (required).
 * @param string $label Field label (optional).
 * @param bool $checked Whether the radio button is selected (optional, default false).
 * @param bool $required Whether the field is required (optional, default false).
 * @param array $additional_attrs Additional attributes (optional).
 */
function render_radio_button_field($name, $id, $label = '', $checked = false, $required = false, $additional_attrs = []) {
    // Start rendering
    $field = '<label class="kz-radio-container">'; // Add specific class for styling
    
    // Add radio button input
    $field .= '<input type="radio" class="kz-radio" name="' . esc_attr($name) . '"  value="' . esc_attr($label) . '"  id="' . esc_attr($id) . '"';
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
    
    // Add label if provided
    if ($label) {
        $field .= '<label for="' . esc_attr($id) . '" class="kz-radio-label">' . esc_html($label) . '</label>';
    }
    
    // Close container
    $field .= '</label>';
    
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
