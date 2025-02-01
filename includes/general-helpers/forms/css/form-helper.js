jQuery(document).ready(function ($) {
    // Regex for Dutch postcodes: 4 digits + 2 letters (case-insensitive)
    const nlZipRegex = /^\d{4}[A-Za-z]{2}$/;

    // When a suggestion is selected, mark the input as valid and fill in details.
    $(document).on("click", ".suggestion-item", function () {
        const selectedPostcode = $(this).data("zip");   // from data-zip
        const selectedPlace = $(this).data("place");      // from data-place
        const selectedLat = $(this).data("lat");          // from data-lat
        const selectedLon = $(this).data("lon");          // from data-lon

        const suggestionsBox = $(this).closest(".zip-suggestions");
        const inputField = suggestionsBox.siblings(".zip-input-field");
        const placeDisplay = suggestionsBox.siblings(".zip-place-display");
        const errorBox = $(`#${inputField.attr("id")}-error`);
        const latField = $(`#${inputField.attr("id")}-lat`);
        const lngField = $(`#${inputField.attr("id")}-lng`);

        // Set the selected postcode in the input field
        inputField.val(selectedPostcode);
        // Display the place name
        placeDisplay.text(selectedPlace);
        // Mark that a valid suggestion was selected
        inputField.data("selected", true);

        // Store lat/lon in hidden fields
        latField.val(selectedLat);
        lngField.val(selectedLon);

        // Hide suggestions & clear error
        suggestionsBox.hide();
        errorBox.hide().text("");

        // Optionally refocus the input
        inputField.focus();
    });

    // When the input value changes, clear the "selected" flag and update suggestions
    $(".zip-input-field").on("input", function () {
        $(this).removeData("selected");

        const zipCode = $(this).val().trim();
        const inputField = $(this);
        const inputId = inputField.attr("id");
        const suggestionsBox = $(`#${inputId}-suggestions`);
        const placeDisplay = $(`#${inputId}-place`);
        const errorBox = $(`#${inputId}-error`);
        const latField = $(`#${inputId}-lat`);
        const lngField = $(`#${inputId}-lng`);

        // Reset UI on new input
        placeDisplay.text("");
        suggestionsBox.empty().hide();
        errorBox.hide().text("");
        latField.val("");
        lngField.val("");

        // If user enters exactly 6 characters matching NL postcode format, show suggestions
        if (zipCode.length === 6 && nlZipRegex.test(zipCode)) {
            $.ajax({
                url: "https://nominatim.openstreetmap.org/search",
                dataType: "json",
                data: {
                    format: "json",
                    addressdetails: 1,
                    countrycodes: "nl",   // Restrict to Netherlands
                    postalcode: zipCode,    // The user input
                    limit: 5                // Limit number of suggestions
                },
                success: function (data) {
                    if (data && data.length > 0) {
                        data.forEach((location) => {
                            const locationPostcode = location?.address?.postcode || zipCode;
                            const locationCity =
                                location?.address?.city ||
                                location?.address?.town ||
                                location?.address?.village ||
                                location?.address?.hamlet ||
                                location.display_name ||
                                "Unknown";
                            const locationLat = location.lat;
                            const locationLon = location.lon;
                            const displayLabel = `${locationPostcode} - ${locationCity}`;

                            suggestionsBox.append(`
                                <div 
                                    class="suggestion-item"
                                    data-zip="${locationPostcode}"
                                    data-place="${locationCity}"
                                    data-lat="${locationLat}"
                                    data-lon="${locationLon}"
                                >
                                    ${displayLabel}
                                </div>
                            `);
                        });
                        suggestionsBox.show();
                    } else {
                        errorBox.text("No places found for this postcode.").show();
                    }
                },
                error: function () {
                    errorBox.text("Error while looking up postcode.").show();
                }
            });
        } else if (zipCode.length >= 6) {
            // If user typed >=6 characters but doesn't match the NL format, show error immediately.
            errorBox.text("Please enter a valid Netherlands postcode (e.g., 1234AB).").show();
        }
    });

    // On blur, validate the zipcode field.
    // If the field is valid but no suggestion has been manually selected,
    // automatically select the first suggestion (if available) to fill the place display.
    $(".zip-input-field").on("blur", function () {
        const inputField = $(this);
        const enteredZip = inputField.val().trim();
        const errorBox = $(`#${inputField.attr("id")}-error`);
        const suggestionsBox = $(`#${inputField.attr("id")}-suggestions`);

        // If nothing was selected from the suggestions...
        if (!inputField.data("selected")) {
            // If the entered zip code is valid...
            if (enteredZip !== "" && nlZipRegex.test(enteredZip)) {
                // If there is at least one suggestion, auto-select the first suggestion.
                const firstSuggestion = suggestionsBox.find(".suggestion-item").first();
                if (firstSuggestion.length) {
                    firstSuggestion.click();
                } else {
                    // If no suggestion is available, clear the field and show an error.
                    inputField.val('');
                    errorBox.text("No place information found for this postcode.").show();
                }
            } else {
                // Otherwise, if the zip is invalid, update the placeholder (if needed), clear the value, and show an error.
                if (enteredZip.length === 6) {
                    inputField.attr("placeholder", enteredZip);
                }
                inputField.val('');
                errorBox.text("Please enter a valid Netherlands postcode (e.g., 1234AB).").show();
            }
        }

        // Hide suggestions after a short delay.
        setTimeout(() => suggestionsBox.hide(), 200);
    });
});
