jQuery(document).ready(function ($) {
    $('.artisan-meta-group').first().addClass('open').find('.artisan-meta-group-content').show();
    $('.artisan-meta-group-title').on('click', function () {
        $(this).next('.artisan-meta-group-content').slideToggle();
    });
});
