<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function enqueue_job_list_assets() {
    wp_enqueue_style(
        'job-listings-css',
        plugin_dir_url( __FILE__ ) . 'assets/css/job-list.css',
        array(),
        '1.0.0',
        'all'
    );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script(
        'job-listings-js',
        plugin_dir_url( __FILE__ ) . 'assets/js/job-list.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'enqueue_job_list_assets' );

/**
 * Shortcode to display jobs for artisans
 */
function artisan_job_listings_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<p>You must be logged in to view available jobs.</p>';
    }
    $user_id = get_current_user_id();
    $artisan_post = get_posts( array(
        'post_type'   => 'artisan',
        'numberposts' => 1,
        'post_status' => 'publish',
        'author'      => $user_id,
    ) );
    if ( empty( $artisan_post ) ) {
        return '<p>No artisan profile found for your account.</p>';
    }
    $artisan_post_id = $artisan_post[0]->ID;
    $artisan_trades = wp_get_post_terms( $artisan_post_id, 'global_services', array( 'fields' => 'names' ) );
    if ( empty( $artisan_trades ) ) {
        return '<p>No trade type found for your artisan profile.</p>';
    }
    $artisan_lat      = get_post_meta( $artisan_post_id, 'latitude', true );
    $artisan_lng      = get_post_meta( $artisan_post_id, 'longitude', true );
    $artisan_zip      = get_post_meta( $artisan_post_id, 'zip_code', true );
    $artisan_coverage = get_post_meta( $artisan_post_id, 'distance', true ); // Coverage radius
    // Check if artisan works throughout Netherlands
    $covers_all = get_post_meta( $artisan_post_id, 'work_throughout_netherlands', true );
    
    // Determine filter (default: 'all')
    $filter = isset( $_GET['job_filter'] ) ? sanitize_text_field( $_GET['job_filter'] ) : 'all';

    $args = array(
        'post_type'      => 'service_job',
        'posts_per_page' => -1,
        'tax_query'      => array(
            array(
                'taxonomy' => 'global_services',
                'field'    => 'name',
                'terms'    => $artisan_trades,
            ),
        ),
    );
    $jobs_query = new WP_Query( $args );
    if ( ! $jobs_query->have_posts() ) {
        return '<p>No jobs available matching your trade.</p>';
    }

    $all_jobs     = array();
    $matched_jobs = array();
    while ( $jobs_query->have_posts() ) {
        $jobs_query->the_post();
        $job_id        = get_the_ID();
        $job_title     = get_the_title();
        $job_zip       = get_post_meta( $job_id, 'job_zip_code', true );
        $job_city      = get_post_meta( $job_id, 'city', true );
        $job_lat       = get_post_meta( $job_id, 'latitude', true );
        $job_lng       = get_post_meta( $job_id, 'longitude', true );
        $client_name   = get_post_meta( $job_id, 'client_name', true );
        $client_email  = get_post_meta( $job_id, 'client_email', true );
        $client_phone  = get_post_meta( $job_id, 'client_phone', true );
        $published_ago = human_time_diff( get_post_time( 'U', true, $job_id ), current_time( 'timestamp' ) ) . ' ago';
        
        $job_details       = get_post_meta( $job_id, 'job_details_json', true );
        $job_details_array = json_decode( $job_details, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            error_log( "JSON Decode Error: " . json_last_error_msg() );
            $job_details_array = array();
        }
        $job_excerpt   = get_the_excerpt( $job_id );
        $service_types = wp_get_post_terms( $job_id, 'global_services', array( 'fields' => 'names' ) );
        $distance      = null;
        $distance_info = '';
        if ( ! empty( $artisan_lat ) && ! empty( $artisan_lng ) && ! empty( $job_lat ) && ! empty( $job_lng ) ) {
            $distance      = haversine_distance( floatval( $artisan_lat ), floatval( $artisan_lng ), floatval( $job_lat ), floatval( $job_lng ) );
            $distance_info = round( $distance, 2 ) . ' km';
        }
        // Build job data with new keys 'posted' and 'distance'
        $job_data = array(
            'id'               => $job_id,
            'title'            => $job_title,
            'posted'           => $published_ago,   // new key for overlay
            'excerpt'          => $job_excerpt,
            'city'             => $job_city,
            'distance'         => $distance_info,   // new key for overlay
            'numeric_distance' => $distance,
            'client_name'      => $client_name,
            'client_email'     => $client_email,
            'client_phone'     => $client_phone,
            'service_type'     => $service_types,
            'details'          => $job_details_array,
        );
        $all_jobs[] = $job_data;
        if ( $covers_all ) {
            $matched_jobs[] = $job_data;
        } elseif ( ! empty( $artisan_coverage ) && $distance !== null && floatval( $distance ) <= floatval( $artisan_coverage ) ) {
            $matched_jobs[] = $job_data;
        }
    }
    wp_reset_postdata();

    // Determine which jobs to display based on the filter:
    $jobs_to_display = ( $filter === 'covered' ) ? $matched_jobs : $all_jobs;
    ob_start();
    ?>
    <main class="artisan-job-listings" role="main">
        <!-- Header Row with Title and Dropdown Filter -->
        <div class="filter-header">
            <h2>Your Matches</h2>
            <form method="get" id="job-filter-form">
                <?php 
                    // Preserve other GET parameters
                    foreach ( $_GET as $key => $value ) {
                        if ( $key !== 'job_filter' ) {
                            echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
                        }
                    }
                    $filter_options = array(
                        'all'     => 'Show All Jobs',
                        'covered' => 'My Covered Areas',
                    );
                    echo '<label for="job_filter" class="visually-hidden">Filter Jobs</label>';
                    render_select_field(
                        'job_filter',
                        'job_filter',
                        '', // No visible label needed
                        $filter_options,
                        $filter,
                        false,
                        array( 'onchange' => "document.getElementById('job-filter-form').submit();", 'class' => 'job-filter-select' )
                    );
                ?>
            </form>
        </div>
        <hr class="divider">
        <!-- Job Listings -->
        <section class="jobs-section">
            <?php if ( ! empty( $jobs_to_display ) ) : ?>
                <ul class="job-list" role="list">
                    <?php foreach ( $jobs_to_display as $job ) : ?>
                        <li class="job-item" data-job-details='<?php echo esc_attr( wp_json_encode( $job ) ); ?>'>
                            <div class="job-card">
                                <div class="job-info">
                                    <p class="meta"><?php echo esc_html( $job['posted'] ); ?></p>
                                    <h3 class="job-title"><?php echo esc_html( $job['title'] ); ?></h3>
                                    <?php if ( ! empty( $job['excerpt'] ) ) : ?>
                                        <p class="excerpt"><?php echo esc_html( $job['excerpt'] ); ?></p>
                                    <?php endif; ?>
                                    <?php if ( $job['city'] || $job['distance'] ) : ?>
                                        <p class="meta details">
                                            <?php if ( $job['city'] ) : ?>
                                                <span class="location"><strong>Location:</strong> <?php echo esc_html( $job['city'] ); ?></span>
                                            <?php endif; ?>
                                            <?php if ( $job['distance'] ) : ?>
                                                <span class="distance"><strong>Distance:</strong> <?php echo esc_html( $job['distance'] ); ?></span>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ( $job['client_name'] ) : ?>
                                        <p class="client-name"><?php echo esc_html( $job['client_name'] ); ?></p>
                                    <?php endif; ?>
                                </div>
                                <button class="view-job-button" aria-label="View brief for <?php echo esc_attr( $job['title'] ); ?>">View brief</button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="no-results">No jobs to display.</p>
            <?php endif; ?>
        </section>
    </main>
    <!-- OVERLAY Markup -->
    <div id="overlay-backdrop" class="overlay-backdrop hidden" tabindex="-1"></div>
    <div id="job-detail-overlay" class="overlay hidden" role="dialog" aria-modal="true" aria-labelledby="overlay-title">
        <div class="overlay__header">
            <h2 id="overlay-title">Job Preview</h2>
            <button class="close-overlay" aria-label="Close">&times;</button>
        </div>
        <div class="overlay__tabs" role="tablist">
            <button class="overlay-tab active" data-tab="brief-tab" role="tab" aria-selected="true">Brief</button>
            <button class="overlay-tab" data-tab="client-info-tab" role="tab" aria-selected="false">Client Info</button>
        </div>
        <div class="overlay__body">
            <div class="tab-content active" id="brief-tab" role="tabpanel">
                <h2 id="overlay-job-title"></h2>
                <p class="meta"><strong>Posted:</strong> <span id="overlay-posted-time"></span></p>
                <p class="meta"><strong>Location:</strong> <span id="overlay-location"></span><br/><strong>Distance:</strong> <span id="overlay-distance"></span></p>
                <strong>Service Type:</strong> <p id="overlay-service-type"></p>
                <p id="overlay-excerpt"></p>
                <h3>Details</h3>
                <table id="overlay-job-details"></table>
            </div>
            <div class="tab-content" id="client-info-tab" role="tabpanel">
                <h3>Client Info</h3>
                <p><strong>Name:</strong> <span id="overlay-client-name"></span></p>
                <p><strong>Email:</strong> <span id="overlay-client-email"></span></p>
                <p><strong>Phone:</strong> <span id="overlay-client-phone"></span></p>
            </div>
        </div>
    </div>
    <?php
    $output = ob_get_clean();
    return $output;
}
add_shortcode( 'artisan_jobs', 'artisan_job_listings_shortcode' );
?>
