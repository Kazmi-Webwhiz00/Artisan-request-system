<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Register shortcode
function render_service_search_form() {
    ob_start(); ?>
    <div id="service-search-container">
        <div class="search-box">
            <input type="text" id="service-search-input" placeholder="z. B: Malerarbeiten" />
            <button id="service-search-clear" aria-label="Clear Search">&times;</button>
            <button id="service-search-submit" aria-label="Search">&#x2794;</button>
        </div>
        <ul id="service-search-results" class="search-results">
            <!-- Results will be populated here via AJAX -->
        </ul>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('service_search', 'render_service_search_form');

// Enqueue assets
function enqueue_service_search_assets() {
    wp_enqueue_style('service-search-css', plugin_dir_url(__FILE__) . 'service-search.css');
    wp_enqueue_script('service-search-js', plugin_dir_url(__FILE__) . 'service-search.js', ['jquery'], false, true);
    wp_localize_script('service-search-js', 'ajax_object_search', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_service_search_assets');

// Handle AJAX request
function fetch_service_forms() {
    $search_query = sanitize_text_field($_GET['query'] ?? '');

    $args = [
        'post_type' => 'service_form',
        'posts_per_page' => -1,
        's' => $search_query,
    ];

    $query = new WP_Query($args);
    $results = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $results[] = [
                'title' => get_the_title(),
                'link' => get_permalink(),
            ];
        }
    }

    wp_reset_postdata();
    wp_send_json($results);
}
add_action('wp_ajax_fetch_service_forms', 'fetch_service_forms');
add_action('wp_ajax_nopriv_fetch_service_forms', 'fetch_service_forms');
