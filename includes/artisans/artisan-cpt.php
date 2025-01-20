<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Register "artisan" Custom Post Type
 */
function register_artisan_cpt() {
    $labels = array(
        'name'               => __( 'Artisans', 'textdomain' ),
        'singular_name'      => __( 'Artisan', 'textdomain' ),
        'add_new'            => __( 'Add New Artisan', 'textdomain' ),
        'add_new_item'       => __( 'Add New Artisan', 'textdomain' ),
        'edit_item'          => __( 'Edit Artisan', 'textdomain' ),
        'new_item'           => __( 'New Artisan', 'textdomain' ),
        'all_items'          => __( 'All Artisans', 'textdomain' ),
        'view_item'          => __( 'View Artisan', 'textdomain' ),
        'search_items'       => __( 'Search Artisans', 'textdomain' ),
        'not_found'          => __( 'No artisans found', 'textdomain' ),
        'not_found_in_trash' => __( 'No artisans found in Trash', 'textdomain' ),
        'menu_name'          => __( 'Artisans', 'textdomain' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'supports'           => array( 'title' ), // Add more as needed: 'editor', 'thumbnail', etc.
        'capability_type'    => 'post',
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'artisans' ),
        'show_in_rest'       => true,  // If you want Gutenberg & REST API support
    );

    register_post_type( 'artisan', $args );
}
add_action( 'init', 'register_artisan_cpt' );

/**
 * Register meta fields for "artisan" CPT.
 * We'll define all the fields we collect from the 11-step form, except for 'password'.
 */
function register_artisan_cpt_meta_fields() {

    // Here we define each meta key => arguments.
    // 'show_in_rest' => true if you want them accessible in the REST API.
    // 'type' can be 'string', 'boolean', 'integer', etc. 
    // 'single' => true means one value per post (not an array).
    // Adjust as needed for your data structure.
    $fields = array(
        // Step 1
        'trade'                    => array( 'type' => 'string',  'single' => true, 'show_in_rest' => true ),
        'zip_code'                 => array( 'type' => 'string',  'single' => true, 'show_in_rest' => true ),
        'email'                    => array( 'type' => 'string',  'single' => true, 'show_in_rest' => true ),

        // Step 2
        'first_name'               => array( 'type' => 'string',  'single' => true, 'show_in_rest' => true ),
        'last_name'                => array( 'type' => 'string',  'single' => true, 'show_in_rest' => true ),
        'phone'                    => array( 'type' => 'string',  'single' => true, 'show_in_rest' => true ),
        'subscribe'                => array( 'type' => 'boolean', 'single' => true, 'show_in_rest' => true ),

        // Step 4
        // This is an array of trades; we can store it as a serialized array or JSON.
        // For best REST usage, consider storing as an array type. But below is the simplest approach:
        'selected_trades'          => array( 'type' => 'string',  'single' => true, 'show_in_rest' => true ),

        // Step 5
        'distance'                 => array( 'type' => 'integer', 'single' => true, 'show_in_rest' => true ),
        'work_throughout_austria'  => array( 'type' => 'boolean','single' => true, 'show_in_rest' => true ),

        // Step 6
        'professional_status'      => array( 'type' => 'string',  'single' => true, 'show_in_rest' => true ),

        // Step 8
        'gisa_number'              => array( 'type' => 'string',  'single' => true, 'show_in_rest' => true ),
        'company_name'             => array( 'type' => 'string',  'single' => true, 'show_in_rest' => true ),
        'address'                  => array( 'type' => 'string',  'single' => true, 'show_in_rest' => true ),
        // Potentially you have two zip codes: one from Step 1, one for business (step8).
        // We'll store step8's as "business_zip_code"
        'business_zip_code'        => array( 'type' => 'string',  'single' => true, 'show_in_rest' => true ),
        'city'                     => array( 'type' => 'string',  'single' => true, 'show_in_rest' => true ),

        // Step 9
        // If you only store the filename, a 'string' is enough.
        // If you store an attachment ID, you'd use 'integer'.
        'business_license'         => array( 'type' => 'string',  'single' => true, 'show_in_rest' => true ),

        // Step 11
        'description'              => array( 'type' => 'string',  'single' => true, 'show_in_rest' => true ),
    );

    foreach ( $fields as $meta_key => $args ) {
        register_post_meta( 'artisan', $meta_key, $args );
    }
}
add_action( 'init', 'register_artisan_cpt_meta_fields' );

