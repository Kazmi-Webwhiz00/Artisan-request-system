<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . '../general-helpers/forms/text-field.php';
require_once plugin_dir_path( __FILE__ ) . '../general-helpers/forms/select-field.php';
require_once plugin_dir_path( __FILE__ ) . '../general-helpers/forms/email-field.php';
require_once plugin_dir_path( __FILE__ ) . '../general-helpers/forms/phone-field.php';
require_once plugin_dir_path( __FILE__ ) . '../general-helpers/forms/password-field.php';
require_once plugin_dir_path( __FILE__ ) . '../general-helpers/forms/checkbox-field.php';
require_once plugin_dir_path( __FILE__ ) . '../general-helpers/forms/radio-field.php';
require_once plugin_dir_path( __FILE__ ) . '../general-helpers/forms/grouped-field.php';
require_once plugin_dir_path( __FILE__ ) . '../general-helpers/forms/file-upload-field.php';


// Enqueue CSS for artisan registration form
function enqueue_artisan_registration_form_styles() {
    wp_enqueue_style(
        'artisan-registration-form-css', // Handle for the stylesheet
        plugin_dir_url(__FILE__) . 'artisan-registration-form.css', // Path to the CSS file
        array(), // Dependencies (if any)
        filemtime(plugin_dir_path(__FILE__) . 'artisan-registration-form.css') // Version based on file modification time
    );
}
add_action('wp_enqueue_scripts', 'enqueue_artisan_registration_form_styles');

// Add Leaflet.js for map functionality
add_action('wp_head', function() {
    ?>
    <!-- Leaflet.js CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <?php
});

// Function to render Step 1
function render_artisan_registration_step_1() {
    ?>
    <div class="form-step form-step-1 active">
        <h2>View orders from the region</h2>
        <?php
        // Render the trade select field using render_select_field
        render_select_field(
            'trade',                // Name
            'trade',                // ID
            'Trade',                // Label
            [                       // Options array
                '' => 'Select your trade',
                'well-builder' => 'Well Builder',
                'electrician' => 'Electrician',
                'plumber' => 'Plumber',
                // Add more trades as needed
            ],
            '',                     // Default selected value
            true                    // Required
        );

        // Render the zip code field using render_text_field
        render_text_field(
            'zip_code',             // Name
            'zip_code',             // ID
            'Zip Code',             // Label
            'Enter your zip code',  // Placeholder
            '',                     // Default value
            true                    // Required
        );
        // Render the email field using render_email_field
        render_email_field(
            'email',                // Name
            'email',                // ID
            'Email Address',        // Label
            'Enter your email address', // Placeholder
            '',                     // Default value
            true                    // Required
        );
        ?>
        
        <div class="form-group terms">
            <p>By clicking on “Register for free” you agree to Kazverse’s 
                <a href="#">terms and conditions</a>. Information about how we process your data can be found in our 
                <a href="#">privacy policy</a>.
            </p>
        </div>
        <button type="button" class="next-button" data-next-step="2">Register for free</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Step 1 specific JS logic (if any)
        });
    </script>
    <?php
}

// Function to render Step 2
function render_artisan_registration_step_2() {
    ?>
    <div class="form-step form-step-2">
        <h2>Create an Account</h2>
        <p>Enter your name exactly as it appears on your company documents.</p>
        <?php
        // Render the first name field
        render_text_field(
            'first_name',           // Name
            'first_name',           // ID
            'Company Owner',           // Label
            'First name',           // Placeholder
            '',                     // Default value
            true                    // Required
        );

        // Render the last name field
        render_text_field(
            'last_name',            // Name
            'last_name',            // ID
            '',            // Label
            'Last name',            // Placeholder
            '',                     // Default value
            true                    // Required
        );

        // Render the phone field
        render_phone_field(
            'phone',                // Name
            'phone',                // ID
            'Phone Number',         // Label
            'Enter your phone number', // Placeholder
            '',                     // Default value
            true,                   // Required
            '+43'                   // Phone prefix
        );

        // Render the password field using the render_password_field function
        render_password_field(
            'password',             // Name
            'password',             // ID
            'Password (at least 6 characters)', // Label
            'Create password',     // Placeholder
            '',                     // Default value
            true                    // Required
        );

        // Subscribe checkbox
        render_checkbox_field(
            'subscribe',           // Name
            'subscribe',           // ID
            'I would like to receive advertising about Kazverse services and offers by email, SMS, and/or telephone.', // Label
            false,                 // Checked (set to true if checked by default)
            true                   // Required
        );

        ?>

        <!-- Navigation buttons -->
        <button type="button" class="previous-button" data-previous-step="1">Back</button>
        <button type="button" class="next-button purple-btn" data-next-step="3">Continue</button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const showPasswordButton = document.querySelector('.show-password');
            const passwordField = document.getElementById('password');

            if (showPasswordButton && passwordField) {
                showPasswordButton.addEventListener('click', function () {
                    if (passwordField.type === 'password') {
                        passwordField.type = 'text';
                        showPasswordButton.textContent = 'Hide';
                    } else {
                        passwordField.type = 'password';
                        showPasswordButton.textContent = 'Show';
                    }
                });
            }
        });
    </script>
    <?php
}

