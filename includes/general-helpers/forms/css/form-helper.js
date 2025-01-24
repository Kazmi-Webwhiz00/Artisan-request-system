jQuery(document).ready(function ($) {
    // Event listener for zip code input
    $(".zip-input-field").on("input", function () {
        const zipCode = $(this).val(); // Get current input value
        const suggestionsBox = $(`#${$(this).attr("id")}-suggestions`);
        const placeDisplay = $(`#${$(this).attr("id")}-place`); // Get the related span
        const errorBox = $(`#${$(this).attr("id")}-error`); // Error display div

        // Reset suggestions, place display, and error box
        placeDisplay.text("");
        suggestionsBox.empty().hide();
        errorBox.hide();

        // If 4 digits are entered
        if (zipCode.length === 4) {
            if (ZipcodeHelper.validateZip(zipCode)) {
                // Populate suggestions
                const places = ZipcodeHelper.getPlacesForZip(zipCode);
                places.forEach((place) => {
                    suggestionsBox.append(`<div class="suggestion-item">${place}</div>`);
                });
                suggestionsBox.show();
            } else {
                // Show error message if zip code is invalid
                errorBox.text("Invalid or unsupported ZIP code").show();
            }
        }
    });

    // Event delegation for selecting a suggestion
    $(document).on("click", ".suggestion-item", function () {
        const selectedPlace = $(this).text();
        const suggestionsBox = $(this).closest(".zip-suggestions");
        const placeDisplay = suggestionsBox.siblings(".zip-place-display");
        const inputField = suggestionsBox.siblings(".zip-input-field");
        const errorBox = $(`#${inputField.attr("id")}-error`); // Error box

        // Set the selected place name in the display
        placeDisplay.text(`${selectedPlace}`);
        suggestionsBox.hide();
        errorBox.hide(); // Clear error if user selects a valid place

        // Optionally focus back on the input field after selection
        inputField.focus();
    });

    // Hide suggestions on blur
    $(".zip-input-field").on("blur", function () {
        const suggestionsBox = $(`#${$(this).attr("id")}-suggestions`);
        setTimeout(() => suggestionsBox.hide(), 200); // Delay to allow click event
    });
});
