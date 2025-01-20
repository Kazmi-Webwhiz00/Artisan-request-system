<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . '../general-helpers/forms/text-field.php';
require_once plugin_dir_path( __FILE__ ) . '../general-helpers/forms/select-field.php';
require_once plugin_dir_path( __FILE__ ) . '../general-helpers/forms/email-field.php';
require_once plugin_dir_path( __FILE__ ) . '../general-helpers/forms/phone-field.php';
require_once plugin_dir_path( __FILE__ ) . '../general-helpers/forms/password-field.php';
require_once plugin_dir_path( __FILE__ ) . '../general-helpers/forms/checkbox-field.php';

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
    <style>
        .form-step-1 {
            /* Step 1 specific styling */
        }
    </style>
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
    <style>
        .form-step-3 {
            text-align: center;
            font-family: Arial, sans-serif;
        }

        .progress {
            margin-bottom: 20px;
        }

        .progress-bar {
            background-color: #6b52ae;
            height: 10px;
        }

        h2 {
            color: #6b52ae;
        }

        p {
            font-size: 1.1em;
            color: #333;
        }

        .next-button,
        .previous-button {
            margin-top: 20px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // No specific JS for Step 3 yet
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
            // Additional steps will go here
            ?>
        </form>
    </div>
    <style>
        /* Shared styling */
        #artisan-registration-form {
            max-width: 600px;
            margin: 0 auto;
            font-family: Arial, sans-serif;
        }
        .form-step {
            display: none;
        }
        .form-step.active {
            display: block;
        }
        .form-group {
            margin-bottom: 20px;
        }
    </style>
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