// Function to render Step 3
function render_artisan_registration_step_3() {
    ?>
    <div class="form-step form-step-3">
        <!-- Progress Bar -->
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <h2>About You</h2>
        <p><strong>~ 2 min</strong></p>
        <p>Welcome! Let's go!</p>
        <p>We would like to get to know you better so that we can provide you with suitable jobs – in your area and tailored to your area of expertise.</p>
        <p>In this step, we ask you about your job, your professional status, and your location.</p>

        <button type="button" class="previous-button" data-previous-step="2">Back</button>
        <button type="button" class="next-button purple-btn" data-next-step="4">Continue</button>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // No specific JS for Step 3 yet
        });
    </script>
    <?php
}

// Function to render Step 4
function render_artisan_registration_step_4() {
    $trades = [
        'earthmoving and excavation companies',
        'tiler',
        'gardeners and landscapers',
        'electricians',
        'plumbers'
    ];
    ?>
    <div class="form-step form-step-4">
        <!-- Progress Bar -->
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <h2>Select up to five trades</h2>
        <p>Tell us your areas of expertise so we can send you the most relevant assignments.</p>

        <!-- Search Trades Input -->
        <div class="f4_form-group form-group">
            <label for="f4_trade_search">Search trades</label>
            <input type="text" class="form-control" id="f4_trade_search" placeholder="Search trades" />
        </div>

        <!-- Trades Cards with Checkboxes (using render_checkbox_field) -->
        <div class="f4_form-group form-group">
            <label for="f4_trade_select">Select Trade(s)</label>
            <div id="f4_trade_cards" class="f4_trade-cards">
                <?php
                foreach ($trades as $trade) {
                    // Render each trade as a checkbox field
                    render_checkbox_field(
                        'f4_trade_select[]',         // Name (with [] to make it an array)
                        'f4_trade_' . sanitize_title($trade), // ID
                        esc_html($trade),         // Label
                        false,                    // Checked (false by default)
                        true                      // Required
                    );
                }
                ?>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <button type="button" class="previous-button" data-previous-step="3">Back</button>
        <button type="button" class="next-button purple-btn" data-next-step="5">Continue</button>
    </div>
   
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Simple trade search functionality (for filtering the trades)
            const tradeSearchInput = document.getElementById('f4_trade_search');
            const tradeCards = document.getElementById('f4_trade_cards').querySelectorAll('.f4_trade-card');
            
            tradeSearchInput.addEventListener('input', function() {
                const filterValue = tradeSearchInput.value.toLowerCase();
                tradeCards.forEach(function(card) {
                    const tradeText = card.querySelector('label').textContent.toLowerCase();
                    if (tradeText.indexOf(filterValue) === -1) {
                        card.style.display = 'none';
                    } else {
                        card.style.display = 'flex';
                    }
                });
            });
        });
    </script>
    <?php
}

