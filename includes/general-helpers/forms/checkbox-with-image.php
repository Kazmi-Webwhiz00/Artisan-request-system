<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Render a styled checkbox field with dynamic values (label, image, and ID).
 *
 * @param string $name Field name (required).
 * @param string $id Field ID (required).
 * @param string $label Field label (required).
 * @param string $image_url Image URL (required).
 * @param bool $checked Whether the checkbox is checked (optional, default false).
 */
function render_dynamic_checkbox_field($base_id, $name, $label, $image_url, $checked = false) {
    // Generate the combined id-name
    $unique_key = esc_attr($base_id) . '-' . esc_attr($name);

    ?>
    <div class="checkbox-container">
        <div class="checkbox-card">
            <!-- Use the unique key for both id and name -->
            <input type="checkbox" name="<?php echo $unique_key; ?>" value="<?php echo $label; ?>" id="<?php echo $unique_key; ?>" <?php echo $checked ? 'checked' : ''; ?> />
            <label for="<?php echo $unique_key; ?>">
                <div class="checkbox-box">✓</div>
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($label); ?> Image">
                <div class="label"><?php echo esc_html($label); ?></div>
            </label>
        </div>
    </div>
    <?php
}



// Add Bootstrap CSS and custom styles
add_action('wp_head', function() {
    ?>
    <?php
});
