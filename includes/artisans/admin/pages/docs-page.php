<?php

function ws_render_docs_page() {
    error_log("Docs Page");
    ?>
    <h2><?php esc_html_e('General Information', 'textdomain'); ?></h2>
    <p><?php esc_html_e('This is the general documentation for the plugin. It provides an overview and instructions on how to use the plugin.', 'textdomain'); ?></p>
    
    <h3><?php esc_html_e('Shortcodes', 'textdomain'); ?></h3>
    <p><?php esc_html_e('Below are the available shortcodes for the Artisan modules. Click "Copy" to copy the shortcode to your clipboard.', 'textdomain'); ?></p>

    <h4><?php esc_html_e('Registration Form Shortcode', 'textdomain'); ?></h4>
<div class="shortcode-box">
    <code class="shortcode-text">[artisan_registration_form]</code>
    <button type="button" class="copy-button"><?php esc_html_e('Copy', 'textdomain'); ?></button>
    <span class="copy-message" style="display:none;"><?php esc_html_e('Copied to clipboard!', 'textdomain'); ?></span>
</div>

<h4><?php esc_html_e('Jobs Listing Shortcode', 'textdomain'); ?></h4>
<div class="shortcode-box">
    <code class="shortcode-text">[artisan_jobs]</code>
    <button type="button" class="copy-button"><?php esc_html_e('Copy', 'textdomain'); ?></button>
    <span class="copy-message" style="display:none;"><?php esc_html_e('Copied to clipboard!', 'textdomain'); ?></span>
</div>

<h4><?php esc_html_e('Service Form Shortcode', 'textdomain'); ?></h4>
<div class="shortcode-box">
    <code class="shortcode-text">[service_form]</code>
    <button type="button" class="copy-button"><?php esc_html_e('Copy', 'textdomain'); ?></button>
    <span class="copy-message" style="display:none;"><?php esc_html_e('Copied to clipboard!', 'textdomain'); ?></span>
</div>

<h4><?php esc_html_e('Service Search Shortcode', 'textdomain'); ?></h4>
<div class="shortcode-box">
    <code class="shortcode-text">[service_search]</code>
    <button type="button" class="copy-button"><?php esc_html_e('Copy', 'textdomain'); ?></button>
    <span class="copy-message" style="display:none;"><?php esc_html_e('Copied to clipboard!', 'textdomain'); ?></span>
</div>

    <?php
}