// Function to render Step 5
function render_artisan_registration_step_5() {
    ?>
    <div class="form-step form-step-5">
        <!-- Progress Bar -->
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <h2>In which area do you work?</h2>
        <p>Set maximum distance by gear.</p>

        <!-- Map displaying work area (using Mapbox or Leaflet) -->
        <div id="f5_work_area_map" style="height: 300px; width: 100%;"></div>

        <!-- Slider for maximum distance -->
        <div class="f5_form-group form-group">
            <label for="f5_distance_slider">Maximum Distance</label>
            <input type="range" class="form-range" id="f5_distance_slider" min="1" max="500" value="50" step="1">
            <span id="f5_distance_value">50 km</span>
        </div>

        <!-- Checkbox for "I work throughout Austria" -->
        <div class="f5_form-group form-group">
            <label for="f5_work_throughout_austria">
                <input type="checkbox" id="f5_work_throughout_austria"> I work throughout Austria
            </label>
        </div>

        <!-- Navigation buttons -->
        <button type="button" class="previous-button" data-previous-step="4">Back</button>
        <button type="button" class="next-button purple-btn" data-next-step="6">Continue</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Update slider value
            const slider = document.getElementById('f5_distance_slider');
            const distanceValue = document.getElementById('f5_distance_value');
            slider.addEventListener('input', function () {
                distanceValue.textContent = slider.value + ' km';
            });

            // Initialize map (using Leaflet.js or Mapbox)
            const map = L.map('f5_work_area_map').setView([48.2082, 16.3738], 13); // Example coordinates (Vienna, Austria)

            // Set up Mapbox tile layer (replace with your own Mapbox API key)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            // Draw a circle around the center point with a radius of 50 km (default)
            let circle = L.circle([48.2082, 16.3738], {
                color: 'purple',
                fillColor: '#6b52ae',
                fillOpacity: 0.3,
                radius: slider.value * 1000 // Radius in meters
            }).addTo(map);

            // Update circle radius when slider value changes
            slider.addEventListener('input', function () {
                circle.setRadius(slider.value * 1000); // Update radius in meters
            });
        });
    </script>
    <?php
}

// Function to render Step 6
function render_artisan_registration_step_6() {
    ?>
    <div class="form-step form-step-6">
        <!-- Progress Bar -->
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <h2>What is your current professional status?</h2>
        
        <!-- Radio buttons for professional status -->
        <div class="form-group">
            <?php
            // Array of professional status options
            $status_options = [
                'I have just registered my company and am still waiting for my business license',
                'My company has existed for less than 3 months',
                'My company exists between 3 months and one year',
                'My company has existed for over a year',
                'I don\'t have a company'
            ];

            // Loop through options and render each as a radio button
            foreach ($status_options as $index => $status) {
                render_radio_button_field(
                    'professional_status',                // Name
                    'status_' . $index,                   // ID (unique for each option)
                    $status,                              // Label
                    false,                                // Checked (default is false)
                    true                                   // Required
                );
            }
            ?>
        </div>

        <!-- Navigation buttons -->
        <button type="button" class="previous-button" data-previous-step="5">Back</button>
        <button type="button" class="next-button purple-btn" data-next-step="8">Continue</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // No additional JS functionality needed for this step
        });
    </script>
    <?php
}

// Function to render Step 8
function render_artisan_registration_step_8() {
    ?>
    <div class="form-step form-step-8">
        <!-- Progress Bar -->
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <h2>What is your company name?</h2>
        <p>Only qualified tradesmen and service providers can use MyHammer.</p>

        <!-- GISA Number (Optional) -->
        <div class="f8_form-group form-group">
            <?php
            render_text_field(
                'f8_gisa_number',      // Name
                'f8_gisa_number',      // ID
                'GISA (optional)',     // Label
                'Enter your GISA number',  // Placeholder
                '',                    // Default value
                false                  // Not required
            );
            ?>
        </div>

        <!-- Company Name (Required) -->
        <div class="f8_form-group form-group">
            <?php
            render_text_field(
                'f8_company_name',     // Name
                'f8_company_name',     // ID
                'Company Name *',      // Label
                'Enter your company name', // Placeholder
                '',                    // Default value
                true                   // Required
            );
            ?>
        </div>

        <!-- Address (Required) -->
        <div class="f8_form-group form-group">
            <?php
            render_text_field(
                'f8_address',          // Name
                'f8_address',          // ID
                'Address *',           // Label
                'Enter your address',  // Placeholder
                '',                    // Default value
                true                   // Required
            );
            ?>
        </div>

        <!-- Zip Code and City (Grouped) -->
        <div class="f8_form-group form-group">
            <?php
            render_grouped_fields(
                'f8_zip_code', 'f8_zip_code', 'Zip Code *',    // Zip Code Field
                'f8_city', 'f8_city', 'City *',                 // City Field
                '', '', true                                    // Default values and required
            );
            ?>
        </div>

        <!-- Navigation Buttons -->
        <button type="button" class="previous-button" data-previous-step="6">Back</button>
        <button type="button" class="next-button purple-btn" data-next-step="9">Continue</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // No specific JS for Step 8 yet
        });
    </script>
    <?php
}

