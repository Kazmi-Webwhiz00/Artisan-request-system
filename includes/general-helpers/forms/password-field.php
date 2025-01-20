<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Render a Bootstrap-styled password field with input group
 *
 * @param string $name Field name (required).
 * @param string $id Field ID (required).
 * @param string $label Field label (optional).
 * @param string $placeholder Placeholder text (optional).
 * @param string $value Default value (optional).
 * @param bool $required Whether the field is required (optional, default false).
 * @param array $additional_attrs Additional attributes (optional).
 */
function render_password_field($name, $id, $label = '', $placeholder = '', $value = '', $required = false, $additional_attrs = []) {
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

    // Add input group for password field
    $field .= '<div class="input-group">';
    $field .= '<input type="password" class="form-control" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '"';
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
    $field .= '<button type="button" class="btn btn-outline-secondary toggle-password" aria-label="Toggle password visibility">Show</button>';
    $field .= '</div>'; // Close input-group

    // Close container
    $field .= '</div>';

    // Echo or return the field
    echo $field;
}

// Add Bootstrap CDN and reset styles for the password field
add_action('wp_head', function() {
    ?>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .input-group .form-control:focus {
            border-color: #86b7fe !important; /* Bootstrap's focus border color */
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important; /* Bootstrap focus shadow */
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleButtons = document.querySelectorAll('.toggle-password');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const input = button.previousElementSibling;
                    if (input.type === 'password') {
                        input.type = 'text';
                        button.textContent = 'Hide';
                    } else {
                        input.type = 'password';
                        button.textContent = 'Show';
                    }
                });
            });
        });
    </script>
    <?php
});