function my_artisan_add_meta_box() {
    add_meta_box(
        'my_artisan_details_meta_box',
        __( 'Artisan Details', 'textdomain' ),
        'my_artisan_meta_box_callback',
        'artisan',      // post type
        'normal',       // context
        'default'       // priority
    );
}
add_action( 'add_meta_boxes', 'my_artisan_add_meta_box' );

/**
 * 2. Render the Meta Box Fields
 */
function my_artisan_meta_box_callback( $post ) {
    // Use nonce for verification
    wp_nonce_field( 'my_artisan_save_meta_box_data', 'my_artisan_meta_box_nonce' );

    // Retrieve existing values
    $trade                   = get_post_meta( $post->ID, 'trade', true );
    $zip_code                = get_post_meta( $post->ID, 'zip_code', true );
    $email                   = get_post_meta( $post->ID, 'email', true );
    $first_name              = get_post_meta( $post->ID, 'first_name', true );
    $last_name               = get_post_meta( $post->ID, 'last_name', true );
    $phone                   = get_post_meta( $post->ID, 'phone', true );
    $subscribe               = get_post_meta( $post->ID, 'subscribe', true );
    $selected_trades         = maybe_unserialize( get_post_meta( $post->ID, 'selected_trades', true ) ); 
    $distance                = get_post_meta( $post->ID, 'distance', true );
    $work_throughout_austria = get_post_meta( $post->ID, 'work_throughout_austria', true );
    $professional_status     = get_post_meta( $post->ID, 'professional_status', true );
    $gisa_number             = get_post_meta( $post->ID, 'gisa_number', true );
    $company_name            = get_post_meta( $post->ID, 'company_name', true );
    $address                 = get_post_meta( $post->ID, 'address', true );
    $business_zip_code       = get_post_meta( $post->ID, 'business_zip_code', true );
    $city                    = get_post_meta( $post->ID, 'city', true );
    $business_license        = get_post_meta( $post->ID, 'business_license', true );
    $description             = get_post_meta( $post->ID, 'description', true );

    // Display fields in a simple table (adjust markup as needed)
    echo '<table class="form-table">';
    
    // Step 1 fields
    echo '<tr><th><label>' . esc_html__('Trade', 'textdomain') . '</label></th>';
    echo '<td><input type="text" name="trade" value="' . esc_attr($trade) . '" class="regular-text" /></td></tr>';

    echo '<tr><th><label>' . esc_html__('Zip Code', 'textdomain') . '</label></th>';
    echo '<td><input type="text" name="zip_code" value="' . esc_attr($zip_code) . '" /></td></tr>';

    echo '<tr><th><label>' . esc_html__('Email', 'textdomain') . '</label></th>';
    echo '<td><input type="email" name="email" value="' . esc_attr($email) . '" class="regular-text" /></td></tr>';

    // Step 2 fields
    echo '<tr><th><label>' . esc_html__('First Name', 'textdomain') . '</label></th>';
    echo '<td><input type="text" name="first_name" value="' . esc_attr($first_name) . '" /></td></tr>';

    echo '<tr><th><label>' . esc_html__('Last Name', 'textdomain') . '</label></th>';
    echo '<td><input type="text" name="last_name" value="' . esc_attr($last_name) . '" /></td></tr>';

    echo '<tr><th><label>' . esc_html__('Phone', 'textdomain') . '</label></th>';
    echo '<td><input type="text" name="phone" value="' . esc_attr($phone) . '" /></td></tr>';

    echo '<tr><th><label>' . esc_html__('Subscribe', 'textdomain') . '</label></th>';
    echo '<td><input type="checkbox" name="subscribe" value="1" ' . checked($subscribe, true, false) . ' /></td></tr>';

    // Step 4 selected trades (array)
    echo '<tr><th><label>' . esc_html__('Selected Trades', 'textdomain') . '</label></th>';
    echo '<td><textarea name="selected_trades" rows="2" cols="40">' . esc_textarea( is_array($selected_trades) ? implode(', ', $selected_trades) : $selected_trades ) . '</textarea><br/>';
    echo '<small>' . esc_html__('Comma-separated or store as you prefer.', 'textdomain') . '</small></td></tr>';

    // Step 5
    echo '<tr><th><label>' . esc_html__('Distance (km)', 'textdomain') . '</label></th>';
    echo '<td><input type="number" name="distance" value="' . esc_attr($distance) . '" /></td></tr>';

    echo '<tr><th><label>' . esc_html__('Work Throughout Austria', 'textdomain') . '</label></th>';
    echo '<td><input type="checkbox" name="work_throughout_austria" value="1" ' . checked($work_throughout_austria, true, false) . ' /></td></tr>';

    // Step 6
    echo '<tr><th><label>' . esc_html__('Professional Status', 'textdomain') . '</label></th>';
    echo '<td><input type="text" name="professional_status" value="' . esc_attr($professional_status) . '" class="widefat" /></td></tr>';

    // Step 8
    echo '<tr><th><label>' . esc_html__('GISA Number', 'textdomain') . '</label></th>';
    echo '<td><input type="text" name="gisa_number" value="' . esc_attr($gisa_number) . '" /></td></tr>';

    echo '<tr><th><label>' . esc_html__('Company Name', 'textdomain') . '</label></th>';
    echo '<td><input type="text" name="company_name" value="' . esc_attr($company_name) . '" /></td></tr>';

    echo '<tr><th><label>' . esc_html__('Address', 'textdomain') . '</label></th>';
    echo '<td><input type="text" name="address" value="' . esc_attr($address) . '" /></td></tr>';

    echo '<tr><th><label>' . esc_html__('Business Zip Code', 'textdomain') . '</label></th>';
    echo '<td><input type="text" name="business_zip_code" value="' . esc_attr($business_zip_code) . '" /></td></tr>';

    echo '<tr><th><label>' . esc_html__('City', 'textdomain') . '</label></th>';
    echo '<td><input type="text" name="city" value="' . esc_attr($city) . '" /></td></tr>';

    // Step 9
    echo '<tr><th><label>' . esc_html__('Business License File', 'textdomain') . '</label></th>';
    echo '<td><input type="text" name="business_license" value="' . esc_attr($business_license) . '" class="widefat" /></td></tr>';

    // Step 11
    echo '<tr><th><label>' . esc_html__('Description', 'textdomain') . '</label></th>';
    echo '<td><textarea name="description" rows="4" cols="60">' . esc_textarea($description) . '</textarea></td></tr>';

    echo '</table>';
}