// Function to render Step 9
function render_artisan_registration_step_9() {
    ?>
    <div class="form-step form-step-9">
        <!-- Progress Bar -->
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <h2>qualifications</h2>
        <p>Please upload your business license</p>
        <p class="f9_subtitle">
            You can upload either each page individually or a file with multiple pages.
        </p>

        <!-- File Upload Field -->
        <div class="f9_form-group form-group">
            <?php
            render_file_upload_field(
                'business_license',                 // Name
                'business_license',                 // ID
                '',                                 // Label (No label required)
                true,                               // Required
                ['accept' => 'image/png, image/jpeg, application/pdf'] // Accept specific file types
            );
            ?>
            <p class="f9_file-info">File: PNG, JPG, PDF, max. 15 MB</p>
        </div>

        <!-- Navigation Buttons -->
        <button type="button" class="previous-button" data-previous-step="8">Back</button>
        <button type="button" class="next-button purple-btn" data-next-step="10">Continue</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Add any specific JS logic for file validation or submission here
            const submitButton = document.querySelector('.f9_submit-button');
            submitButton.addEventListener('click', function () {
                alert('File submitted successfully!'); // Replace with actual logic as needed
            });
        });
    </script>
    <?php
}

// Function to render Step 10
function render_artisan_registration_step_10() {
    ?>
    <div class="form-step form-step-10">
        <!-- Progress Bar -->
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <h2>Create profile</h2>
        
        <h3 class="f10_heading">Prepared for success</h3>
        <p class="f10_subheading">~ 5 mins</p>
        <p class="f10_description">
            You've almost made it!<br>
            We help you achieve your goals and get the orders you want.<br><br>
            In this step, we will set up your public profile and guide you through important steps to consider when using MyHammer.
        </p>

        <!-- Navigation Buttons -->
        <button type="button" class="previous-button purple-btn" data-previous-step="9">Back</button>
        <button type="button" class="next-button purple-btn" data-next-step="11">Continue</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // No additional JS logic needed for this step
        });
    </script>
    <?php
}

// Function to render Step 11
function render_artisan_registration_step_11() {
    ?>
    <div class="form-step form-step-11">
        <!-- Progress Bar -->
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <h2>Create profile</h2>
        
        <h3 class="f11_heading">Introduce yourself to future customers</h3>
        <p class="f11_description">
            This is your opportunity to make a good first impression. You can make changes to your profile at any time.
        </p>

        <!-- Tip Box -->
        <div class="f11_tip-box">
            <i class="f11_icon">ℹ️</i>
            <span class="f11_tip-text">
                A good description can increase your chances of getting orders.
                <a href="#" class="f11_tip-link">Helpful Writing Tips</a>
            </span>
        </div>

        <!-- Text Area Field -->
        <div class="f11_form-group form-group">
            <textarea 
                id="f11_professional_description" 
                name="professional_description" 
                class="f11_textarea form-control" 
                placeholder="Describe your professional experience and expertise..." 
                maxlength="1250" 
                rows="5"></textarea>
            <div class="f11_character-count">
                <span id="f11_char_count">0</span> / 1250
            </div>
        </div>

        <!-- Navigation Buttons -->
        <button type="button" class="previous-button purple-btn" data-previous-step="10">Back</button>
        <button type="submit" class="submit-button purple-btn">Submit</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const textarea = document.getElementById('f11_professional_description');
            const charCount = document.getElementById('f11_char_count');

            // Update character count
            textarea.addEventListener('input', function () {
                charCount.textContent = textarea.value.length;
            });
        });
    </script>
    <?php
}

// Main function to render the form
function kazverse_render_registration_form() {
    ob_start();
    ?>
    <div id="artisan-registration-form">
        <form id="artisanForm" method="post">
            <?php
            render_artisan_registration_step_1();
            render_artisan_registration_step_2();
            render_artisan_registration_step_3();
            render_artisan_registration_step_4();
            render_artisan_registration_step_5();
            render_artisan_registration_step_6();
            render_artisan_registration_step_8();
            render_artisan_registration_step_9();
            render_artisan_registration_step_10();
            render_artisan_registration_step_11();
            // Additional steps will go here
            ?>
        </form>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const steps = document.querySelectorAll('.form-step');
            const nextButtons = document.querySelectorAll('.next-button');
            const previousButtons = document.querySelectorAll('.previous-button');

            nextButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const currentStep = document.querySelector('.form-step.active');
                    const nextStep = document.querySelector(`.form-step-${button.dataset.nextStep}`);
                    if (nextStep) {
                        currentStep.classList.remove('active');
                        nextStep.classList.add('active');
                    }
                });
            });

            previousButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const currentStep = document.querySelector('.form-step.active');
                    const previousStep = document.querySelector(`.form-step-${button.dataset.previousStep}`);
                    if (previousStep) {
                        currentStep.classList.remove('active');
                        previousStep.classList.add('active');
                    }
                });
            });
        });
    </script>

    <?php
    return ob_get_clean();
}
