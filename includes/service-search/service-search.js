jQuery(document).ready(function ($) {
    const $input = $('#service-search-input');
    const $results = $('#service-search-results');
    const $clearButton = $('#service-search-clear');
    const $submitButton = $('#service-search-submit');

    // Fetch results based on query
    function fetchResults(query = '') {
        $.ajax({
            url: ajax_object_search.ajax_url,
            method: 'GET',
            data: {
                action: 'fetch_service_forms',
                query: query, // Send empty string to fetch all results
            },
            beforeSend: function () {
                $results.addClass('visible'); // Show the results container
                $results.html('<li class="loading"><span class="loader"></span></li>');
            },
            success: function (response) {
                $results.empty();
                if (response.length) {
                    response.forEach((item) => {
                        $results.append(
                            `<li data-link="${item.link}">${item.title}</li>`
                        );
                    });

                    // Attach click handler to each list item
                    $results.find('li').on('click', function () {
                        const link = $(this).data('link');
                        window.location.href = link; // Navigate to the link
                    });
                } else {
                    $results.html('<li>No results found.</li>');
                }
            },
            error: function () {
                $results.html('<li>Error fetching results. Please try again.</li>');
            },
        });
    }

    // Show results container on focus or click
    $input.on('focus', function () {
        const query = $input.val().trim();
        fetchResults(query); // Fetch all if query is empty
    });

    // Hide results container when input loses focus
    $input.on('blur', function () {
        setTimeout(() => {
            $results.removeClass('visible');
        }, 200); // Add delay to allow clicking results
    });

    // Input change event
    $input.on('input', function () {
        const query = $input.val().trim();
        fetchResults(query);
    });

    // Clear search button
    $clearButton.on('click', function () {
        $input.val('');
        fetchResults(''); // Fetch all results when cleared
    });

    // Submit button click event
    $submitButton.on('click', function () {
        const query = $input.val().trim();
        fetchResults(query); // Perform AJAX call when submit button is clicked
    });
});
