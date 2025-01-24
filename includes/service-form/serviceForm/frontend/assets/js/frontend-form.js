jQuery(document).ready(function ($) {
    const steps = $(".form-step");
    const nextButton = $(".next-step");
    const prevButton = $(".prev-step");
    const submitButton = $("#form-submit-button");
    const form = $("#dynamic-multi-step-form");

    let currentStep = 0;

    // Scroll smoothly to a specific step
    function scrollToStep(step) {
        const targetOffset = steps.eq(step).offset().top - 200; // Adjust 200px for padding above
        $("html, body").animate({ scrollTop: targetOffset }, 400);
    }

    // Detect scroll to top and remove the "form-interaction" class
    // Detect scroll to top and remove the "form-interaction" class after scrolling up by 50px
    function detectScrollToTop() {
        let lastScrollPosition = $(window).scrollTop();

        $(window).on("scroll", function () {
        const currentScroll = $(window).scrollTop();

        // Check if the user scrolled up by at least 50px
        if (lastScrollPosition > currentScroll){
            $(".form-group").removeClass("form-interaction");
        }

        // Update the last scroll position
        lastScrollPosition = currentScroll-8;
        });
    }

    // Validate a step before moving to the next
    function validateStep(step) {
        const inputs = steps.eq(step).find("input, textarea, select");
        let isValid = true;
    
        // Validate regular inputs
        inputs.each(function () {
            const input = $(this);
            const error = input.next(".error-message");
            const errorBox = input.closest(".zipcode-input-wrapper").next(".zip-error-box"); // Target the zip-error-box
    
            // Reset error box
            errorBox.text("").hide();
    
            // Required field validation for text, email, textarea, etc.
            if (input.prop("required") && !input.val().trim()) {
                isValid = false;
                input.addClass("error");
    
                if (error.length === 0) {
                    input.after('<span class="error-message">This field is required.</span>');
                }
            } else {
                input.removeClass("error");
                error.remove();
            }
    
            // Email validation
            if (input.attr("type") === "email" && input.val().trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.val().trim())) {
                    isValid = false;
                    input.addClass("error");
    
                    if (error.length === 0) {
                        input.after('<span class="error-message">Please enter a valid email address.</span>');
                    }
                }
            }
    
            // Zip code validation
            if (input.hasClass("zip-input-field")) {
                const zipCode = input.val().trim();
    
                if (zipCode.length !== 4) {
                    isValid = false;
                    input.addClass("error");
    
                    if (errorBox.length > 0) {
                        errorBox.text("ZIP code must be exactly 4 digits.").show();
                    }
                } else if (!ZipcodeHelper.validateZip(zipCode)) {
                    isValid = false;
                    input.addClass("error");
    
                    if (errorBox.length > 0) {
                        errorBox.text("Invalid or unsupported ZIP code.").show();
                    }
                } else {
                    input.removeClass("error");
                }
            }
        });
    
        // Validate radio and checkbox groups with data-require="true"
        steps.eq(step).find('.field-wrapper[data-require="true"]').each(function () {
            const fieldWrapper = $(this);
            const groupInputs = fieldWrapper.find('input[type="radio"], input[type="checkbox"]');
            const error = fieldWrapper.siblings(".error-message");
    
            // Check if at least one is selected
            if (groupInputs.length > 0 && !groupInputs.is(":checked")) {
                isValid = false;
    
                if (error.length === 0) {
                    fieldWrapper.after('<span class="error-message">Please select at least one option.</span>');
                }
            } else {
                error.remove();
            }
        });
    
        return isValid;
    }
    
    
    // Show the current step and scroll smoothly
    function showStep(step) {
        steps.each(function (index) {
            const $step = $(this);

            if (index <= step) {
                // Show the step with fade-in effect
                $step.stop(true, true).slideDown(300);
            } else {
                // Keep the rest hidden
                $step.stop(true, true).slideUp(300);
            }
        });

        prevButton.prop("disabled", step === 0);
        nextButton.toggle(step < steps.length - 1);
        submitButton.toggle(step === steps.length - 1);

        // Scroll to the current step
        scrollToStep(step);
    }

    // On clicking "Next"
    nextButton.on("click", function () {
        if (validateStep(currentStep)) {
            // Add "form-interaction" class to the last form-group of the current step
            steps.eq(currentStep).find(".form-group").last().addClass("form-interaction");

            currentStep++;
            showStep(currentStep);
        }
    });

    // On clicking "Back"
    prevButton.on("click", function () {
        if (currentStep > 0) {
            // Remove "form-interaction" class from the last form-group of the current step
            steps.eq(currentStep).find(".form-group").last().removeClass("form-interaction");

            currentStep--;
            showStep(currentStep);
        }
    });

    // On submitting the form
    form.on("submit", function (e) {
        if (!validateStep(currentStep)) {
            e.preventDefault();
        }
    });

    // Initialize with the first step visible
    showStep(currentStep);
    // Initialize the scroll detection for removing opacity
    detectScrollToTop();

});


