<?php

// 1) Enqueue front-end assets
add_action( 'wp_enqueue_scripts', function(){
    if ( ! is_user_logged_in() ) {
        // SweetAlert2
        wp_enqueue_script( 'swal2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', [], null, true );

        // Our login form styles
        wp_enqueue_style(
            'artisan-login-style',
            plugin_dir_url(__FILE__).'css/custom-login-form.css',
            [],
            '1.0'
        );

        // Our login form JS
        wp_enqueue_script(
            'artisan-login-js',
            plugin_dir_url(__FILE__).'js/custom-login-form.js',
            ['jquery','swal2'],
            '1.0',
            true
        );

        $redirect_slug = get_option( 'artisan_login_redirect_url', '' );
        $current_url = home_url( add_query_arg( null, null ) ); // Current URL

        // Pass AJAX URL into JS
        wp_localize_script( 'artisan-login-js', 'ArtisanLogin', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'redirect_url' => $redirect_slug ? home_url( '/' . $redirect_slug ) : home_url(),
            'current_url' => $current_url, // Add the current URL
            'signup_url' =>  trim( get_option( 'artisan_register_redirect_url', 'register' ), '/' ),
        ]);
    }
});

add_action( 'wp_head', function(){
  echo '<link href="https://fonts.googleapis.com/css2?family=Iner:wght@400;600&display=swap" rel="stylesheet">';
});

// 2) AJAX handler for login
add_action( 'wp_ajax_nopriv_custom_ajax_login', function(){
    // check nonce
    if ( empty( $_POST['cl_login_nonce'] ) ||
         ! wp_verify_nonce( $_POST['cl_login_nonce'], 'cl_login_action' ) ) {
        wp_send_json_error( 'Security check failed.' );
    }

    if ( isset( $_POST['cl_email'] ) && isset( $_POST['cl_password'] ) ) {
    // collect & sanitize
    $email    = sanitize_text_field( wp_unslash( $_POST['cl_email'] ?? '' ) );
    $password = wp_unslash( $_POST['cl_password'] ?? '' );
    $remember = ! empty( $_POST['cl_remember'] );

    $remember = isset( $_POST['cl_remember'] ) && $_POST['cl_remember'] === 'on' ? true : false;

    $creds = [
        'user_login'    => $email,
        'user_password' => $password,
        'remember'      => $remember,
    ];

    $user = wp_signon( $creds, is_ssl() );
    if ( is_wp_error( $user ) ) {
        wp_send_json_error( $user->get_error_message() );
    }

    // success
    wp_send_json_success( 'You’re now logged in.' );
  }
});

// 3) Shortcode to render the form
if ( ! function_exists( 'artisan_login_form_shortcode' ) ) {
    function artisan_login_form_shortcode( $atts ) {
        if ( is_user_logged_in() ) {
          $redirect_slug = trim( get_option( 'artisan_login_redirect_url', '' ), '/' );
          ?>
          <script type="text/javascript">
          document.addEventListener('DOMContentLoaded', function(){
            var origin = window.location.origin;
            var path   = window.location.pathname;
            if ( /^\/login\/?$/.test(path) ) {
              var slug = "<?php echo esc_js( $redirect_slug ); ?>";
              console.log( slug );
              var target = origin + ( slug ? '/' + slug + '/' : '/' );
              console.log( origin );
              window.location.href = target;
            }
          });
          </script>
          <?php
          exit; 
      }      
        // pull your theme’s custom logo if set
// 1) Paths to your bundled image
$bundled_path = plugin_dir_path(__FILE__). '../assets/images/logo.png';
$bundled_url  = plugin_dir_url(__FILE__). '../assets/images/logo.png';

// 2) Try to find it in the Media Library
// $attachment_id = attachment_url_to_postid( $bundled_url );

// if ( ! $attachment_id && file_exists( $bundled_path ) ) {
//     // 3a) Read the file
//     $file_contents = file_get_contents( $bundled_path );
//     if ( false !== $file_contents ) {
//         // 3b) Upload to WP uploads
//         $upload = wp_upload_bits( basename( $bundled_path ), null, $file_contents );
//         if ( empty( $upload['error'] ) ) {
//             // 3c) Insert into Media Library
//             require_once ABSPATH . 'wp-admin/includes/file.php';
//             require_once ABSPATH . 'wp-admin/includes/media.php';
//             require_once ABSPATH . 'wp-admin/includes/image.php';

//             $wp_filetype = wp_check_filetype( $upload['file'], null );
//             $attachment = [
//                 'guid'           => $upload['url'],
//                 'post_mime_type' => $wp_filetype['type'],
//                 'post_title'     => sanitize_file_name( basename( $upload['file'] ) ),
//                 'post_content'   => '',
//                 'post_status'    => 'inherit',
//             ];
//             $attachment_id = wp_insert_attachment( $attachment, $upload['file'] );
//             if ( ! is_wp_error( $attachment_id ) ) {
//                 $meta = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
//                 wp_update_attachment_metadata( $attachment_id, $meta );
//             }
//         }
//     }
// }

// // 4) Finally, get the URL to use in <img>
// if ( $attachment_id ) {
//     $logo_url = wp_get_attachment_image_url( $attachment_id, 'full' );
// } else {
//     // fallback (should only happen if something went wrong)
//     $logo_url = $bundled_url;
// }

        // your static description text
        $desc_text = 'Voer uw e-mailadres en wachtwoord in om in te loggen op uw account.';

        ob_start();
        ?>
        <div class="artisan-login-wrap">

          <?php if ( $bundled_url ): ?>
            <div class="artisan-login-logo">
            <a href="#"  id="logo-link">
              <img class="logo-img" src="<?php echo esc_url( $bundled_url ); ?>"
                   alt="<?php echo esc_attr( get_bloginfo('name') ); ?>">
          </a>
            </div>
          <?php endif; ?>

          <?php if ( $desc_text ): ?>
            <p class="artisan-login-desc"><?php echo esc_html( $desc_text, 'textdomain' ); ?></p>
          <?php endif; ?>

          <form method="post" class="artisan-login-form">
            <?php wp_nonce_field( 'cl_login_action', 'cl_login_nonce' ); ?>

            <label for="cl_email"><?php esc_html_e("Email" , 'textdomain') ?> </label>
            <input type="text" id="cl_email" name="cl_email"
                   placeholder="<?php esc_html_e("Please enter your email address" , 'textdomain') ?>" required>

            <label for="cl_password"><?php esc_html_e("Password", 'textdomain') ?> </label>
            <div class="artisan-password-wrapper">
              <input type="password" id="cl_password" name="cl_password"
                     placeholder="<?php esc_html_e("Please enter your password", 'textdomain')?>"" required>
              <span class="artisan-toggle-pass" title="Show/Hide Password">&#128065;</span>
            </div>

            <div class="artisan-options">
              <label>
              <input type="checkbox" name="cl_remember" <?php checked( isset( $_POST['cl_remember'] ) ); ?>> Remember me
              </label>
              <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"
                 class="artisan-forgot">
                 <?php esc_html_e("Forget password?", 'textdomain') ?>
              </a>
            </div>

            <button type="submit" class="artisan-submit"><?php esc_html_e("Sign In", 'textdomain') ?></button>
          </form>

          <div class="artisan-signup">
          <?php esc_html_e("Don’t have an account?", 'textdomain') ?>
            <a href="#" class="artisan-signup-link"><?php esc_html_e("Sign up" , 'textdomain') ?> </a>
          </div>
        </div>
        <?php
        return ob_get_clean();
    }
    add_shortcode( 'custom_login_form', 'artisan_login_form_shortcode' );
}
