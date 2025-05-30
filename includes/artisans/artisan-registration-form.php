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
require_once plugin_dir_path( __FILE__ ) .  '../general-helpers/forms/zipcode-field.php';


// Enqueue CSS for artisan registration form
function enqueue_artisan_registration_form_styles() {
    wp_enqueue_style(
        'artisan-registration-form-css', // Handle for the stylesheet
        plugin_dir_url(__FILE__) . 'artisan-registration-form.css', // Path to the CSS file
        array(), // Dependencies (if any)
        filemtime(plugin_dir_path(__FILE__) . 'artisan-registration-form.css') // Version based on file modification time
    );

    wp_enqueue_script(
        'kz-file-upload-preview', // Script handle
        plugin_dir_url(__FILE__) . 'artisan-registration-form.js', // Path to JS file
        array('jquery'), // Dependencies
        '1.0.0', // Version
        true // Load in footer
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

        // Fetch trades dynamically from the 'global_services' taxonomy
        $terms = get_terms([
            'taxonomy'   => 'global_services',
            'hide_empty' => false, // Include terms even if not assigned to posts
        ]);

        $trades = ['' => __( 'Select your trade', 'kazverse-artisan-plugin' )];
        if ( !is_wp_error( $terms ) && !empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $trades[sanitize_title( $term->name )] = $term->name;
            }
        }
    ?>
    <div class="form-step form-step-1 active">

        <!-- Trade Field -->
        <div class="form-group">
            <?php
            render_select_field(
                'trade',
                'trade',
                __( 'Trade', 'kazverse-artisan-plugin' ),
                $trades,
                '',
                true // Required
            );
            ?>
            <!-- Inline error for trade -->
            <span id="trade-error" class="error-msg" style="display:none; color:red;"></span>
        </div>

        <!-- Zip Code Field -->
        <div class="form-group">
            <?php
            render_zipcode_field_with_place_selector(
                'zip_code',
                'zip_code',
                __( 'eg. 5061 AA', 'kazverse-artisan-plugin' ),
                __( 'Zip Code *', 'kazverse-artisan-plugin' )
            );
            ?>
            
            <!-- Inline error for zip -->
            <span id="zip-error" class="error-msg" style="display:none; color:red;"></span>
        </div>

        <!-- Email Field -->
        <div class="form-group">
            <?php
            render_email_field(
                'email',
                'email',
                __( 'Email Address', 'kazverse-artisan-plugin' ),
                __( 'Enter your email address', 'kazverse-artisan-plugin' ),
                '',
                true // Required
            );
            ?>
            <!-- Inline error for email -->
            <span id="email-error" class="error-msg" style="display:none; color:red;"></span>
        </div>

        <div class="form-group terms">
            <p>
                <?php
                /* translators: %s and %s are links */
                echo sprintf(
                    __( 'By clicking on “Register for free” you agree to Kazverse’s %1$s. Information about how we process your data can be found in our %2$s.', 'kazverse-artisan-plugin' ),
                    '<a href="#">' . __( 'terms and conditions', 'kazverse-artisan-plugin' ) . '</a>',
                    '<a href="#">' . __( 'privacy policy', 'kazverse-artisan-plugin' ) . '</a>'
                );
                ?>
            </p>
        </div>

        <!-- Always clickable; we handle validation on click -->
        <button 
            type="button" 
            class="next-button" 
            id="step1ContinueBtn"
        >
            <?php echo esc_html__( 'Register for free', 'kazverse-artisan-plugin' ); ?>
        </button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tradeSelect = document.getElementById('trade');
            const zipInput    = document.getElementById('zip_code');
            const emailInput  = document.getElementById('email');
            const nextButton  = document.getElementById('step1ContinueBtn');

            // Inline error elements
            const tradeError  = document.getElementById('trade-error');
            const zipError    = document.getElementById('zip-error');
            const emailError  = document.getElementById('email-error');

            // Simple email validation regex
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            // This flag indicates if we've shown errors once
            // so that subsequent field edits can clear them proactively
            let hasClickedNext = false;

            // Whenever a field changes, if we've already tried to go next,
            // recheck validity => remove error highlights on corrected fields.
            function onFieldChange() {
                if (hasClickedNext) {
                    validateAndShowErrors();
                }
            }

            // Attach listeners for changes
            tradeSelect.addEventListener('change', onFieldChange);
            zipInput.addEventListener('input', onFieldChange);
            emailInput.addEventListener('input', onFieldChange);

            // Validation + inline errors
            function validateAndShowErrors() {
                let isValid = true;

                // Clear old errors
                clearError(tradeSelect, tradeError);
                clearError(zipInput,   zipError);
                clearError(emailInput, emailError);

                // Trade
                const tradeVal = tradeSelect.value.trim();
                if (!tradeVal) {
                    isValid = false;
                    showError(tradeSelect, tradeError, '<?php echo esc_js( __( 'Please select a trade.', 'kazverse-artisan-plugin' ) ); ?>');
                }

                // Zip
                const zipVal = zipInput.value.trim();
                if (!zipVal) {
                    isValid = false;
                    showError(zipInput, zipError, '<?php echo esc_js( __( 'Zip code is required.', 'kazverse-artisan-plugin' ) ); ?>');
                } else {
                    let zipPlaceDisplay = document.querySelector('.zip-place-display');

                    if (!zipPlaceDisplay || zipPlaceDisplay.textContent.trim() === '') {
                        isValid = false;
                        showError(zipInput, zipError, '<?php echo esc_js( __( 'Valid zip code is required.', 'kazverse-artisan-plugin' ) ); ?>');
                    } else {
                        isValid = true;
                    }
                }

                // Email
                const emailVal = emailInput.value.trim();
                if (!emailVal) {
                    isValid = false;
                    showError(emailInput, emailError, '<?php echo esc_js( __( 'Email is required.', 'kazverse-artisan-plugin' ) ); ?>');
                } else if (!emailRegex.test(emailVal)) {
                    isValid = false;
                    showError(emailInput, emailError, '<?php echo esc_js( __( 'Please enter a valid email address.', 'kazverse-artisan-plugin' ) ); ?>');
                }

                return isValid;
            }

            function showError(inputEl, errorEl, msg) {
                errorEl.textContent   = msg;
                errorEl.style.display = 'inline';
                inputEl.classList.add('error-field');
            }

            function clearError(inputEl, errorEl) {
                errorEl.textContent   = '';
                errorEl.style.display = 'none';
                inputEl.classList.remove('error-field');
            }

            // On Next button click => show errors if invalid, else proceed
            nextButton.addEventListener('click', function() {
                hasClickedNext = true; // Now we can show errors
                const valid = validateAndShowErrors();
                if (!valid) {
                    // Stop here
                    return;
                }

                // If valid => store data, go to next step
                const trade = tradeSelect.value.trim();
                const zip   = zipInput.value.trim();
                const email = emailInput.value.trim();

                if (!window.kazverseRegistrationData.step1) {
                    window.kazverseRegistrationData.step1 = {};
                }
                window.kazverseRegistrationData.step1.trade    = trade;
                window.kazverseRegistrationData.step1.zip_code = zip;
                window.kazverseRegistrationData.step1.email    = email;

                console.log('Current Registration Data:', window.kazverseRegistrationData);

                // Manually switch step
                const currentStep = document.querySelector('.form-step.active');
                const nextStep    = document.querySelector('.form-step-2');
                if (currentStep && nextStep) {
                    currentStep.classList.remove('active');
                    nextStep.classList.add('active');
                }
            });

            // Helper styling for red border
            function addErrorStylingCSS() {
                const style = document.createElement('style');
                style.innerHTML = `
                  .error-field {
                     
                      outline: none;
                  }
                  .error-msg {
                      margin-left: 10px;
                  }
                `;
                document.head.appendChild(style);
            }
            addErrorStylingCSS();

        });
    </script>
    <?php
}

