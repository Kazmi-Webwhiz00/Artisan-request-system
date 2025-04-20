<div class="kw-settings-section">
<h2><?php esc_html_e('Custom  Redirect Link Settings', 'textdomain'); ?></h2>


    <div class="kw-settings-field">
    <div class="kw-settings-notice-box">
            <span class="kw-settings-icon">ⓘ</span>
            <div class="kw-settings-notice-content">
                <strong><?php esc_html_e('Note:', 'textdomain'); ?></strong>
                <?php
                $permalink_settings_url = admin_url('options-permalink.php');
                printf(
                    __('After changing the url, go to <strong><a href="%s" target="_blank">Settings > Permalinks</a></strong> and click "Save Changes" to update.', 'textdomain'),
                    esc_url($permalink_settings_url)
                );
                ?>
            </div>
        </div>
        
        <label for="artisan_custom_redirect_url"><?php esc_html_e('Custom Redirect URL', 'textdomain'); ?></label>
   <?php $redirect_url = get_option('artisan_custom_redirect_url', 'job-listing-page'); ?>
   <input
     type="text"
     id="artisan_custom_redirect_url"
     name="artisan_custom_redirect_url"
     value="<?php echo esc_attr( $redirect_url ); ?>"
     class="regular-text"
   >
        <p class="description"><?php esc_html_e('Set a custom URL slug for crosswords. Example: "artisan".', 'textdomain'); ?></p>
    </div>
</div>