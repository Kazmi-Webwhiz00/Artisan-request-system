<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function enqueue_admin_styles() {
    // Ensure you use the correct path to your CSS file
    wp_enqueue_style(
        'artisan-cpt-style', // Handle for the CSS file
        plugin_dir_url(__FILE__) . 'artisan-cpt.css', // Path to the CSS file
        array(), // Dependencies (if any)
        '1.0.0', // Version
        'all' // Media type
    );

       // Enqueue JS file
       wp_enqueue_script(
        'artisan-cpt-script', // Handle for the JS file
        plugin_dir_url(__FILE__) . 'artisan-cpt.js', // Path to the JS file
        array('jquery'), // Dependencies (uses jQuery)
        '1.0.0', // Version
        true // Load in footer
    );

}
add_action('admin_enqueue_scripts', 'enqueue_admin_styles');


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
        'work_throughout_netherlands'  => array( 'type' => 'boolean','single' => true, 'show_in_rest' => true ),
        'latitude'                 => array( 'type' => 'number', 'single' => true, 'show_in_rest' => true ),
        'longitude'                => array( 'type' => 'number', 'single' => true, 'show_in_rest' => true ),
        

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
        

        'artisan_status'            => array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ),

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
        'longitude',
        'latitude',
        'distance',
        'business_license',
        'description',
    );
    // Boolean checkboxes
    $bool_fields = array('subscribe','work_throughout_netherlands');

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

function my_artisan_meta_box_callback($post) {
    // Use nonce for verification
    wp_nonce_field('my_artisan_save_meta_box_data', 'my_artisan_meta_box_nonce');

    // Retrieve existing values
    $meta_fields = [
        'trade' => get_post_meta($post->ID, 'trade', true),
        'zip_code' => get_post_meta($post->ID, 'zip_code', true),
        'email' => get_post_meta($post->ID, 'email', true),
        'first_name' => get_post_meta($post->ID, 'first_name', true),
        'last_name' => get_post_meta($post->ID, 'last_name', true),
        'phone' => get_post_meta($post->ID, 'phone', true),
        'subscribe' => get_post_meta($post->ID, 'subscribe', true),
        'selected_trades' => maybe_unserialize(get_post_meta($post->ID, 'selected_trades', true)),
        'distance' => get_post_meta($post->ID, 'distance', true),
        'work_throughout_netherlands' => get_post_meta($post->ID, 'work_throughout_netherlands', true),
        'latitude' => get_post_meta($post->ID, 'latitude', true),
        'longitude' => get_post_meta($post->ID, 'longitude', true),
        'professional_status' => get_post_meta($post->ID, 'professional_status', true),
        'gisa_number' => get_post_meta($post->ID, 'gisa_number', true),
        'company_name' => get_post_meta($post->ID, 'company_name', true),
        'address' => get_post_meta($post->ID, 'address', true),
        'business_zip_code' => get_post_meta($post->ID, 'business_zip_code', true),
        'city' => get_post_meta($post->ID, 'city', true),
        'business_license_url' => get_post_meta($post->ID, 'business_license_url', true),
        'description' => get_post_meta($post->ID, 'description', true),
    ];

    // Fetch assigned trades for the current post
    $assigned_trades = wp_get_post_terms($post->ID, 'global_services', ['fields' => 'names']);

    echo '<div class="artisan-meta-box-container">';

    // Step 1: Basic Details
    echo '<div class="artisan-meta-group">';
    echo '<div class="artisan-meta-group-title">Step 1: Basic Details</div>';
    echo '<div class="artisan-meta-group-content">';
    echo '<label>Trade</label><input type="text" name="trade" value="' . esc_attr($meta_fields['trade']) . '" class="regular-text" />';
    echo '<label>Zip Code</label><input type="text" name="zip_code" value="' . esc_attr($meta_fields['zip_code']) . '" />';
    echo '<label>Email</label><input type="email" name="email" value="' . esc_attr($meta_fields['email']) . '" class="regular-text" />';
    echo '</div></div>';

    // Step 2: Personal Information
    echo '<div class="artisan-meta-group">';
    echo '<div class="artisan-meta-group-title">Step 2: Personal Information</div>';
    echo '<div class="artisan-meta-group-content">';
    echo '<label>First Name</label><input type="text" name="first_name" value="' . esc_attr($meta_fields['first_name']) . '" />';
    echo '<label>Last Name</label><input type="text" name="last_name" value="' . esc_attr($meta_fields['last_name']) . '" />';
    echo '<label>Phone</label><input type="text" name="phone" value="' . esc_attr($meta_fields['phone']) . '" />';
    echo '<label>Subscribe</label><input type="checkbox" name="subscribe" value="1" ' . checked($meta_fields['subscribe'], true, false) . ' />';
    echo '</div></div>';

    // Step 3: Selected Trades
    echo '<div class="artisan-meta-group">';
    echo '<div class="artisan-meta-group-title">Step 3: Selected Trades</div>';
    echo '<div class="artisan-meta-group-content">';
    echo '<label>Selected Trades</label>';

    if (!is_wp_error($assigned_trades) && !empty($assigned_trades)) {
        echo '<div class="trade-bubbles">';
        foreach ($assigned_trades as $trade) {
            echo '<span class="trade-bubble">' . esc_html($trade) . '</span>';
        }
        echo '</div>';
    } else {
        echo '<p>No trades assigned.</p>';
    }

    echo '</div></div>';

    // Step 4: Work Details
    echo '<div class="artisan-meta-group">';
    echo '<div class="artisan-meta-group-title">Step 4: Work Details</div>';
    echo '<div class="artisan-meta-group-content">';
    echo '<label>Distance (km)</label><input type="number" name="distance" value="' . esc_attr($meta_fields['distance']) . '" />';
    echo '<label>Latitude</label><input type="text" name="latitude" value="' . esc_attr($meta_fields['latitude']) . '"  />';
    echo '<label>Longitude</label><input type="text" name="longitude" value="' . esc_attr($meta_fields['longitude']) . '"  />';
    echo '<label>Work Throughout netherlands</label><input type="checkbox" name="work_throughout_netherlands" value="1" ' . checked($meta_fields['work_throughout_netherlands'], true, false) . ' />';
    echo '</div></div>';

    // Step 5: Professional Information
    echo '<div class="artisan-meta-group">';
    echo '<div class="artisan-meta-group-title">Step 5: Professional Information</div>';
    echo '<div class="artisan-meta-group-content">';
    echo '<label>Professional Status</label><input type="text" name="professional_status" value="' . esc_attr($meta_fields['professional_status']) . '" class="widefat" />';
    echo '</div></div>';

    // Step 6: Business Information
    echo '<div class="artisan-meta-group">';
    echo '<div class="artisan-meta-group-title">Step 6: Business Information</div>';
    echo '<div class="artisan-meta-group-content">';
    echo '<label>GISA Number</label><input type="text" name="gisa_number" value="' . esc_attr($meta_fields['gisa_number']) . '" />';
    echo '<label>Company Name</label><input type="text" name="company_name" value="' . esc_attr($meta_fields['company_name']) . '" />';
    echo '<label>Address</label><input type="text" name="address" value="' . esc_attr($meta_fields['address']) . '" />';
    echo '<label>Business Zip Code</label><input type="text" name="business_zip_code" value="' . esc_attr($meta_fields['business_zip_code']) . '" />';
    echo '<label>City</label><input type="text" name="city" value="' . esc_attr($meta_fields['city']) . '" />';
    echo '</div></div>';

    // Step 7: Business License
    echo '<div class="artisan-meta-group">';
    echo '<div class="artisan-meta-group-title">Step 7: Business License</div>';
    echo '<div class="artisan-meta-group-content">';

    if ( ! empty($meta_fields['business_license_url']) ) {
        // Check if the file is an image
        $file_type = wp_check_filetype($meta_fields['business_license_url']);
        $is_image = in_array($file_type['ext'], ['jpg', 'jpeg', 'png', 'gif']);

        
        echo '<label>Business License File</label>';
        
        if ( $is_image ) {
            // Display the image preview
            echo '<div class="kz-business-license-admin-preview">
                    <img src="' . esc_url($meta_fields['business_license_url']) . '" alt="Business License Preview">
                  </div>';
        }
    
        echo '<a href="' . esc_url($meta_fields['business_license_url']) . '" target="_blank" class="kz-view-license">View License</a>';

    } else {
        echo '<label>Business License File</label><span>No file uploaded.</span>';
    }

    echo '</div></div>';


    // Step 8: Description
    echo '<div class="artisan-meta-group">';
    echo '<div class="artisan-meta-group-title">Step 8: Description</div>';
    echo '<div class="artisan-meta-group-content">';
    echo '<label>Description</label><textarea name="description" rows="4" cols="60">' . esc_textarea($meta_fields['description']) . '</textarea>';
    echo '</div></div>';

    echo '</div>';
}