/**
 * 3. Save the Meta Box Fields
 */
function my_artisan_save_meta_box_data( $post_id ) {
    // Check if our nonce is set and valid
    if ( ! isset( $_POST['my_artisan_meta_box_nonce'] ) || 
         ! wp_verify_nonce( $_POST['my_artisan_meta_box_nonce'], 'my_artisan_save_meta_box_data' ) ) {
        return;
    }

    // Check autosave or user capabilities
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Only save for 'artisan' post type
    if ( get_post_type($post_id) !== 'artisan' ) return;

    // Sanitize each field and update post meta
    $fields = array(
        'trade',
        'zip_code',
        'email',
        'first_name',
        'last_name',
        'phone',
        'professional_status',
        'gisa_number',
        'company_name',
        'address',
        'business_zip_code',
        'city',
        'business_license',
        'description',
    );
    // Boolean checkboxes
    $bool_fields = array('subscribe','work_throughout_austria');

    // Textarea that might be array (like 'selected_trades')
    // We'll store as a simple string. If you have a special format, parse it.
    if ( isset( $_POST['selected_trades'] ) ) {
        $trades_input = sanitize_text_field( $_POST['selected_trades'] );
        // If it’s comma-separated, you can do:
        // $trades_array = array_map( 'trim', explode(',', $trades_input) );
        // update_post_meta( $post_id, 'selected_trades', $trades_array );
        // Or store as a single string:
        update_post_meta( $post_id, 'selected_trades', $trades_input );
    }

    // Save the simple text fields
    foreach ( $fields as $field ) {
        if ( isset($_POST[$field]) ) {
            update_post_meta( $post_id, $field, sanitize_text_field( $_POST[$field] ) );
        } else {
            // If you want to clear out the field when empty, you could do:
            // update_post_meta( $post_id, $field, '' );
        }
    }

    // Save the boolean checkboxes
    foreach ( $bool_fields as $bf ) {
        $value = isset($_POST[$bf]) ? true : false;
        update_post_meta( $post_id, $bf, $value );
    }
}
add_action( 'save_post', 'my_artisan_save_meta_box_data' );