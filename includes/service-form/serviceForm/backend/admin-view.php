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

// Render the custom meta box
// Render the custom meta box
function render_custom_meta_box($post) {
    wp_nonce_field('save_custom_meta_box', 'kz_custom_meta_box_nonce');
    ?>
    <div>
        <span id="kz-add-new-field">Add New Field</span>
        <div id="kz-fields-container"></div>
    </div>
    <?php
}


// Enqueue styles and scripts
function enqueue_custom_meta_box_assets($hook) {
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        wp_enqueue_style('custom-meta-box-style', plugin_dir_url(__FILE__) . 'assets/form-admin.css');
        wp_enqueue_script('custom-meta-box-script', plugin_dir_url(__FILE__) . 'assets/form-admin.js', ['jquery'], false, true);
        wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', [], null, true);

        wp_localize_script('custom-meta-box-script', 'FormMetaData', [
            'formId' => $post->ID ?? null,
        ]);

    }
}
add_action('admin_enqueue_scripts', 'enqueue_custom_meta_box_assets');

// Save custom meta box data
function save_custom_meta_box($post_id) {
    if (!isset($_POST['custom_meta_box_nonce']) || !wp_verify_nonce($_POST['custom_meta_box_nonce'], 'save_custom_meta_box')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['dynamic_fields'])) {
        update_post_meta($post_id, 'dynamic_fields', sanitize_text_field($_POST['dynamic_fields']));
    }
}
add_action('save_post', 'save_custom_meta_box');