function add_artisan_status_meta_box() {
    add_meta_box(
        'artisan_status_meta_box',
        __( 'Artisan Status', 'textdomain' ),
        'render_artisan_status_meta_box',
        'artisan',      // Post type
        'side',         // Position (Right sidebar under Publish box)
        'high'          // Priority
    );
}
add_action( 'add_meta_boxes', 'add_artisan_status_meta_box' );


function render_artisan_status_meta_box( $post ) {
    $status = get_post_meta( $post->ID, 'artisan_status', true );
    $options = array(
        'pending'   => __( 'Pending', 'textdomain' ),
        'approved'  => __( 'Approved', 'textdomain' ),
        'rejected'  => __( 'Rejected', 'textdomain' ),
    );

    // Security nonce field
    wp_nonce_field( 'save_artisan_status_meta_box', 'artisan_status_nonce' );

    echo '<select name="artisan_status" id="artisan_status" class="widefat">';
    foreach ( $options as $value => $label ) {
        echo '<option value="' . esc_attr( $value ) . '" ' . selected( $status, $value, false ) . '>' . esc_html( $label ) . '</option>';
    }
    echo '</select>';
}



function save_artisan_status_meta_box( $post_id ) {
    // Security checks
    if ( ! isset( $_POST['artisan_status_nonce'] ) || 
         ! wp_verify_nonce( $_POST['artisan_status_nonce'], 'save_artisan_status_meta_box' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Save the artisan status
    if ( isset( $_POST['artisan_status'] ) ) {
        update_post_meta( $post_id, 'artisan_status', sanitize_text_field( $_POST['artisan_status'] ) );
    }
}
add_action( 'save_post', 'save_artisan_status_meta_box' );