// Function to render Step 2
function render_artisan_registration_step_2() {
    ?>
    <div class="form-step form-step-2">
        <h2><?php echo esc_html__( 'Create an Account', 'kazverse-artisan-plugin' ); ?></h2>
        <p><?php echo esc_html__( 'Enter your name exactly as it appears on your company documents.', 'kazverse-artisan-plugin' ); ?></p>

        <!-- First Name -->
        <div class="form-group">
            <?php
            render_text_field(
                'first_name',           
                'first_name',           
                __( 'Company Owner', 'kazverse-artisan-plugin' ),        
                __( 'First name', 'kazverse-artisan-plugin' ),           
                '',                     
                true // Required
            );
            ?>
            <span id="firstName-error" class="error-msg" style="display:none; color:red;"></span>
        </div>

        <!-- Last Name -->
        <div class="form-group">
            <?php
            render_text_field(
                'last_name',            
                'last_name',            
                '',                     
                __( 'Last name', 'kazverse-artisan-plugin' ),            
                '',                     
                true // Required
            );
            ?>
            <span id="lastName-error" class="error-msg" style="display:none; color:red;"></span>
        </div>

        <!-- Phone -->
        <div class="form-group">
            <?php
            render_phone_field(
                'phone',                
                'phone',                
                __( 'Phone Number', 'kazverse-artisan-plugin' ),         
                __( 'Enter your phone number', 'kazverse-artisan-plugin' ), 
                '',                     
                true,   // Required
                '+43'   // Phone prefix
            );
            ?>
            <span id="phone-error" class="error-msg" style="display:none; color:red;"></span>
        </div>

        <!-- Password -->
        <div class="form-group">
            <?php
            render_password_field(
                'password',             
                'password',             
                __( 'Password (at least 6 characters)', 'kazverse-artisan-plugin' ), 
                __( 'Create password', 'kazverse-artisan-plugin' ),      
                '',                     
                true // Required
            );
            ?>
            <span id="password-error" class="error-msg" style="display:none; color:red;"></span>
        </div>

        <!-- Subscribe (not required) -->
        <div class="form-group">
            <?php
            render_checkbox_field(
                'subscribe',           
                'subscribe',           
                __( 'I would like to receive advertising about Kazverse services and offers by email, SMS, and/or telephone.', 'kazverse-artisan-plugin' ), 
                false, // default unchecked
                false
            );
            ?>
        </div>

        <!-- Navigation Buttons -->
        <button 
            type="button" 
            class="previous-button" 
            data-previous-step="1"
        >
            <?php echo esc_html__( 'Back', 'kazverse-artisan-plugin' ); ?>
        </button>

        <!-- We REMOVE data-next-step and do validation + AJAX ourselves -->
        <button 
            type="button" 
            class="next-button next-button" 
            id="step2ContinueBtn"
        >
            <?php echo esc_html__( 'Continue', 'kazverse-artisan-plugin' ); ?>
        </button>

        <!-- Error container (for AJAX user creation issues) -->
        <div class="step2-error" style="display:none; color:red; margin-top:10px;"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const firstNameInput  = document.getElementById('first_name');
        const lastNameInput   = document.getElementById('last_name');
        const phoneInput      = document.getElementById('phone');
        const passwordInput   = document.getElementById('password');
        const subscribeBox    = document.getElementById('subscribe');
        const nextButton      = document.getElementById('step2ContinueBtn');
        const ajaxErrorEl     = document.querySelector('.step2-error');

        // Inline error spans
        const firstNameError  = document.getElementById('firstName-error');
        const lastNameError   = document.getElementById('lastName-error');
        const phoneError      = document.getElementById('phone-error');
        const passwordError   = document.getElementById('password-error');

        // Regex for phone must be 6..13 digits (excluding +43 prefix)
        const phoneDigitsRegex = /^\d{6,13}$/;

        // Let’s track if user has attempted next => show inline errors
        let hasClickedNext = false;

        // Whenever a field changes, if user hasClickedNext, re-validate => remove corrected errors
        function onFieldChange() {
            if (hasClickedNext) {
                validateAndShowErrors();
            }
        }

        firstNameInput.addEventListener('input', onFieldChange);
        lastNameInput.addEventListener('input', onFieldChange);
        phoneInput.addEventListener('input', onFieldChange);
        passwordInput.addEventListener('input', onFieldChange);
        // subscribe is optional, no inline error

        // Core validation + showing inline errors
        function validateAndShowErrors() {
            let isValid = true;
            clearErrors();

            const firstName   = firstNameInput.value.trim();
            const lastName    = lastNameInput.value.trim();
            const phoneDigits = phoneInput.value.trim();
            const password    = passwordInput.value.trim();

            // First name
            if (!firstName) {
                isValid = false;
                showError(firstNameInput, firstNameError, '<?php echo esc_js( __( 'First name is required.', 'kazverse-artisan-plugin' ) ); ?>');
            }
            // Last name
            if (!lastName) {
                isValid = false;
                showError(lastNameInput, lastNameError, '<?php echo esc_js( __( 'Last name is required.', 'kazverse-artisan-plugin' ) ); ?>');
            }
            // Phone
            if (!phoneDigits) {
                isValid = false;
                showError(phoneInput, phoneError, '<?php echo esc_js( __( 'Phone number is required.', 'kazverse-artisan-plugin' ) ); ?>');
            } else if (!phoneDigitsRegex.test(phoneDigits)) {
                isValid = false;
                showError(phoneInput, phoneError, '<?php echo esc_js( __( 'Phone must be 6..13 digits (excl. +43).', 'kazverse-artisan-plugin' ) ); ?>');
            }
            // Password
            if (!password) {
                isValid = false;
                showError(passwordInput, passwordError, '<?php echo esc_js( __( 'Password is required.', 'kazverse-artisan-plugin' ) ); ?>');
            } else if (password.length < 6) {
                isValid = false;
                showError(passwordInput, passwordError, '<?php echo esc_js( __( 'Password must be at least 6 characters.', 'kazverse-artisan-plugin' ) ); ?>');
            }

            return isValid;
        }

        function showError(inputEl, errorEl, msg) {
            errorEl.textContent   = msg;
            errorEl.style.display = 'inline';
            inputEl.classList.add('error-field');
        }
        function clearError(inputEl, errorEl) {
            errorEl.textContent   = '';
            errorEl.style.display = 'none';
            inputEl.classList.remove('error-field');
        }
        function clearErrors() {
            clearError(firstNameInput,  firstNameError);
            clearError(lastNameInput,   lastNameError);
            clearError(phoneInput,      phoneError);
            clearError(passwordInput,   passwordError);
            // Also clear any leftover AJAX error
            ajaxErrorEl.style.display = 'none';
            ajaxErrorEl.innerHTML     = '';
        }

        // On next click => if valid, do user creation AJAX
        nextButton.addEventListener('click', function() {
            hasClickedNext = true;
            if (!validateAndShowErrors()) {
                // not valid => show errors, do not proceed
                return;
            }

            // If fields are valid, proceed with the user creation AJAX
            const firstName    = firstNameInput.value.trim();
            const lastName     = lastNameInput.value.trim();
            const phoneDigits  = phoneInput.value.trim();
            const phoneFinal   = '+43 ' + phoneDigits;
            const password     = passwordInput.value.trim();
            const isSubscribed = subscribeBox && subscribeBox.checked ? true : false;

            // Save data in the global object
            if (!window.kazverseRegistrationData.step2) {
                window.kazverseRegistrationData.step2 = {};
            }
            window.kazverseRegistrationData.step2.first_name = firstName;
            window.kazverseRegistrationData.step2.last_name  = lastName;
            window.kazverseRegistrationData.step2.phone      = phoneFinal;
            window.kazverseRegistrationData.step2.password   = password;
            window.kazverseRegistrationData.step2.subscribe  = isSubscribed;

            console.log('Step2 data ready:', window.kazverseRegistrationData.step2);

            // We'll do an AJAX call to create the WP user now
            const step1Email = window.kazverseRegistrationData.step1?.email || '';
            if (!step1Email) {
                alert("<?php echo esc_js( __( 'No email found from Step 1!', 'kazverse-artisan-plugin' ) ); ?>");
                return;
            }

            nextButton.disabled = true; // temporarily prevent repeat clicks
            ajaxErrorEl.style.display = 'none';
            ajaxErrorEl.innerHTML     = '';

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_artisan_user',
                    email: step1Email,
                    first_name: firstName,
                    last_name: lastName,
                    password: password,
                    phone: phoneFinal
                })
            })
            .then(res => res.json())
            .then(response => {
                if (!response) throw new Error("<?php echo esc_js( __( 'No response from server', 'kazverse-artisan-plugin' ) ); ?>");
                if (response.success) {
                    // User creation success
                    console.log('User created:', response.data.message);;

                    window.kazverseRegistrationData.step2.userId  = response.data.user_id;


                    // Make Step 2 fields read-only
                    firstNameInput.readOnly = true;
                    lastNameInput.readOnly  = true;
                    phoneInput.readOnly     = true;
                    passwordInput.readOnly  = true;
                    if (subscribeBox) subscribeBox.disabled = true;

                    // Move to Step 3
                    const currentStep = document.querySelector('.form-step.active');
                    const nextStep    = document.querySelector('.form-step-3');
                    if (currentStep && nextStep) {
                        currentStep.classList.remove('active');
                        nextStep.classList.add('active');
                    }
                } else {
                    // Show server error => do not proceed
                    const errMsg = response.data || "<?php echo esc_js( __( 'An unknown error occurred.', 'kazverse-artisan-plugin' ) ); ?>";
                    ajaxErrorEl.style.display = 'block';
                    ajaxErrorEl.innerHTML = errMsg;
                    nextButton.disabled = false; // let them try again
                }
            })
            .catch(err => {
                console.error('AJAX error:', err);
                ajaxErrorEl.style.display = 'block';
                ajaxErrorEl.innerHTML = "<?php echo esc_js( __( 'Something went wrong. Check console.', 'kazverse-artisan-plugin' ) ); ?>";
                nextButton.disabled = false;
            });
        });

        // Inject some CSS for .error-field
        function addErrorStyling() {
            const style = document.createElement('style');
            style.innerHTML = `
                .error-field {
                   
                    outline: none;
                }
                .error-msg {
                    margin-left: 10px;
                }
            `;
            document.head.appendChild(style);
        }
        addErrorStyling();
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

        <h2><?php echo esc_html__( 'About You', 'kazverse-artisan-plugin' ); ?></h2>
        <p><strong><?php echo esc_html__( '~ 2 min', 'kazverse-artisan-plugin' ); ?></strong></p>
        <p><?php echo esc_html__( 'Welcome! Let\'s go!', 'kazverse-artisan-plugin' ); ?></p>
        <p><?php echo esc_html__( 'We would like to get to know you better so that we can provide you with suitable jobs – in your area and tailored to your area of expertise.', 'kazverse-artisan-plugin' ); ?></p>

        <button type="button" class="next-button purple-btn" data-next-step="4">
            <?php echo esc_html__( 'Continue', 'kazverse-artisan-plugin' ); ?>
        </button>
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
    // Fetch trades dynamically from the 'global_services' taxonomy
    $terms = get_terms([
        'taxonomy'   => 'global_services',
        'hide_empty' => false, // Include terms even if not assigned to posts
    ]);

    $trades = [];
    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            $trades[] = $term->name;
        }
    }
    
    ?>
    <div class="form-step form-step-4">
        <!-- Progress Bar -->
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <h2><?php echo esc_html__( 'Select up to five trades', 'kazverse-artisan-plugin' ); ?></h2>
        <p><?php echo esc_html__( 'Tell us your areas of expertise so we can send you the most relevant assignments.', 'kazverse-artisan-plugin' ); ?></p>

        <!-- Search Trades Input -->
        <div class="f4_form_group form-group">
            <label for="f4_trade_search"><?php echo esc_html__( 'Search trades', 'kazverse-artisan-plugin' ); ?></label>
            <input 
                type="text" 
                class="form-control" 
                id="f4_trade_search" 
                placeholder="<?php echo esc_attr__( 'Search trades', 'kazverse-artisan-plugin' ); ?>"
            />
        </div>

        <!-- Trades Cards with Checkboxes (using render_checkbox_field) -->
        <div class="f4_form_group form-group">
            <label for="f4_trade_select"><?php echo esc_html__( 'Select Trade(s)', 'kazverse-artisan-plugin' ); ?></label>
            <div id="f4_trade_cards" class="f4_trade-cards">
                <?php foreach ($trades as $trade): ?>
                    <div class="f4_trade-card">
                        <?php
                        render_checkbox_field(
                            'f4_trade_select[]', 
                            'f4_trade_' . sanitize_title($trade), 
                            esc_html($trade),
                            false, 
                            false
                        );
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Error message container -->
        <div class="step4-error" style="display:none; color:red; margin-top:10px;"></div>

        <!-- Navigation Buttons -->
        <button 
            type="button" 
            class="previous-button" 
            data-previous-step="3"
        >
            <?php echo esc_html__( 'Back', 'kazverse-artisan-plugin' ); ?>
        </button>
        <!-- We remove data-next-step => handle validation ourselves -->
        <button 
            type="button" 
            class="next-button purple-btn"
            id="step4ContinueBtn"
        >
            <?php echo esc_html__( 'Continue', 'kazverse-artisan-plugin' ); ?>
        </button>

        
    </div>
   
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tradeSearchInput = document.getElementById('f4_trade_search');
            const tradeCards       = document.querySelectorAll('#f4_trade_cards .f4_trade-card');
            const checkboxes       = document.querySelectorAll('input[name="f4_trade_select[]"]');
            const step4NextButton  = document.getElementById('step4ContinueBtn');
            const errorContainer   = document.querySelector('.step4-error');

            // Keep track if user tried "Continue" once
            let hasClickedNext = false;

            // Simple trade search functionality
            if (tradeSearchInput && tradeCards.length > 0) {
                tradeSearchInput.addEventListener('input', function() {
                    const filterValue = tradeSearchInput.value.toLowerCase();
                    tradeCards.forEach(function(card) {
                        const labelElement = card.querySelector('label');
                        if (!labelElement) return;

                        const tradeText = labelElement.textContent.toLowerCase();
                        // Show/hide based on match
                        card.style.display = tradeText.includes(filterValue) ? '' : 'none';
                    });
                });
            }

            // Validate: user must select 1..5 trades
            function validateStep4() {
                const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);
                let errors = [];

                if (checkedBoxes.length === 0) {
                    errors.push('<?php echo esc_js( __( 'You must select at least one trade.', 'kazverse-artisan-plugin' ) ); ?>');
                } else if (checkedBoxes.length > 5) {
                    errors.push('<?php echo esc_js( __( 'You can select a maximum of five trades.', 'kazverse-artisan-plugin' ) ); ?>');
                }
                return errors;
            }

            // Show any errors
            function showErrors() {
                const errors = validateStep4();
                if (errors.length > 0) {
                    errorContainer.style.display = 'block';
                    errorContainer.innerHTML = errors.join('<br>');
                } else {
                    errorContainer.style.display = 'none';
                    errorContainer.innerHTML = '';
                }
                return (errors.length === 0);
            }

            // If user already tried once, revalidate on any change
            function onCheckboxChange() {
                if (hasClickedNext) {
                    showErrors();
                }
            }
            checkboxes.forEach(cb => {
                cb.addEventListener('change', onCheckboxChange);
            });

            // On "Continue" click => show errors if invalid, else proceed
            step4NextButton.addEventListener('click', function() {
                hasClickedNext = true;
                if (!showErrors()) {
                    return; // If invalid, do not proceed
                }

                // Otherwise, gather selected trades
                const checkedBoxes   = Array.from(checkboxes).filter(cb => cb.checked);
                const selectedTrades = checkedBoxes.map(cb => {
                    const labelEl = document.querySelector(`label[for="${cb.id}"]`);
                    return labelEl ? labelEl.innerText.trim() : cb.id;
                });

                // Store in global object
                if (!window.kazverseRegistrationData.step4) {
                    window.kazverseRegistrationData.step4 = {};
                }
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
        });
    </script>
    <?php
}


