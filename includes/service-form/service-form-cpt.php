<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Service Form Custom Post Type
 */
function register_service_form_cpt() {
    $labels = array(
        'name'                  => _x('Service Forms', 'Post Type General Name', 'textdomain'),
        'singular_name'         => _x('Service Form', 'Post Type Singular Name', 'textdomain'),
        'menu_name'             => __('Service Forms', 'textdomain'),
        'name_admin_bar'        => __('Service Form', 'textdomain'),
        'archives'              => __('Service Form Archives', 'textdomain'),
        'attributes'            => __('Service Form Attributes', 'textdomain'),
        'parent_item_colon'     => __('Parent Service Form:', 'textdomain'),
        'all_items'             => __('All Service Forms', 'textdomain'),
        'add_new_item'          => __('Add New Service Form', 'textdomain'),
        'add_new'               => __('Add New', 'textdomain'),
        'new_item'              => __('New Service Form', 'textdomain'),
        'edit_item'             => __('Edit Service Form', 'textdomain'),
        'update_item'           => __('Update Service Form', 'textdomain'),
        'view_item'             => __('View Service Form', 'textdomain'),
        'view_items'            => __('View Service Forms', 'textdomain'),
        'search_items'          => __('Search Service Forms', 'textdomain'),
        'not_found'             => __('Not found', 'textdomain'),
        'not_found_in_trash'    => __('Not found in Trash', 'textdomain'),
        'featured_image'        => __('Featured Image', 'textdomain'),
        'set_featured_image'    => __('Set featured image', 'textdomain'),
        'remove_featured_image' => __('Remove featured image', 'textdomain'),
        'use_featured_image'    => __('Use as featured image', 'textdomain'),
        'insert_into_item'      => __('Insert into service form', 'textdomain'),
        'uploaded_to_this_item' => __('Uploaded to this service form', 'textdomain'),
        'items_list'            => __('Service Forms list', 'textdomain'),
        'items_list_navigation' => __('Service Forms list navigation', 'textdomain'),
        'filter_items_list'     => __('Filter service forms list', 'textdomain'),
    );

    $args = array(
        'label'                 => __('Service Form', 'textdomain'),
        'description'           => __('Custom Post Type for Service Forms', 'textdomain'),
        'labels'                => $labels,
        'supports'              => array('title'), // Only title field is supported
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-forms', // Dashicon for Service Form
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true, // For Gutenberg support
    );

    register_post_type('service_form', $args);
}
add_action('init', 'register_service_form_cpt');

/**
 * Add Meta Box for Form Description
 */
function add_service_form_meta_box() {
    add_meta_box(
        'service_form_description_meta_box',
        __('Form Description', 'textdomain'),
        'render_service_form_meta_box',
        'service_form',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_service_form_meta_box');

/**
 * Render Meta Box for Form Description
 */
function render_service_form_meta_box($post) {
    // Retrieve existing value from post meta
    $form_description = get_post_meta($post->ID, '_form_description', true);

    // Nonce for security
    wp_nonce_field('service_form_meta_box_nonce', 'service_form_meta_box_nonce_field');

    ?>
    <p>
        <label for="form_description"><?php _e('Form Description:', 'textdomain'); ?></label><br>
        <textarea id="form_description" name="form_description" rows="5" style="width: 100%;"><?php echo esc_textarea($form_description); ?></textarea>
    </p>
    <?php
}

/**
 * Save Form Description Meta Box Data
 */
function save_service_form_meta_box_data($post_id) {
    // Verify nonce
    if (!isset($_POST['service_form_meta_box_nonce_field']) ||
        !wp_verify_nonce($_POST['service_form_meta_box_nonce_field'], 'service_form_meta_box_nonce')) {
        return;
    }

    // Check for autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save Form Description
    if (isset($_POST['form_description'])) {
        update_post_meta($post_id, '_form_description', sanitize_textarea_field($_POST['form_description']));
    }
}
add_action('save_post', 'save_service_form_meta_box_data');