jQuery(document).ready(function ($) {
    // Main submit handler
    $('#form-submit-button').on('click', function (e) {
        e.preventDefault();

        const formFields = getFormFields();
        const userDetails = getUserDetails();

        const formData = {
            form_name: $('#service-form-name').text().trim(),
            form_type: $('#service-form-name').attr('form-type').trim(),
            form_fields: formFields,
            user_details: userDetails,
        };
        

        console.log("Form data:", JSON.stringify(formData));
        console.table(formData.form_fields);
        console.table([formData.user_details]);

        // Submit via AJAX
        submitForm(formData);
    });

    // Extract form fields (questions and answers)
    function getFormFields() {
        const fields = [];
        $('#dynamic-multi-step-form')
            .find('input, textarea, select')
            .not('#zip_code, #name, #email, #phone') // Exclude user details fields
            .each(function () {
                const fieldType = $(this).attr('type');
                const question = $(this)
                    .closest('.form-group')
                    .find('label')
                    .text()
                    .trim();
                let answer = '';

                if (fieldType === 'checkbox') {
                    if ($(this).is(':checked')) {
                        answer = $(this).val();
                        const existingField = fields.find((item) => item.question === question);
                        if (existingField) {
                            existingField.answer += ', ' + answer;
                        } else {
                            fields.push({ question, answer });
                        }
                    }
                } else if (fieldType === 'radio') {
                    if ($(this).is(':checked')) {
                        answer = $(this).val();
                        fields.push({ question, answer });
                    }
                } else {
                    answer = $(this).val();
                    fields.push({ question, answer });
                }
            });
        return fields;
    }

    // Extract user details (zip code, name, email, and phone)
    function getUserDetails() {
        const zipCode = $('#zip_code').val() || '';
        const name = $('#name').val() || '';
        const email = $('#email').val() || '';
        const phone = $('#phone').val() || '';

        return {
            zip_code: zipCode,
            name: name,
            email: email,
            phone: phone,
        };
    }

    function showThankYouMessage(formData) {
        const userName = formData.user_details.name;
        const userEmail = formData.user_details.email;
        const userPhone = formData.user_details.phone;
        const formName = formData.form_name;
    
        // Create the thank-you message
        const message = `
            <div class="thank-you-message">
                <img src="${ajax_object.success_gif_url}" alt="Success" class="success-gif">
                <h2 class="thank-you-title">
                    Thank you, ${userName}, for requesting ${formName} service!
                </h2>
                <p class="thank-you-description">
                    Your request has been shared with all the available artisans. They will start contacting you soon using the following contact details:
                </p>
                <div class="user-details">
                    <p><strong>Name:</strong> ${userName}</p>
                    <p><strong>Email:</strong> ${userEmail}</p>
                    <p><strong>Phone:</strong> ${userPhone}</p>
                </div>
            </div>
        `;
    
        // Replace the form with the thank-you message
        $('.service-form').parent().html(message);
    }
    

    
    // AJAX form submission
    function submitForm(formData) {
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'submit_service_form',
                form_data: JSON.stringify(formData),
            },
            beforeSend: function () {
                $('#form-submit-button').prop('disabled', true).text('Submitting...');
            },
            success: function (response) {
                if (response.success) {
                    showThankYouMessage(formData);
                } else {
                    alert(response.data.message); // Error message
                }
            },
            error: function () {
                alert('An error occurred while submitting the form.');
            },
            complete: function () {
                $('#form-submit-button').prop('disabled', false).text('Submit');
            },
        });
    }
});