// Function to render Step 5
function render_artisan_registration_step_5() {
    ?>
    <div class="form-step form-step-5" >
        <!-- Progress Bar -->
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <h2><?php echo esc_html__( 'In which area do you work?', 'kazverse-artisan-plugin' ); ?></h2>
        <p><?php echo esc_html__( 'Set maximum distance by gear.', 'kazverse-artisan-plugin' ); ?></p>

        <!-- Map displaying work area (using Leaflet) -->
        <div id="f5_work_area_map" style="height: 300px; width: 100%;"></div>

        <!-- Slider for maximum distance -->
        <div class="f5_form-group form-group">
            <label for="f5_distance_slider"><?php echo esc_html__( 'Maximum Distance', 'kazverse-artisan-plugin' ); ?></label>
            <input 
                type="range" 
                class="form-range" 
                id="f5_distance_slider" 
                min="1" 
                max="500" 
                value="1" 
                step="1"
            >
            <span id="f5_distance_value">1 km</span>
        </div>

        <!-- Checkbox for "I work throughout netherlands" (not mandatory) -->
        <div class="f5_form_group form-group">
            <label for="f5_work_throughout_netherlands">
                <input type="checkbox" id="f5_work_throughout_netherlands"> <?php echo esc_html__( 'I work throughout Netherlands', 'kazverse-artisan-plugin' ); ?>
            </label>
        </div>

        <!-- Navigation buttons -->
        <button type="button" class="previous-button" data-previous-step="4"><?php echo esc_html__( 'Back', 'kazverse-artisan-plugin' ); ?></button>
        <button 
            type="button" 
            class="next-button purple-btn" 
            data-next-step="6"
            disabled
        >
            <?php echo esc_html__( 'Continue', 'kazverse-artisan-plugin' ); ?>
        </button>

        <!-- Error message container -->
        <div class="step5-error" style="display:none; color:red; margin-top:10px;"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // DOM references
            const slider = document.getElementById('f5_distance_slider');
            const distanceValueSpan = document.getElementById('f5_distance_value');
            const netherlandsCheckbox = document.getElementById('f5_work_throughout_netherlands');
            const step5NextButton = document.querySelector('.form-step-5 .next-button');
            const errorContainer = document.querySelector('.step5-error');

            // Initialize Leaflet map (Amsterdam, Netherlands)
            const map = L.map('f5_work_area_map').setView([52.3676, 4.9041], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            // Draw initial circle with default 50 km radius
            let circle = L.circle([52.3676, 4.9041], {
                color: '#002335',
                fillColor: '#002335',
                fillOpacity: 0.3,
                radius: slider.value * 1000
            }).addTo(map);

            console.log("Initial Circle Coordinates:", circle.getLatLng());

            // Ensure global object exists
            if (!window.kazverseRegistrationData) {
                window.kazverseRegistrationData = {};
            }
            if (!window.kazverseRegistrationData.step5) {
                window.kazverseRegistrationData.step5 = {};
            }

            // Store **initial** values
            window.kazverseRegistrationData.step5 = {
                distance: parseInt(slider.value, 10),
                latitude: 52.3676, // Amsterdam latitude
                longitude: 4.9041, // Amsterdam longitude
                work_throughout_netherlands: netherlandsCheckbox.checked
            };

            // Ensure the map is properly rendered when Step 5 becomes visible
            const step5Container = document.querySelector('.form-step-5');
            const observer = new MutationObserver(function (mutationsList) {
                mutationsList.forEach(function (mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        if (step5Container.classList.contains('active')) {
                            setTimeout(() => {
                                map.invalidateSize();
                            }, 300);
                        }
                    }
                });
            });

            observer.observe(step5Container, { attributes: true });

            // Function to adjust zoom only if needed
            function adjustZoomIfNeeded() {
                const circleBounds = circle.getBounds();
                if (!map.getBounds().contains(circleBounds)) {
                    map.fitBounds(circleBounds, { padding: [20, 20] });
                }
            }

            // ✅ **UPDATE DISTANCE LIVE WHEN SLIDER CHANGES**
            slider.addEventListener('input', function () {
                const distanceInKm = parseInt(slider.value, 10);
                distanceValueSpan.textContent = distanceInKm + ' km';

                const radiusInMeters = distanceInKm * 1000;
                circle.setRadius(radiusInMeters);
                adjustZoomIfNeeded();

                // Update Global Data
                window.kazverseRegistrationData.step5.distance = distanceInKm;
                console.log("Updated Distance:", distanceInKm, "km", window.kazverseRegistrationData.step5);
                
                validateStep5();
            });

            // ✅ **UPDATE LATITUDE & LONGITUDE LIVE WHEN USER CLICKS ON THE MAP**
            map.on('click', function (event) {
                const selectedLatLng = event.latlng;
                circle.setLatLng(selectedLatLng);

                // Update Global Data
                window.kazverseRegistrationData.step5.latitude = selectedLatLng.lat;
                window.kazverseRegistrationData.step5.longitude = selectedLatLng.lng;

                // ✅ Recenter map on new location
                map.setView(selectedLatLng, map.getZoom());

                console.log("Updated Coordinates:", selectedLatLng.lat, selectedLatLng.lng, window.kazverseRegistrationData.step5);
            });

            // ✅ **UPDATE WORK THROUGHOUT netherlands CHECKBOX**
            netherlandsCheckbox.addEventListener('change', function () {
                const isChecked = netherlandsCheckbox.checked;
                
                window.kazverseRegistrationData.step5.work_throughout_netherlands = isChecked;
                
                // ✅ Disable the distance slider if checkbox is checked
                slider.disabled = isChecked;
                distanceValueSpan.style.opacity = isChecked ? "0.5" : "1";

                console.log("Updated Work Throughout netherlands:", isChecked, window.kazverseRegistrationData.step5);
            });

            // Validate step 5
            function validateStep5() {
                const distance = parseInt(slider.value, 10);
                let errors = [];

                // Basic check: distance must be between 1 and 500
                if (isNaN(distance) || distance < 1 || distance > 500) {
                    errors.push('<?php echo esc_js( __( 'Distance must be between 1 and 500 km.', 'kazverse-artisan-plugin' ) ); ?>');
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

            validateStep5();

            step5NextButton.addEventListener('click', function () {
                console.log("Final Step 5 Data (Before Moving Forward):", window.kazverseRegistrationData.step5);
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

        <h2><?php echo esc_html__( 'What is your current professional status?', 'kazverse-artisan-plugin' ); ?></h2>
        
        <!-- Radio buttons for professional status -->
        <div class="form-group" id="professional-status-group">
            <?php
            // Array of professional status options
            $status_options = [
                __( 'I have just registered my company and am still waiting for my business license', 'kazverse-artisan-plugin' ),
                __( 'My company has existed for less than 3 months', 'kazverse-artisan-plugin' ),
                __( 'My company exists between 3 months and one year', 'kazverse-artisan-plugin' ),
                __( 'My company has existed for over a year', 'kazverse-artisan-plugin' ),
                __( 'I don\'t have a company', 'kazverse-artisan-plugin' )
            ];

            // Loop through options and render each as a radio button
            foreach ($status_options as $index => $status) {
                render_radio_button_field(
                    'professional_status',
                    'status_' . $index,
                    $status,
                    false, // not checked by default
                    false
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
            <?php echo esc_html__( 'Back', 'kazverse-artisan-plugin' ); ?>
        </button>

        <!-- We remove data-next-step to handle validation ourselves -->
        <button 
            type="button" 
            class="next-button purple-btn" 
            id="step6ContinueBtn"
        >
            <?php echo esc_html__( 'Continue', 'kazverse-artisan-plugin' ); ?>
        </button>

        <!-- Inline error container -->
        <div id="step6-inline-error" style="display:none; color:red; margin-top:10px;"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const step6NextButton = document.getElementById('step6ContinueBtn');
            const errorContainer  = document.getElementById('step6-inline-error');
            const radioButtons    = document.querySelectorAll('input[name="professional_status"]');

            // Track if user has clicked "Continue" at least once
            let attemptedNext = false;

            // Whenever a radio changes, if user already tried to go next,
            // re-validate => removing errors if now selected
            radioButtons.forEach(rb => {
                rb.addEventListener('change', function() {
                    if (attemptedNext) {
                        validateStep6();
                    }
                });
            });

            function validateStep6() {
                // Clear old error
                errorContainer.style.display = 'none';
                errorContainer.innerHTML     = '';

                let isValid = true;

                // Check if any radio is checked
                const selectedRadio = Array.from(radioButtons).find(rb => rb.checked);
                if (!selectedRadio) {
                    isValid = false;
                    showError('<?php echo esc_js( __( 'Please select your current professional status.', 'kazverse-artisan-plugin' ) ); ?>');
                }

                return isValid;
            }

            function showError(msg) {
                errorContainer.style.display = 'block';
                errorContainer.innerHTML     = msg;
            }

            // On button click => show errors if invalid, else proceed
            step6NextButton.addEventListener('click', function() {
                attemptedNext = true;

                const valid = validateStep6();
                if (!valid) {
                    // Stop here
                    return;
                }

                // Otherwise, store data
                const selectedRadio = Array.from(radioButtons).find(rb => rb.checked);
                let selectedStatus = '';
                if (selectedRadio) {
                    const labelElement = document.querySelector(`label[for="${selectedRadio.id}"]`);
                    if (labelElement) {
                        selectedStatus = labelElement.innerText.trim();
                    }
                }

                if (!window.kazverseRegistrationData.step6) {
                    window.kazverseRegistrationData.step6 = {};
                }
                window.kazverseRegistrationData.step6.professional_status = selectedStatus;

                console.log('Current Registration Data:', window.kazverseRegistrationData);

                // Move to Step 8
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

        <h2><?php echo esc_html__( 'What is your company name?', 'kazverse-artisan-plugin' ); ?></h2>
        <p><?php echo esc_html__( 'Only qualified tradesmen and service providers can use MyHammer.', 'kazverse-artisan-plugin' ); ?></p>

        <!-- GISA Number (Optional) -->
        <div class="f8_form_group form-group">
            <?php
            render_text_field(
                'f8_gisa_number',      // Name
                'f8_gisa_number',      // ID
                __( 'GISA (optional)', 'kazverse-artisan-plugin' ),     // Label
                __( 'Enter your GISA number', 'kazverse-artisan-plugin' ),  // Placeholder
                '',                    // Default value
                false                  // Not required
            );
            ?>
            <span id="gisa-error" class="error-msg" style="display:none; color:red;"></span>
        </div>

        <!-- Company Name (Required) -->
        <div class="f8_form_group form-group">
            <?php
            render_text_field(
                'f8_company_name',     // Name
                'f8_company_name',     // ID
                __( 'Company Name', 'kazverse-artisan-plugin' ),        // Label
                __( 'Enter your company name', 'kazverse-artisan-plugin' ), 
                '',                    // Default value
                true                   // Required
            );
            ?>
            <span id="companyName-error" class="error-msg" style="display:none; color:red;"></span>
        </div>

        <!-- Address (Required) -->
        <div class="f8_form_group form-group">
            <?php
            render_text_field(
                'f8_address',          // Name
                'f8_address',          // ID
                __( 'Address', 'kazverse-artisan-plugin' ),             // Label
                __( 'Enter your address', 'kazverse-artisan-plugin' ),
                '',                    
                true // Required
            );
            ?>
            <span id="address-error" class="error-msg" style="display:none; color:red;"></span>
        </div>

        <!-- Zip Code and City (Grouped) -->
        <div class="f8_form_group form-group">
            <?php
            render_zipcode_field_with_place_selector(
                'f8_zip_code',
                'f8_zip_code',
                __( 'eg. 5061 AA', 'kazverse-artisan-plugin' ),
                __( 'Zip Code *', 'kazverse-artisan-plugin' )
            );
            ?>
            <span id="zipCity-error" class="error-msg" style="display:none; color:red;"></span>
        </div>

        <!-- Navigation Buttons -->
        <button 
            type="button" 
            class="previous-button" 
            data-previous-step="6"
        >
            <?php echo esc_html__( 'Back', 'kazverse-artisan-plugin' ); ?>
        </button>
        <!-- Remove data-next-step so we can handle it ourselves -->
        <button 
            type="button" 
            class="next-button purple-btn" 
            id="step8ContinueBtn"
        >
            <?php echo esc_html__( 'Continue', 'kazverse-artisan-plugin' ); ?>
        </button>

        <!-- Error container if needed for combined messages -->
        <div id="step8-inline-error" style="display:none; color:red; margin-top:10px;"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // DOM Elements
        const gisaInput       = document.getElementById('f8_gisa_number');  
        const companyNameInput= document.getElementById('f8_company_name'); 
        const addressInput    = document.getElementById('f8_address');
        const zipCodeInput    = document.getElementById('f8_zip_code');
        const step8NextButton = document.getElementById('step8ContinueBtn');
        const globalError     = document.getElementById('step8-inline-error');

        // Inline spans
        const gisaError       = document.getElementById('gisa-error');
        const companyError    = document.getElementById('companyName-error');
        const addressError    = document.getElementById('address-error');
        const zipCityError    = document.getElementById('zipCity-error');

        // Basic numeric regex for zip
        const zipRegex = /^[0-9]+$/;

        // Track if user clicked "Continue" at least once
        let attemptedNext = false;

        // Whenever user types, if they've tried once, re-check
        [gisaInput, companyNameInput, addressInput, zipCodeInput].forEach(field => {
            field.addEventListener('input', function() {
                if (attemptedNext) {
                    validateAndShowErrors();
                }
            });
        });

        function validateAndShowErrors() {
            clearErrors();

            let isValid = true;

            // GISA is optional => no error if empty
            // If you had GISA format rules, you'd add them here.

            const companyVal = companyNameInput.value.trim();
            if (!companyVal) {
                isValid = false;
                showError(companyNameInput, companyError, '<?php echo esc_js( __( 'Company Name is required.', 'kazverse-artisan-plugin' ) ); ?>');
            }

            const addressVal = addressInput.value.trim();
            if (!addressVal) {
                isValid = false;
                showError(addressInput, addressError, '<?php echo esc_js( __( 'Address is required.', 'kazverse-artisan-plugin' ) ); ?>');
            }

            const zipVal = zipCodeInput.value.trim();
           if (!zipVal) {
                isValid = false;
                showError(zipCodeInput, zipCityError, '<?php echo esc_js( __( 'Zip code is required.', 'kazverse-artisan-plugin' ) ); ?>');
            } else {
                let zipPlaceDisplay = document.querySelector('.zip-place-display');

                if (!zipPlaceDisplay || zipPlaceDisplay.textContent.trim() === '') {
                    isValid = false;
                    showError(zipCodeInput, zipCityError, '<?php echo esc_js( __( 'Valid zip code is required.', 'kazverse-artisan-plugin' ) ); ?>');
                } else {
                    isValid = true;
                }
            }

            return isValid;
        }

        function showError(inputEl, errorEl, msg) {
            errorEl.style.display = 'inline';
            // If errorEl already has text, append with space for clarity
            if (errorEl.textContent) {
                errorEl.textContent += ' ' + msg;
            } else {
                errorEl.textContent = msg;
            }
            inputEl.classList.add('error-field');
        }

        function clearError(inputEl, errorEl) {
            errorEl.style.display = 'none';
            errorEl.textContent    = '';
            inputEl.classList.remove('error-field');
        }

        function clearErrors() {
            clearError(gisaInput,       gisaError);
            clearError(companyNameInput,companyError);
            clearError(addressInput,    addressError);
            clearError(zipCodeInput,    zipCityError);
            // Also hide global error if any
            globalError.style.display = 'none';
            globalError.innerHTML     = '';
        }

        // On button click => show errors if any. If valid => store data, next step
        step8NextButton.addEventListener('click', function() {
            attemptedNext = true;
            if (!validateAndShowErrors()) {
                return; // do not proceed
            }

            // If valid => store data
            const gisaVal       = gisaInput.value.trim();
            const companyVal    = companyNameInput.value.trim();
            const addressVal    = addressInput.value.trim();
            const zipVal        = zipCodeInput.value.trim();

            if (!window.kazverseRegistrationData.step8) {
                window.kazverseRegistrationData.step8 = {};
            }
            window.kazverseRegistrationData.step8.gisa_number   = gisaVal;
            window.kazverseRegistrationData.step8.company_name  = companyVal;
            window.kazverseRegistrationData.step8.address       = addressVal;
            window.kazverseRegistrationData.step8.zip_code      = zipVal;

            console.log('Current Registration Data:', window.kazverseRegistrationData);

            // Move to step 9
            const currentStep = document.querySelector('.form-step.active');
            const nextStep    = document.querySelector('.form-step-9');
            if (currentStep && nextStep) {
                currentStep.classList.remove('active');
                nextStep.classList.add('active');
            }
        });

        // Optionally add styling for .error-field
        (function addErrorStylingCSS() {
            const style = document.createElement('style');
            style.innerHTML = `
                .error-field {
                   
                    outline: none;
                }
                .error-msg {
                    margin-left: 10px;
                }
            `;
            document.head.appendChild(style);
        })();
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

        <h2><?php echo esc_html__( 'Qualifications', 'kazverse-artisan-plugin' ); ?></h2>
        <p><?php echo esc_html__( 'Please upload your business license', 'kazverse-artisan-plugin' ); ?></p>
        <p class="f9_subtitle">
            <?php echo esc_html__( 'You can upload either each page individually or a file with multiple pages.', 'kazverse-artisan-plugin' ); ?>
        </p>

        <!-- File Upload Field -->
        <div class="f9_form-group form-group">
            <?php
            render_file_upload_field(
                'business_license',                 // Name
                'business_license',                 // ID
                '',                                 // Label
                true,                               // Required
                ['accept' => 'image/png, image/jpeg, application/pdf'] // Accept specific types
            );
            ?>
            <p class="f9_file-info"><?php echo esc_html__( 'File: PNG, JPG, PDF, max. 15 MB', 'kazverse-artisan-plugin' ); ?></p>
        </div>

        <!-- Loading Indicator -->
        <div id="upload-loading" style="display:none; margin-top:10px;">
            <span><?php echo esc_html__( 'Uploading...', 'kazverse-artisan-plugin' ); ?></span>
        </div>

        <!-- Error message container -->
        <div class="step9-error" style="display:none; color:red; margin-top:10px;"></div>

        <!-- Navigation Buttons -->
        <button 
            type="button" 
            class="previous-button" 
            data-previous-step="8"
        >
            <?php echo esc_html__( 'Back', 'kazverse-artisan-plugin' ); ?>
        </button>

        <!-- Remove data-next-step to handle manually -->
        <button 
            type="button" 
            class="next-button purple-btn" 
            id="step9ContinueBtn"
        >
            <?php echo esc_html__( 'Continue', 'kazverse-artisan-plugin' ); ?>
        </button>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const businessLicenseInput = document.getElementById('business_license');
        const step9NextButton      = document.getElementById('step9ContinueBtn');
        const errorContainer       = document.querySelector('.step9-error');
        const loadingIndicator     = document.getElementById('upload-loading');

        const submitBtn      = document.querySelector('.submit-button');
        const redirectInput  = document.getElementById('redirect-url-field');

        submitBtn.addEventListener('click', function () {
            // 1. capture the full URL
            const fullUrl = window.location.href;  
            window.redirectUrl = fullUrl;   
            const pathSegments = window.location.pathname.split("/").filter(Boolean);
            const subdirectory = pathSegments[0] || ""; // "stateduntimed"

    // Build the base URL using the subdirectory

            // 2. copy it into the hidden input so PHP can see it
            redirectInput.value = fullUrl;
        });

        // Nonce for security
        const uploadNonce = '<?php echo wp_create_nonce("kazverse_upload_nonce"); ?>';

        // Allowed file extensions and size
        const allowedExtensions = ['png', 'jpg', 'jpeg', 'pdf'];
        const maxFileSize = 15 * 1024 * 1024; // 15 MB

        // Validate file before upload
        function validateFile() {
            let errors = [];
            const files = businessLicenseInput.files;

            if (!files || files.length === 0) {
                errors.push('<?php echo esc_js( __( 'Please select a file to upload.', 'kazverse-artisan-plugin' ) ); ?>');
            } else {
                const file = files[0];
                const fileName = file.name;
                const fileSize = file.size;
                const ext = fileName.split('.').pop().toLowerCase();

                if (!allowedExtensions.includes(ext)) {
                    errors.push('<?php echo esc_js( __( 'File must be PNG, JPG, or PDF.', 'kazverse-artisan-plugin' ) ); ?>');
                }
                if (fileSize > maxFileSize) {
                    errors.push('<?php echo esc_js( __( 'File must not exceed 15 MB.', 'kazverse-artisan-plugin' ) ); ?>');
                }
            }

            if (errors.length > 0) {
                errorContainer.style.display = 'block';
                errorContainer.innerHTML = errors.join('<br>');
                return false;
            } else {
                errorContainer.style.display = 'none';
                errorContainer.innerHTML = '';
                return true;
            }
        }

        // Handle "Continue" button click
        step9NextButton.addEventListener('click', function() {
            if (!validateFile()) {
                return;
            }

            const file = businessLicenseInput.files[0];
            if (!file) {
                return;
            }

            // Show loading and disable button
            loadingIndicator.style.display = 'block';
            step9NextButton.disabled = true;

            // Prepare FormData
            const formData = new FormData();
            formData.append('action', 'upload_business_license');
            formData.append('nonce', uploadNonce);
            formData.append('business_license', file);

            // AJAX upload
            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loadingIndicator.style.display = 'none';
                step9NextButton.disabled = false;

                if (data.success) {
                    // Store the URL in the global object
                    window.kazverseRegistrationData.step9 = window.kazverseRegistrationData.step9 || {};
                    window.kazverseRegistrationData.step9.business_license_url = data.data.url;

                    console.log('Uploaded File URL:', data.data.url);

                    // Proceed to next step
                    const currentStep = document.querySelector('.form-step.active');
                    const nextStep    = document.querySelector('.form-step-10');
                    if (currentStep && nextStep) {
                        currentStep.classList.remove('active');
                        nextStep.classList.add('active');
                    }
                } else {
                    // Show error
                    errorContainer.style.display = 'block';
                    errorContainer.innerHTML = data.data || '<?php echo esc_js( __( 'Upload failed.', 'kazverse-artisan-plugin' ) ); ?>';
                }
            })
            .catch(error => {
                loadingIndicator.style.display = 'none';
                step9NextButton.disabled = false;
                console.error('Upload error:', error);
                errorContainer.style.display = 'block';
                errorContainer.innerHTML = '<?php echo esc_js( __( 'Something went wrong. Please try again.', 'kazverse-artisan-plugin' ) ); ?>';
            });
        });

        // Optional: Re-validate on file change if needed
        businessLicenseInput.addEventListener('change', validateFile);
    });
    </script>

    <style>
        .error-field {
          
            outline: none;
        }
        .error-msg {
            margin-left: 10px;
        }
    </style>
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

        <h2><?php echo esc_html__( 'Create profile', 'kazverse-artisan-plugin' ); ?></h2>
        
        <h3 class="f10_heading"><?php echo esc_html__( 'Prepared for success', 'kazverse-artisan-plugin' ); ?></h3>
        <p class="f10_subheading"><?php echo esc_html__( '~ 5 mins', 'kazverse-artisan-plugin' ); ?></p>
        <p class="f10_description">
            <?php echo esc_html__( "You've almost made it!\nWe help you achieve your goals and get the orders you want.\n\nIn this step, we will set up your public profile and guide you through important steps to consider when using MyHammer.", 'kazverse-artisan-plugin' ); ?>
        </p>

        <!-- Navigation Buttons -->
        <button type="button" class="previous-button purple-btn" data-previous-step="9"><?php echo esc_html__( 'Back', 'kazverse-artisan-plugin' ); ?></button>
        <button type="button" class="next-button purple-btn" data-next-step="11"><?php echo esc_html__( 'Continue', 'kazverse-artisan-plugin' ); ?></button>
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

        <h2><?php echo esc_html__( 'Create profile', 'kazverse-artisan-plugin' ); ?></h2>
        
        <h3 class="f11_heading"><?php echo esc_html__( 'Introduce yourself to future customers', 'kazverse-artisan-plugin' ); ?></h3>
        <p class="f11_description">
            <?php echo esc_html__( 'This is your opportunity to make a good first impression. You can make changes to your profile at any time.', 'kazverse-artisan-plugin' ); ?>
        </p>

        <!-- Tip Box -->
        <div class="f11_tip-box">
            <i class="f11_icon">ℹ️</i>
            <span class="f11_tip-text">
                <?php
                /* translators: %s is a link */
                echo sprintf(
                    __( 'A good description can increase your chances of getting orders. <a href="#" class="f11_tip-link">%s</a>', 'kazverse-artisan-plugin' ),
                    __( 'Helpful Writing Tips', 'kazverse-artisan-plugin' )
                );
                ?>
            </span>
        </div>

        <!-- Text Area Field -->
        <div class="f11_form-group form-group">
            <textarea 
                id="f11_professional_description" 
                name="professional_description" 
                class="f11_textarea form-control" 
                placeholder="<?php echo esc_attr__( 'Beschrijf je professionele ervaring en expertise...', 'kazverse-artisan-plugin' ); ?>" 
                maxlength="1250" 
                rows="5"
            ></textarea>
            <div class="f11_character-count">
                <span id="f11_char_count">0</span> / 1250
            </div>
        </div>

        <!-- Error message container -->
        <div class="step11-error" style="display:none; color:red; margin-top:10px;"></div>

        <!-- Navigation Buttons -->
        <button 
            type="button" 
            class="previous-button purple-btn" 
            data-previous-step="10"
        >
            <?php echo esc_html__( 'Back', 'kazverse-artisan-plugin' ); ?>
        </button>

        <input
        type="hidden"
        name="redirect-url"
        id="redirect-url-field"
        value=""
        />

        <button 
            type="submit" 
            class="submit-button purple-btn"
            disabled
        >
            <?php echo esc_html__( 'Submit', 'kazverse-artisan-plugin' ); ?>
        </button>

        
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
                    errors.push("<?php echo esc_js( sprintf( __( 'Description must be at least %d characters long.', 'kazverse-artisan-plugin' ), 10 ) ); ?>");
                }
                if (length > maxDescriptionLength) {
                    errors.push("<?php echo esc_js( sprintf( __( 'Description cannot exceed %d characters.', 'kazverse-artisan-plugin' ), 1250 ) ); ?>");
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