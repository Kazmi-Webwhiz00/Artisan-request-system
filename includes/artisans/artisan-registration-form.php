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
            [
                '' => 'Select your trade',
                'well-builder' => 'Well Builder',
                'electrician' => 'Electrician',
                'plumber' => 'Plumber',
                // Add more trades as needed
            ],
            '',   // Default selected value
            true  // Required
        );

        // Render the zip code field using render_text_field
        render_text_field(
            'zip_code',
            'zip_code',
            'Zip Code',
            'Enter your zip code',
            '',
            true // Required
        );

        // Render the email field using render_email_field
        render_email_field(
            'email',
            'email',
            'Email Address',
            'Enter your email address',
            '',
            true // Required
        );
        ?>

        <div class="form-group terms">
            <p>
                By clicking on “Register for free” you agree to Kazverse’s 
                <a href="#">terms and conditions</a>. Information about how we process your data can be found in our 
                <a href="#">privacy policy</a>.
            </p>
        </div>

        <!-- "Next" button is disabled by default; enabled only when validation passes -->
        <button 
            type="button" 
            class="next-button" 
            data-next-step="2" 
            disabled
        >
            Register for free
        </button>

        <!-- Error message container -->
        <div class="step1-error" style="display:none; color:red; margin-top:10px;"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tradeSelect = document.getElementById('trade');
            const zipInput    = document.getElementById('zip_code');
            const emailInput  = document.getElementById('email');
            const nextButton  = document.querySelector('.form-step-1 .next-button');
            const errorContainer = document.querySelector('.step1-error');

            // Simple email validation regex
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            // Check validity of Step 1 inputs
            function validateStep1() {
                const trade = tradeSelect.value.trim();
                const zip   = zipInput.value.trim();
                const email = emailInput.value.trim();

                let errors = [];

                // Validate trade (required and not empty)
                if (!trade) {
                    errors.push('Please select a trade.');
                }

                // Validate zip code (must be numeric and non-empty)
                if (!zip) {
                    errors.push('Zip code is required.');
                } else if (!/^[0-9]+$/.test(zip)) {
                    errors.push('Zip code must be numeric only.');
                }

                // Validate email
                if (!email) {
                    errors.push('Email is required.');
                } else if (!emailRegex.test(email)) {
                    errors.push('Please enter a valid email address.');
                }

                // If no errors, enable button; otherwise disable it
                if (errors.length === 0) {
                    nextButton.disabled = false;
                    errorContainer.style.display = 'none';
                    errorContainer.innerHTML = '';
                } else {
                    nextButton.disabled = true;
                    errorContainer.style.display = 'block';
                    errorContainer.innerHTML = errors.join('<br>');
                }
            }

            // Validate on every input/change
            tradeSelect.addEventListener('change', validateStep1);
            zipInput.addEventListener('input', validateStep1);
            emailInput.addEventListener('input', validateStep1);

            // Final check on button click (in case it somehow gets clicked)
            nextButton.addEventListener('click', function () {
                // If disabled, do nothing
                if (nextButton.disabled) {
                    return;
                }

                // Once valid, store data and move to the next step
                const trade = tradeSelect.value.trim();
                const zip   = zipInput.value.trim();
                const email = emailInput.value.trim();

                // Initialize step1 object if not present
                if (!window.kazverseRegistrationData.step1) {
                    window.kazverseRegistrationData.step1 = {};
                }

                // Store Step 1 data in the global object
                window.kazverseRegistrationData.step1.trade    = trade;
                window.kazverseRegistrationData.step1.zip_code = zip;
                window.kazverseRegistrationData.step1.email    = email;

                // Console log the entire data object
                console.log('Current Registration Data:', window.kazverseRegistrationData);

                // Manually switch to the next step
                const currentStep = document.querySelector('.form-step.active');
                const nextStep = document.querySelector('.form-step-2');
                if (currentStep && nextStep) {
                    currentStep.classList.remove('active');
                    nextStep.classList.add('active');
                }
            });
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
            'Company Owner',        // Label
            'First name',           // Placeholder
            '',                     // Default value
            true                    // Required
        );

        // Render the last name field
        render_text_field(
            'last_name',            // Name
            'last_name',            // ID
            '',                     // Label
            'Last name',            // Placeholder
            '',                     // Default value
            true                    // Required
        );

        // Render the phone field (Note: +43 prefix is already shown, so user enters only the remaining digits)
        render_phone_field(
            'phone',                // Name
            'phone',                // ID
            'Phone Number',         // Label
            'Enter your phone number', // Placeholder
            '',                     // Default value
            true,                   // Required
            '+43'                   // Phone prefix (displayed but not typed by user)
        );

        // Render the password field using the render_password_field function
        render_password_field(
            'password',             // Name
            'password',             // ID
            'Password (at least 6 characters)', // Label
            'Create password',      // Placeholder
            '',                     // Default value
            true                    // Required
        );

        // Subscribe checkbox - not mandatory
        render_checkbox_field(
            'subscribe',           // Name
            'subscribe',           // ID
            'I would like to receive advertising about Kazverse services and offers by email, SMS, and/or telephone.', // Label
            false,                 // Checked by default
            false                  // Not required
        );
        ?>

        <!-- Navigation buttons -->
        <button 
            type="button" 
            class="previous-button" 
            data-previous-step="1"
        >
            Back
        </button>
        <button 
            type="button" 
            class="next-button purple-btn" 
            data-next-step="3"
            disabled
        >
            Continue
        </button>

        <!-- Error message container -->
        <div class="step2-error" style="display:none; color:red; margin-top:10px;"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const firstNameInput  = document.getElementById('first_name');
            const lastNameInput   = document.getElementById('last_name');
            const phoneInput      = document.getElementById('phone');
            const passwordInput   = document.getElementById('password');
            const subscribeBox    = document.getElementById('subscribe');
            const nextButton      = document.querySelector('.form-step-2 .next-button');
            const errorContainer  = document.querySelector('.step2-error');

            // We'll assume user only enters digits for phoneInput (excluding +43, already shown in UI).
            // Must be 6..13 digits. Example: '1234567' => final becomes '+43 1234567'
            const phoneDigitsRegex = /^\d{6,13}$/;

            // Validate step 2 fields
            function validateStep2() {
                const firstName    = firstNameInput.value.trim();
                const lastName     = lastNameInput.value.trim();
                const phoneDigits  = phoneInput.value.trim();
                const password     = passwordInput.value.trim();
                let errors = [];

                // First name required
                if (!firstName) {
                    errors.push('First name is required.');
                }

                // Last name required
                if (!lastName) {
                    errors.push('Last name is required.');
                }

                // Phone must be 6..13 digits if user has typed anything
                if (!phoneDigits) {
                    errors.push('Phone number is required.');
                } else if (!phoneDigitsRegex.test(phoneDigits)) {
                    errors.push('Phone number must be 6 to 13 digits (excluding the +43 prefix).');
                }

                // Password required, at least 6 characters
                if (!password) {
                    errors.push('Password is required.');
                } else if (password.length < 6) {
                    errors.push('Password must be at least 6 characters long.');
                }

                if (errors.length === 0) {
                    nextButton.disabled = false;
                    errorContainer.style.display = 'none';
                    errorContainer.innerHTML = '';
                } else {
                    nextButton.disabled = true;
                    errorContainer.style.display = 'block';
                    errorContainer.innerHTML = errors.join('<br>');
                }
            }

            // Listen to all relevant fields
            firstNameInput.addEventListener('input', validateStep2);
            lastNameInput.addEventListener('input', validateStep2);
            phoneInput.addEventListener('input', validateStep2);
            passwordInput.addEventListener('input', validateStep2);
            if (subscribeBox) {
                subscribeBox.addEventListener('change', validateStep2);
            }

            // On final button click, store data if not disabled
            nextButton.addEventListener('click', function() {
                if (nextButton.disabled) {
                    return; // If disabled, do nothing
                }

                const firstName    = firstNameInput.value.trim();
                const lastName     = lastNameInput.value.trim();
                // We create final phone by prefixing '+43 ' (or however you want to format).
                const phoneDigits  = phoneInput.value.trim();
                const phoneFinal   = '+43 ' + phoneDigits;
                const password     = passwordInput.value.trim();
                // Subscription is optional
                const isSubscribed = subscribeBox && subscribeBox.checked ? true : false;

                // Initialize step2 object if not present
                if (!window.kazverseRegistrationData.step2) {
                    window.kazverseRegistrationData.step2 = {};
                }

                window.kazverseRegistrationData.step2.first_name = firstName;
                window.kazverseRegistrationData.step2.last_name  = lastName;
                window.kazverseRegistrationData.step2.phone      = phoneFinal;
                window.kazverseRegistrationData.step2.password   = password;
                window.kazverseRegistrationData.step2.subscribe  = isSubscribed;

                console.log('Current Registration Data:', window.kazverseRegistrationData);

                // Manually switch steps
                const currentStep = document.querySelector('.form-step.active');
                const nextStep    = document.querySelector('.form-step-3');
                if (currentStep && nextStep) {
                    currentStep.classList.remove('active');
                    nextStep.classList.add('active');
                }
            });
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
        <div class="f4_form_group form-group">
            <label for="f4_trade_select">Select Trade(s)</label>
            <div id="f4_trade_cards" class="f4_trade-cards">
                <?php
                foreach ($trades as $trade) {
                    render_checkbox_field(
                        'f4_trade_select[]',                // Name (array)
                        'f4_trade_' . sanitize_title($trade), // ID
                        esc_html($trade),                    // Label
                        false,                               // Checked (default)
                        false                                // Not strictly required at the HTML level,
                                                            // but we enforce in JS
                    );
                }
                ?>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <button type="button" class="previous-button" data-previous-step="3">Back</button>
        <button 
            type="button" 
            class="next-button purple-btn" 
            data-next-step="5"
            disabled
        >
            Continue
        </button>

        <!-- Error message container -->
        <div class="step4-error" style="display:none; color:red; margin-top:10px;"></div>
    </div>
   
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tradeSearchInput = document.getElementById('f4_trade_search');
            const tradeCards       = document.querySelectorAll('#f4_trade_cards .f4_trade-card');
            const checkboxes       = document.querySelectorAll('input[name="f4_trade_select[]"]');
            const step4NextButton  = document.querySelector('.form-step-4 .next-button');
            const errorContainer   = document.querySelector('.step4-error');

            // Simple trade search functionality (for filtering the trades)
            if (tradeSearchInput && tradeCards.length > 0) {
                tradeSearchInput.addEventListener('input', function() {
                    const filterValue = tradeSearchInput.value.toLowerCase();
                    tradeCards.forEach(function(card) {
                        const labelElement = card.querySelector('label');
                        if (!labelElement) return;

                        const tradeText = labelElement.textContent.toLowerCase();
                        if (tradeText.indexOf(filterValue) === -1) {
                            card.style.display = 'none';
                        } else {
                            card.style.display = 'flex';
                        }
                    });
                });
            }

            // Validation: user must select between 1 and 5 trades
            function validateStep4() {
                // Count how many checkboxes are checked
                const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);
                let errors = [];

                if (checkedBoxes.length === 0) {
                    errors.push('You must select at least one trade.');
                } else if (checkedBoxes.length > 5) {
                    errors.push('You can select a maximum of five trades.');
                }

                if (errors.length === 0) {
                    step4NextButton.disabled = false;
                    errorContainer.style.display = 'none';
                    errorContainer.innerHTML = '';
                } else {
                    step4NextButton.disabled = true;
                    errorContainer.style.display = 'block';
                    errorContainer.innerHTML = errors.join('<br>');
                }
            }

            // Run validation on each checkbox change
            checkboxes.forEach(cb => {
                cb.addEventListener('change', validateStep4);
            });

            // On final button click, store data if not disabled
            step4NextButton.addEventListener('click', function() {
                if (step4NextButton.disabled) {
                    return; // If disabled, do nothing
                }

                // Gather selected trades
                const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);
                const selectedTrades = checkedBoxes.map(cb => {
                    // Get the label text for each checked trade
                    const labelElement = document.querySelector(`label[for="${cb.id}"]`);
                    return labelElement ? labelElement.innerText.trim() : cb.id;
                });

                // Initialize step4 object if not present
                if (!window.kazverseRegistrationData.step4) {
                    window.kazverseRegistrationData.step4 = {};
                }

                // Store selected trades
                window.kazverseRegistrationData.step4.selected_trades = selectedTrades;

                console.log('Current Registration Data:', window.kazverseRegistrationData);

                // Manually move to next step
                const currentStep = document.querySelector('.form-step.active');
                const nextStep    = document.querySelector('.form-step-5');
                if (currentStep && nextStep) {
                    currentStep.classList.remove('active');
                    nextStep.classList.add('active');
                }
            });

            // Initially run validation in case user tries to proceed without any interaction
            validateStep4();
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

        <!-- Map displaying work area (using Leaflet) -->
        <div id="f5_work_area_map" style="height: 300px; width: 100%;"></div>

        <!-- Slider for maximum distance -->
        <div class="f5_form-group form-group">
            <label for="f5_distance_slider">Maximum Distance</label>
            <input 
                type="range" 
                class="form-range" 
                id="f5_distance_slider" 
                min="1" 
                max="500" 
                value="50" 
                step="1"
            >
            <span id="f5_distance_value">50 km</span>
        </div>

        <!-- Checkbox for "I work throughout Austria" (not mandatory) -->
        <div class="f5_form_group form-group">
            <label for="f5_work_throughout_austria">
                <input type="checkbox" id="f5_work_throughout_austria"> I work throughout Austria
            </label>
        </div>

        <!-- Navigation buttons -->
        <button type="button" class="previous-button" data-previous-step="4">Back</button>
        <button 
            type="button" 
            class="next-button purple-btn" 
            data-next-step="6"
            disabled
        >
            Continue
        </button>

        <!-- Error message container -->
        <div class="step5-error" style="display:none; color:red; margin-top:10px;"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // DOM references
            const slider            = document.getElementById('f5_distance_slider');
            const distanceValueSpan = document.getElementById('f5_distance_value');
            const austriaCheckbox   = document.getElementById('f5_work_throughout_austria');
            const step5NextButton   = document.querySelector('.form-step-5 .next-button');
            const errorContainer    = document.querySelector('.step5-error');

            // Initialize Leaflet map
            const map = L.map('f5_work_area_map').setView([48.2082, 16.3738], 13); // Example: Vienna, Austria
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            // Draw initial circle with default 50 km radius
            let circle = L.circle([48.2082, 16.3738], {
                color: 'purple',
                fillColor: '#6b52ae',
                fillOpacity: 0.3,
                radius: slider.value * 1000 // Convert km to meters
            }).addTo(map);

            // Update the slider value text & circle radius on input
            slider.addEventListener('input', function () {
                distanceValueSpan.textContent = slider.value + ' km';
                circle.setRadius(slider.value * 1000);
                validateStep5();
            });

            // Validate step 5
            function validateStep5() {
                const distance = parseInt(slider.value, 10);
                let errors = [];

                // Basic check: distance must be between 1 and 500
                if (isNaN(distance) || distance < 1 || distance > 500) {
                    errors.push('Distance must be between 1 and 500 km.');
                }

                if (errors.length === 0) {
                    step5NextButton.disabled = false;
                    errorContainer.style.display = 'none';
                    errorContainer.innerHTML = '';
                } else {
                    step5NextButton.disabled = true;
                    errorContainer.style.display = 'block';
                    errorContainer.innerHTML = errors.join('<br>');
                }
            }

            // Run initial validation to set button state
            validateStep5();

            // On final button click, store data if not disabled
            step5NextButton.addEventListener('click', function() {
                if (step5NextButton.disabled) {
                    return; // If disabled, do nothing
                }

                const distance = parseInt(slider.value, 10);
                const worksThroughoutAustria = austriaCheckbox.checked;

                // Initialize step5 object if not present
                if (!window.kazverseRegistrationData.step5) {
                    window.kazverseRegistrationData.step5 = {};
                }

                // Store Step 5 data in the global object
                window.kazverseRegistrationData.step5.distance = distance;
                window.kazverseRegistrationData.step5.work_throughout_austria = worksThroughoutAustria;

                // Console log the entire data object
                console.log('Current Registration Data:', window.kazverseRegistrationData);

                // Manually switch to the next step
                const currentStep = document.querySelector('.form-step.active');
                const nextStep    = document.querySelector('.form-step-6');
                if (currentStep && nextStep) {
                    currentStep.classList.remove('active');
                    nextStep.classList.add('active');
                }
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
        <div class="form-group" id="professional-status-group">
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
                    false                                 // Not strictly required by HTML, but we enforce in JS
                );
            }
            ?>
        </div>

        <!-- Navigation buttons -->
        <button 
            type="button" 
            class="previous-button" 
            data-previous-step="5"
        >
            Back
        </button>

        <button 
            type="button" 
            class="next-button purple-btn" 
            data-next-step="8"
            disabled
        >
            Continue
        </button>

        <!-- Error message container -->
        <div class="step6-error" style="display:none; color:red; margin-top:10px;"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const step6NextButton   = document.querySelector('.form-step-6 .next-button');
            const errorContainer    = document.querySelector('.step6-error');
            const radioButtons      = document.querySelectorAll('input[name="professional_status"]');

            // Validate step 6: user must pick exactly one professional status
            function validateStep6() {
                let errors = [];
                const checkedRadio = Array.from(radioButtons).find(rb => rb.checked);

                if (!checkedRadio) {
                    errors.push('Please select your current professional status.');
                }

                if (errors.length === 0) {
                    step6NextButton.disabled = false;
                    errorContainer.style.display = 'none';
                    errorContainer.innerHTML = '';
                } else {
                    step6NextButton.disabled = true;
                    errorContainer.style.display = 'block';
                    errorContainer.innerHTML = errors.join('<br>');
                }
            }

            // Add event listeners to each radio button
            radioButtons.forEach(rb => {
                rb.addEventListener('change', validateStep6);
            });

            // Initial validation in case none are selected yet
            validateStep6();

            // On final button click, store data if not disabled
            step6NextButton.addEventListener('click', function() {
                if (step6NextButton.disabled) {
                    return; // If disabled, do nothing
                }

                // Get the selected radio button
                const selectedRadio = Array.from(radioButtons).find(rb => rb.checked);
                let selectedStatus = '';

                if (selectedRadio) {
                    // Get label text for the selected radio
                    const labelElement = document.querySelector(`label[for="${selectedRadio.id}"]`);
                    if (labelElement) {
                        selectedStatus = labelElement.innerText.trim();
                    }
                }

                // Initialize step6 object if not present
                if (!window.kazverseRegistrationData.step6) {
                    window.kazverseRegistrationData.step6 = {};
                }

                // Store selected status in the global object
                window.kazverseRegistrationData.step6.professional_status = selectedStatus;

                // Console log the entire data object
                console.log('Current Registration Data:', window.kazverseRegistrationData);

                // Manually switch to the next step
                const currentStep = document.querySelector('.form-step.active');
                const nextStep    = document.querySelector('.form-step-8');
                if (currentStep && nextStep) {
                    currentStep.classList.remove('active');
                    nextStep.classList.add('active');
                }
            });
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
        <div class="f8_form_group form-group">
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
        <div class="f8_form_group form-group">
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
        <div class="f8_form_group form-group">
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
        <div class="f8_form_group form-group">
            <?php
            render_grouped_fields(
                'f8_zip_code', 'f8_zip_code', 'Zip Code *',    // Zip Code Field
                'f8_city', 'f8_city', 'City *',                // City Field
                '', '', true                                   // Default values and required
            );
            ?>
        </div>

        <!-- Navigation Buttons -->
        <button 
            type="button" 
            class="previous-button" 
            data-previous-step="6"
        >
            Back
        </button>
        <button 
            type="button" 
            class="next-button purple-btn" 
            data-next-step="9"
            disabled
        >
            Continue
        </button>

        <!-- Error message container -->
        <div class="step8-error" style="display:none; color:red; margin-top:10px;"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // DOM Elements
            const gisaInput       = document.getElementById('f8_gisa_number');    // optional
            const companyNameInput= document.getElementById('f8_company_name');   // required
            const addressInput    = document.getElementById('f8_address');        // required
            const zipCodeInput    = document.getElementById('f8_zip_code');       // required
            const cityInput       = document.getElementById('f8_city');           // required
            const step8NextButton = document.querySelector('.form-step-8 .next-button');
            const errorContainer  = document.querySelector('.step8-error');

            // Basic regex to check zip code is numeric (adjust if you need different logic)
            const zipRegex = /^[0-9]+$/;

            // Validate Step 8
            function validateStep8() {
                const gisaNumberVal    = gisaInput.value.trim();         // optional
                const companyNameVal   = companyNameInput.value.trim();  // required
                const addressVal       = addressInput.value.trim();      // required
                const zipVal           = zipCodeInput.value.trim();      // required
                const cityVal          = cityInput.value.trim();         // required

                let errors = [];

                // Company Name
                if (!companyNameVal) {
                    errors.push('Company Name is required.');
                }

                // Address
                if (!addressVal) {
                    errors.push('Address is required.');
                }

                // Zip Code
                if (!zipVal) {
                    errors.push('Zip Code is required.');
                } else if (!zipRegex.test(zipVal)) {
                    errors.push('Zip Code must be numeric only.');
                }

                // City
                if (!cityVal) {
                    errors.push('City is required.');
                }

                // If no errors => enable button, else disable
                if (errors.length === 0) {
                    step8NextButton.disabled = false;
                    errorContainer.style.display = 'none';
                    errorContainer.innerHTML = '';
                } else {
                    step8NextButton.disabled = true;
                    errorContainer.style.display = 'block';
                    errorContainer.innerHTML = errors.join('<br>');
                }
            }

            // Add event listeners
            gisaInput.addEventListener('input', validateStep8);        // even though it's optional, let's watch it 
            companyNameInput.addEventListener('input', validateStep8);
            addressInput.addEventListener('input', validateStep8);
            zipCodeInput.addEventListener('input', validateStep8);
            cityInput.addEventListener('input', validateStep8);

            // Initial validation
            validateStep8();

            // On final button click, store data if not disabled
            step8NextButton.addEventListener('click', function() {
                if (step8NextButton.disabled) {
                    return; // If disabled, do nothing
                }

                const gisaNumberVal    = gisaInput.value.trim();
                const companyNameVal   = companyNameInput.value.trim();
                const addressVal       = addressInput.value.trim();
                const zipVal           = zipCodeInput.value.trim();
                const cityVal          = cityInput.value.trim();

                // Initialize step8 object if not present
                if (!window.kazverseRegistrationData.step8) {
                    window.kazverseRegistrationData.step8 = {};
                }

                // Store Step 8 data in the global object
                window.kazverseRegistrationData.step8.gisa_number   = gisaNumberVal;
                window.kazverseRegistrationData.step8.company_name  = companyNameVal;
                window.kazverseRegistrationData.step8.address       = addressVal;
                window.kazverseRegistrationData.step8.zip_code      = zipVal;
                window.kazverseRegistrationData.step8.city          = cityVal;

                console.log('Current Registration Data:', window.kazverseRegistrationData);

                // Manually move to the next step
                const currentStep = document.querySelector('.form-step.active');
                const nextStep    = document.querySelector('.form-step-9');
                if (currentStep && nextStep) {
                    currentStep.classList.remove('active');
                    nextStep.classList.add('active');
                }
            });
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
        <button 
            type="button" 
            class="previous-button" 
            data-previous-step="8"
        >
            Back
        </button>

        <button 
            type="button" 
            class="next-button purple-btn" 
            data-next-step="10"
            disabled
        >
            Continue
        </button>

        <!-- Error message container -->
        <div class="step9-error" style="display:none; color:red; margin-top:10px;"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const businessLicenseInput = document.getElementById('business_license');
            const step9NextButton      = document.querySelector('.form-step-9 .next-button');
            const errorContainer       = document.querySelector('.step9-error');

            // Allowed file extensions
            const allowedExtensions = ['png', 'jpg', 'jpeg', 'pdf'];
            // Maximum file size in bytes (15 MB)
            const maxFileSize = 15 * 1024 * 1024;

            // Validate Step 9
            function validateStep9() {
                let errors = [];

                const files = businessLicenseInput.files;
                if (!files || files.length === 0) {
                    errors.push('Please select a file to upload.');
                } else {
                    const file = files[0];
                    const fileName = file.name;
                    const fileSize = file.size;

                    // Check extension
                    const ext = fileName.split('.').pop().toLowerCase();
                    if (!allowedExtensions.includes(ext)) {
                        errors.push('File must be PNG, JPG, or PDF.');
                    }

                    // Check size
                    if (fileSize > maxFileSize) {
                        errors.push('File must not exceed 15 MB.');
                    }
                }

                if (errors.length === 0) {
                    step9NextButton.disabled = false;
                    errorContainer.style.display = 'none';
                    errorContainer.innerHTML = '';
                } else {
                    step9NextButton.disabled = true;
                    errorContainer.style.display = 'block';
                    errorContainer.innerHTML = errors.join('<br>');
                }
            }

            // Listen for file changes
            businessLicenseInput.addEventListener('change', validateStep9);

            // On final button click, store data if not disabled
            step9NextButton.addEventListener('click', function() {
                if (step9NextButton.disabled) {
                    return; // If disabled, do nothing
                }

                const file = businessLicenseInput.files[0];
                const fileName = file ? file.name : '';

                // Initialize step9 object if not present
                if (!window.kazverseRegistrationData.step9) {
                    window.kazverseRegistrationData.step9 = {};
                }

                // Save file name in the global data object
                window.kazverseRegistrationData.step9.business_license = fileName;

                // Console log the entire data object
                console.log('Current Registration Data:', window.kazverseRegistrationData);

                // Manually switch to the next step
                const currentStep = document.querySelector('.form-step.active');
                const nextStep    = document.querySelector('.form-step-10');
                if (currentStep && nextStep) {
                    currentStep.classList.remove('active');
                    nextStep.classList.add('active');
                }
            });

            // Initial check in case no file is selected at the start
            validateStep9();
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
                rows="5"
            ></textarea>
            <div class="f11_character-count">
                <span id="f11_char_count">0</span> / 1250
            </div>
        </div>

        <!-- Navigation Buttons -->
        <button 
            type="button" 
            class="previous-button purple-btn" 
            data-previous-step="10"
        >
            Back
        </button>
        <button 
            type="submit" 
            class="submit-button purple-btn"
            disabled
        >
            Submit
        </button>

        <!-- Error message container -->
        <div class="step11-error" style="display:none; color:red; margin-top:10px;"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const textarea       = document.getElementById('f11_professional_description');
            const charCount      = document.getElementById('f11_char_count');
            const submitButton   = document.querySelector('.form-step-11 .submit-button');
            const errorContainer = document.querySelector('.step11-error');
            const hiddenInput    = document.getElementById('kazverse_data'); // hidden input from main form

            const minDescriptionLength = 10; 
            const maxDescriptionLength = 1250; 

            // Validate text area length
            function validateStep11() {
                let errors = [];
                const descriptionValue = textarea.value.trim();
                const length = descriptionValue.length;

                if (length < minDescriptionLength) {
                    errors.push(`Description must be at least ${minDescriptionLength} characters long.`);
                }
                if (length > maxDescriptionLength) {
                    errors.push(`Description cannot exceed ${maxDescriptionLength} characters.`);
                }

                if (errors.length === 0) {
                    submitButton.disabled = false;
                    errorContainer.style.display = 'none';
                    errorContainer.innerHTML = '';
                } else {
                    submitButton.disabled = true;
                    errorContainer.style.display = 'block';
                    errorContainer.innerHTML = errors.join('<br>');
                }
            }

            // Track input changes for character count and validation
            textarea.addEventListener('input', function () {
                charCount.textContent = textarea.value.length;
                validateStep11();
            });

            // Validate on load
            validateStep11();

            // On final "Submit" click
            submitButton.addEventListener('click', function(e) {
                if (submitButton.disabled) {
                    // If still invalid, prevent submission
                    e.preventDefault();
                    return;
                }
                // Merge final text area data into global object
                window.kazverseRegistrationData.step11 = window.kazverseRegistrationData.step11 || {};
                window.kazverseRegistrationData.step11.description = textarea.value.trim();

                // Serialize entire object into the hidden input
                hiddenInput.value = JSON.stringify(window.kazverseRegistrationData);
                // DO NOT prevent default -> let form submit normally
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
        <!-- Global data object initialization in JS -->
        <script>
            // Make a global object to store form data across steps
            window.kazverseRegistrationData = {};
        </script>

        <!-- The form posts to admin-post.php with our custom action & nonce -->
        <form 
            id="artisanForm" 
            method="post"
            action="<?php echo esc_url( admin_url('admin-post.php') ); ?>"
        >
            <input type="hidden" name="action" value="kazverse_artisan_submit" />
            <?php wp_nonce_field( 'kazverse_artisan_submit_action', 'kazverse_artisan_nonce' ); ?>

            <!-- Hidden input to store our entire global data as JSON at final submission -->
            <input type="hidden" name="kazverse_data" id="kazverse_data" value="" />

            <?php
            // Render each step
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
            ?>
        </form>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const steps = document.querySelectorAll('.form-step');
            const nextButtons = document.querySelectorAll('.next-button');
            const previousButtons = document.querySelectorAll('.previous-button');

            // "Next" buttons move to the specified step, no submission
            nextButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const currentStep = document.querySelector('.form-step.active');
                    const nextStep = document.querySelector(`.form-step-${button.dataset.nextStep}`);
                    if (currentStep && nextStep) {
                        currentStep.classList.remove('active');
                        nextStep.classList.add('active');
                    }
                });
            });

            // "Back" buttons move to the previous step
            previousButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const currentStep = document.querySelector('.form-step.active');
                    const previousStep = document.querySelector(`.form-step-${button.dataset.previousStep}`);
                    if (currentStep && previousStep) {
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
