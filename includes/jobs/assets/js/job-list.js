jQuery(document).ready(function ($) {
    $('.view-job-button').on('click', function () {
        var jobDataString = $(this).closest('.job-item').attr('data-job-details');
        try {
            var jobData = JSON.parse(jobDataString.replace(/&quot;/g, '"'));
        } catch (error) {
            console.error("Error parsing job details:", error);
            return;
        }

        // Populate "Brief" tab using the new keys
        $('#overlay-job-title').text(jobData.title || 'No Title');
        $('#overlay-posted-time').text(jobData.posted || 'N/A'); // changed from jobData.time_ago
        $('#overlay-location').text(jobData.city || 'Unknown');
        $('#overlay-distance').text(jobData.distance || 'Unknown'); // uses the new "distance" key
        
        // If service_type is an array, join it
        var serviceType = jobData.service_type;
        if (Array.isArray(serviceType)) {
            serviceType = serviceType.join(', ');
        }
        $('#overlay-service-type').text(serviceType || 'N/A');

        // Optional short excerpt / overview
        $('#overlay-excerpt').text(jobData.excerpt || '');

        // Q&A details
        var detailsTable = $('#overlay-job-details');
        detailsTable.empty().append('<tr><th>Question</th><th>Answer</th></tr>');
        if (Array.isArray(jobData.details)) {
            jobData.details.forEach(function (item) {
                detailsTable.append('<tr><td>' + item.question + '</td><td>' + item.answer + '</td></tr>');
            });
        }

        // Populate "Client Info" tab
        $('#overlay-client-name').text(jobData.client_name || 'N/A');
        $('#overlay-client-email').text(jobData.client_email || 'N/A');
        $('#overlay-client-phone').text(jobData.client_phone || 'N/A');

        // Show overlay & backdrop
        $('#job-detail-overlay').removeClass('hidden').addClass('visible');
        $('#overlay-backdrop').removeClass('hidden').addClass('visible');

        // Default to Brief tab
        $('.overlay-tab').removeClass('active');
        $('[data-tab="brief-tab"]').addClass('active');
        $('#brief-tab').show();
        $('#client-info-tab').hide();
    });

    // Closing overlay
    $('.close-overlay, #overlay-backdrop').on('click', function () {
        $('#job-detail-overlay').removeClass('visible').addClass('hidden');
        $('#overlay-backdrop').removeClass('visible').addClass('hidden');
    });

    // Tab switching
    $('.overlay-tab').on('click', function () {
        $('.overlay-tab').removeClass('active');
        $(this).addClass('active');
        var tab = $(this).data('tab');
        $('.tab-content').hide();
        $('#' + tab).show();
    });
});
