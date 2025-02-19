<?php
// Add a submenu under "Artisans" in the WP Admin
function artisan_register_admin_page() {
    add_submenu_page(
        'edit.php?post_type=artisan', // Parent menu (Artisans CPT)
        __( 'Manage Artisans', 'textdomain' ), // Page title
        __( 'Manage Artisans', 'textdomain' ), // Menu title
        'manage_options', // Capability required
        'manage_artisans', // Menu slug
        'render_artisan_admin_page' // Callback function
    );
}
add_action( 'admin_menu', 'artisan_register_admin_page' );



function render_artisan_admin_page() {
    global $wpdb;

    // Get selected filter and search query
    $filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : 'all';
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

    // Build query arguments
    $meta_query = array();
    if ( in_array($filter, array('pending', 'approved', 'rejected')) ) {
        $meta_query[] = array(
            'key'     => 'artisan_status',
            'value'   => $filter,
            'compare' => '='
        );
    }

    $args = array(
        'post_type'      => 'artisan',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => $meta_query,
    );

    // Add search query
    if ( !empty($search) ) {
        $args['s'] = $search;
    }

    $artisans = new WP_Query($args);
    ?>
    
    <div class="wrap">
        <h1><?php echo esc_html__('Manage Artisans', 'textdomain'); ?></h1>

        <!-- Filter & Search Form -->
        <form method="get" action="" style="margin-bottom: 20px; display: flex; gap: 10px;">
            <input type="hidden" name="post_type" value="artisan">
            <input type="hidden" name="page" value="manage_artisans">
            
            <!-- Status Filter -->
            <select name="filter" id="filter" onchange="this.form.submit();">
                <option value="all" <?php selected($filter, 'all'); ?>><?php esc_html_e('All', 'textdomain'); ?></option>
                <option value="pending" <?php selected($filter, 'pending'); ?>><?php esc_html_e('Pending', 'textdomain'); ?></option>
                <option value="approved" <?php selected($filter, 'approved'); ?>><?php esc_html_e('Approved', 'textdomain'); ?></option>
                <option value="rejected" <?php selected($filter, 'rejected'); ?>><?php esc_html_e('Rejected', 'textdomain'); ?></option>
            </select>

            <!-- Search Input -->
            <input type="text" name="s" placeholder="<?php esc_attr_e('Search by name or email...', 'textdomain'); ?>" value="<?php echo esc_attr($search); ?>">
            
            <button type="submit" class="button button-primary"><?php esc_html_e('Search', 'textdomain'); ?></button>
        </form>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Name', 'textdomain'); ?></th>
                    <th><?php esc_html_e('Email', 'textdomain'); ?></th>
                    <th><?php esc_html_e('Status', 'textdomain'); ?></th>
                    <th><?php esc_html_e('Actions', 'textdomain'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($artisans->have_posts()) :
                    while ($artisans->have_posts()) : $artisans->the_post();
                        $post_id = get_the_ID();
                        $name = get_the_title();
                        $email = get_post_meta($post_id, 'email', true);
                        $status = get_post_meta($post_id, 'artisan_status', true);

                        // Define button actions
                        $approve_url = admin_url("admin-post.php?action=update_artisan_status&post_id={$post_id}&status=approved");
                        $reject_url = admin_url("admin-post.php?action=update_artisan_status&post_id={$post_id}&status=rejected");
                        $view_url = get_edit_post_link($post_id);
                        ?>
                        <tr>
                            <td><?php echo esc_html($name); ?></td>
                            <td><?php echo esc_html($email); ?></td>
                            <td>
                                <strong class="status-<?php echo esc_attr($status); ?>">
                                    <?php echo ucfirst(esc_html($status)); ?>
                                </strong>
                            </td>
                            <td>
                                <a href="<?php echo esc_url($view_url); ?>" class="button button-secondary">
                                    <?php esc_html_e('View', 'textdomain'); ?>
                                </a>
                                <?php if ($status !== 'approved') : ?>
                                    <a href="<?php echo esc_url($approve_url); ?>" class="button button-primary">
                                        <?php esc_html_e('Approve', 'textdomain'); ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ($status !== 'rejected') : ?>
                                    <a href="<?php echo esc_url($reject_url); ?>" class="button rejct-button">
                                        <?php esc_html_e('Reject', 'textdomain'); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    echo '<tr><td colspan="4">' . esc_html__('No artisans found.', 'textdomain') . '</td></tr>';
                endif;
                ?>
            </tbody>
        </table>
    </div>
    <?php
}




function update_artisan_status() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to perform this action.', 'textdomain'));
    }

    // Get post ID and status from URL
    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
    $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

    if ($post_id && in_array($status, array('approved', 'rejected'))) {
        update_post_meta($post_id, 'artisan_status', $status);
    }

    // Redirect back to the artisan management page
    wp_redirect(admin_url('edit.php?post_type=artisan&page=manage_artisans'));
    exit;
}
add_action('admin_post_update_artisan_status', 'update_artisan_status');
