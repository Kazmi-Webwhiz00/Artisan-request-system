jQuery(document).ready(function ($) {
    const steps = $(".form-step");
    const nextButton = $(".next-step");
    const prevButton = $(".prev-step");
    const submitButton = $(".submit-button");
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

        inputs.each(function () {
            const input = $(this);
            const error = input.next(".error-message");

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
