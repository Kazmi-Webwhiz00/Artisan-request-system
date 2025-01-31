jQuery(document).ready(function ($) {
    // Regex for Dutch postcodes: 4 digits + 2 letters (case-insensitive)
    const nlZipRegex = /^\d{4}[A-Za-z]{2}$/;

    // Event listener for zip code input
    $(".zip-input-field").on("input", function () {
        const zipCode = $(this).val().trim();
        const inputField = $(this);
        const inputId = inputField.attr("id");
        const suggestionsBox = $(`#${inputId}-suggestions`);
        const placeDisplay = $(`#${inputId}-place`);
        const errorBox = $(`#${inputId}-error`);
        const latField = $(`#${inputId}-lat`);
        const lngField = $(`#${inputId}-lng`);

        // Reset UI
        placeDisplay.text("");
        suggestionsBox.empty().hide();
        errorBox.hide().text("");
        latField.val("");
        lngField.val("");

        // If user enters 6 characters matching NL postcode pattern (e.g. 1234AB)
        if (zipCode.length === 6 && nlZipRegex.test(zipCode)) {
            $.ajax({
                url: "https://nominatim.openstreetmap.org/search",
                dataType: "json",
                data: {
                    format: "json",
                    addressdetails: 1,
                    countrycodes: "nl",   // Restrict to Netherlands
                    postalcode: zipCode,  // The user input
                    limit: 5              // Limit number of suggestions
                },
                success: function (data) {
                    if (data && data.length > 0) {
                        data.forEach((location) => {
                            // Extract or fallback to what's available
                            const locationPostcode = location?.address?.postcode || zipCode;
                            const locationCity =
                                location?.address?.city ||
                                location?.address?.town ||
                                location?.address?.village ||
                                location?.address?.hamlet ||
                                location.display_name ||
                                "Unknown";
                            // Lat/lon
                            const locationLat = location.lat;
                            const locationLon = location.lon;

                            // We'll show e.g. "1234AB - Amsterdam"
                            const displayLabel = `${locationPostcode} - ${locationCity}`;

                            // Append suggestion with data attributes
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
                        // No results for this postcode
                        errorBox.text("No places found for this postcode.").show();
                    }
                },
                error: function () {
                    errorBox.text("Error while looking up postcode.").show();
                }
            });
        } else if (zipCode.length >= 6) {
            // If user typed >=6 chars, but doesn't match the NL format
            errorBox.text("Please enter a valid Dutch postcode (e.g., 1234AB).").show();
        }
    });

    // Event delegation for selecting a suggestion
    $(document).on("click", ".suggestion-item", function () {
        const selectedPostcode = $(this).data("zip");   // from data-zip
        const selectedPlace = $(this).data("place");    // from data-place
        const selectedLat = $(this).data("lat");        // from data-lat
        const selectedLon = $(this).data("lon");        // from data-lon

        const suggestionsBox = $(this).closest(".zip-suggestions");
        const placeDisplay = suggestionsBox.siblings(".zip-place-display");
        const inputField = suggestionsBox.siblings(".zip-input-field");
        const errorBox = $(`#${inputField.attr("id")}-error`);
        const latField = $(`#${inputField.attr("id")}-lat`);
        const lngField = $(`#${inputField.attr("id")}-lng`);

        // Set the selected postcode in the input field
        inputField.val(selectedPostcode);
        // Display the place name
        placeDisplay.text(selectedPlace);

        // Store lat/lon in hidden fields
        latField.val(selectedLat);
        lngField.val(selectedLon);

        // Hide suggestions & clear error
        suggestionsBox.hide();
        errorBox.hide().text("");

        // Optionally refocus the input
        inputField.focus();
    });

    // Hide suggestions on blur after a short delay
    $(".zip-input-field").on("blur", function () {
        const suggestionsBox = $(`#${$(this).attr("id")}-suggestions`);
        setTimeout(() => suggestionsBox.hide(), 200);
    });
});
