<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function enqueue_job_list_assets() {
    // Enqueue Job Listings CSS
    wp_enqueue_style(
        'job-listings-css',
        plugin_dir_url(__FILE__) . 'assets/css/job-list.css',
        array(),
        '1.0.0',
        'all'
    );

    // Enqueue jQuery (if not already included by WordPress)
    wp_enqueue_script('jquery');

    // Enqueue Job Listings JS
    wp_enqueue_script(
        'job-listings-js',
        plugin_dir_url(__FILE__) . 'assets/js/job-list.js',
        array('jquery'),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_job_list_assets');


/**
 * Shortcode to display jobs for artisans
 */
function artisan_job_listings_shortcode() {
    // Ensure user is logged in
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to view available jobs.</p>';
    }

    $user_id = get_current_user_id();

    // Get the artisan post ID linked to this user
    $artisan_post = get_posts([
        'post_type'   => 'artisan',
        'numberposts' => 1,
        'post_status' => 'publish',
        'author'      => $user_id,
    ]);

    if (empty($artisan_post)) {
        return '<p>No artisan profile found for your account.</p>';
    }

    $artisan_post_id = $artisan_post[0]->ID;

    // Get artisan's assigned trade(s) from taxonomy
    $artisan_trades = wp_get_post_terms($artisan_post_id, 'global_services', ['fields' => 'names']);

    if (empty($artisan_trades)) {
        return '<p>No trade type found for your artisan profile.</p>';
    }

    // Get artisan's location details (latitude, longitude, zip code)
    $artisan_lat = get_post_meta($artisan_post_id, 'latitude', true);
    $artisan_lng = get_post_meta($artisan_post_id, 'longitude', true);
    $artisan_zip = get_post_meta($artisan_post_id, 'zip_code', true);

    // Query service jobs based on trade type
    $args = [
        'post_type'      => 'service_job',
        'posts_per_page' => -1,
        'tax_query'      => [
            [
                'taxonomy' => 'global_services',
                'field'    => 'name',
                'terms'    => $artisan_trades,
            ],
        ],
    ];

    $jobs = new WP_Query($args);

    if (!$jobs->have_posts()) {
        return '<p>No jobs available matching your trade and location.</p>';
    }

    ob_start();
    ?>
    <div class="artisan-job-listings">
        <h2>Your Matches</h2>
        <ul class="job-list">
            <?php while ($jobs->have_posts()) : $jobs->the_post(); ?>
                <?php
                $job_id       = get_the_ID();
                $job_title    = get_the_title();
                $job_zip      = get_post_meta($job_id, 'job_zip_code', true);
                $job_city     = get_post_meta($job_id, 'city', true);
                $job_lat      = get_post_meta($job_id, 'latitude', true);
                $job_lng      = get_post_meta($job_id, 'longitude', true);
                $client_name  = get_post_meta($job_id, 'client_name', true);
                $client_email = get_post_meta($job_id, 'client_email', true);
                $client_phone = get_post_meta($job_id, 'client_phone', true);

                // "published time in readable manner" => use WordPress "time ago"
                // If the job has a published date, we can use get_post_time().
                $published_ago = human_time_diff(get_post_time('U', true, $job_id), current_time('timestamp')) . ' ago';

                // If there's a stored Q&A in JSON
                $job_details  = get_post_meta($job_id, 'job_details_json', true);
                $job_details_array = json_decode($job_details, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("JSON Decode Error: " . json_last_error_msg());
                    $job_details_array = [];
                }

                // Extract or define a short description (optional). 
                // For example, from the job's post content or a meta field:
                $job_excerpt = get_the_excerpt($job_id); // If using excerpt
                // Or something from custom meta:
                // $job_excerpt = get_post_meta($job_id, 'short_description', true);

                // Terms from global_services for "service_type"
                $service_types = wp_get_post_terms($job_id, 'global_services', ['fields' => 'names']);

                // Calculate distance if we have coordinates
                $distance_info = '';
                if (!empty($artisan_lat) && !empty($artisan_lng) && !empty($job_lat) && !empty($job_lng)) {
                    $distance = haversine_distance(floatval($artisan_lat), floatval($artisan_lng), floatval($job_lat), floatval($job_lng));
                    $distance_info = round($distance, 2) . ' km';
                }

                // Build data object for the overlay
                $job_data = [
                    'title'         => $job_title,
                    'time_ago'      => $published_ago, // We'll display in overlay
                    'city'          => $job_city,
                    'distance'      => $distance_info,
                    'client_name'   => $client_name,
                    'client_email'  => $client_email,
                    'client_phone'  => $client_phone,
                    'service_type'  => $service_types,  // Could be array
                    'details'       => $job_details_array,  // Q&A
                    'excerpt'       => $job_excerpt, // optional short description
                ];
                ?>
                <!-- Single job item -->
                <li class="job-item" data-job-details='<?php echo esc_attr(wp_json_encode($job_data)); ?>'>
                    <div class="job-card">
                        <div class="job-info">

                            <!-- Published "time ago" in smaller text -->
                            <p class="meta" style="margin:0;">
                                <?php echo esc_html($published_ago); ?>
                            </p>

                            <!-- Title -->
                            <h3><?php echo esc_html($job_title); ?></h3>

                            <!-- Optional excerpt or short description -->
                            <?php if (!empty($job_excerpt)) : ?>
                                <p class="excerpt">
                                    <?php echo esc_html($job_excerpt); ?>
                                </p>
                            <?php endif; ?>

                            <!-- Location . Distance (label-value style, but short) -->
                            <?php if ($job_city || $distance_info) : ?>
                                <p class="meta">
                                    <?php if ($job_city) : ?>
                                        <strong>Location:</strong> <?php echo esc_html($job_city); ?>
                                    <?php endif; ?>
                                    <?php if ($distance_info) : ?>
                                        &middot; <strong>Distance:</strong> <?php echo esc_html($distance_info); ?>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>

                            <!-- Client name -->
                            <?php if ($client_name) : ?>
                                <p class="client-name"><?php echo esc_html($client_name); ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- View brief button -->
                        <button class="view-job-button">View brief</button>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <!-- BACKDROP for dimming -->
    <div id="overlay-backdrop" class="overlay-backdrop hidden"></div>

    <!-- SLIDE-IN OVERLAY -->
    <div id="job-detail-overlay" class="overlay hidden">
        <div class="overlay__header">
            <h2>Preview</h2>
            <button class="close-overlay" aria-label="Close">&times;</button>
        </div>

        <!-- Tabs for "Brief" and "Client Info" -->
        <div class="overlay__tabs">
            <button class="overlay-tab active" data-tab="brief-tab">Brief</button>
            <button class="overlay-tab" data-tab="client-info-tab">Client Info</button>
        </div>

        <div class="overlay__body">
            <!-- Brief Tab Content -->
            <div class="tab-content" id="brief-tab">
                <!-- Title -->
                <h2 id="overlay-job-title"></h2>

                <!-- "Posted: x time ago" + label-value style -->
                <p class="meta">
                    <strong>Posted:</strong> <span id="overlay-posted-time"></span>
                </p>
                <p class="meta">
                    <strong>Location:</strong> <span id="overlay-location"></span><br/>
                    <strong>Distance:</strong> <span id="overlay-distance"></span><br/>
                </p>

                <strong>Service Type:</strong> <p id="overlay-service-type"></p>
                <p id="overlay-excerpt"></p>

                <!-- Q&A Details -->
                <h3>Details</h3>
                <table id="overlay-job-details"></table>
            </div>

            <!-- Client Info Tab Content -->
            <div class="tab-content" id="client-info-tab" style="display:none;">
                <h3>Client Info</h3>
                <p><strong>Name:</strong> <span id="overlay-client-name"></span></p>
                <p><strong>Email:</strong> <span id="overlay-client-email"></span></p>
                <p><strong>Phone:</strong> <span id="overlay-client-phone"></span></p>
            </div>
        </div>
    </div>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('artisan_jobs', 'artisan_job_listings_shortcode');
