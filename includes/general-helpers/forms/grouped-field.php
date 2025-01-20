<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Render a Bootstrap-styled pair of fields with an input group and a separator between them
 *
 * @param string $field1_name Field name for the first input (required).
 * @param string $field1_id Field ID for the first input (required).
 * @param string $field1_label Label for the first field (required).
 * @param string $field2_name Field name for the second input (required).
 * @param string $field2_id Field ID for the second input (required).
 * @param string $field2_label Label for the second field (required).
 * @param string $field1_value Default value for the first field (optional).
 * @param string $field2_value Default value for the second field (optional).
 * @param bool $required Whether the fields are required (optional, default false).
 * @param array $additional_attrs Additional attributes for the inputs (optional).
 */
function render_grouped_fields($field1_name, $field1_id, $field1_label, $field2_name, $field2_id, $field2_label, $field1_value = '', $field2_value = '', $required = false, $additional_attrs = []) {
    // Start rendering
    $field = '<div class="mb-3">'; // Bootstrap's margin-bottom utility class
    
    // Add label for the first field
    $field .= '<label for="' . esc_attr($field1_id) . '" class="form-label">' . esc_html($field1_label) . '</label>';

    // Start input group
    $field .= '<div class="input-group">';
    
    // First input field
    $field .= '<input type="text" class="form-control" name="' . esc_attr($field1_name) . '" id="' . esc_attr($field1_id) . '"';
    if ($field1_value) {
        $field .= ' value="' . esc_attr($field1_value) . '"';
    }
    if ($required) {
        $field .= ' required';
    }
    foreach ($additional_attrs as $attr_key => $attr_value) {
        $field .= ' ' . esc_attr($attr_key) . '="' . esc_attr($attr_value) . '"';
    }
    $field .= ' />';
    
    // Pipe separator
    $field .= '<span class="input-group-text">|</span>';
    
    // Second input field
    $field .= '<input type="text" class="form-control" name="' . esc_attr($field2_name) . '" id="' . esc_attr($field2_id) . '"';
    if ($field2_value) {
        $field .= ' value="' . esc_attr($field2_value) . '"';
    }
    if ($required) {
        $field .= ' required';
    }
    foreach ($additional_attrs as $attr_key => $attr_value) {
        $field .= ' ' . esc_attr($attr_key) . '="' . esc_attr($attr_value) . '"';
    }
    $field .= ' />';
    
    // Close input group
    $field .= '</div>';
    
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
        .form-label {
            font-weight: bold;
        }
        .input-group-text {
            font-weight: bold;
        }
    </style>
    <?php
});
?>
