jQuery(document).ready(function ($) {
    $('.kz-file-input').on('change', function () {
        const fileInput = $(this);
        const previewContainer = $('#' + fileInput.attr('id') + '_preview');
        const previewImage = previewContainer.find('img');
        const file = this.files[0];

        if (file) {
            const fileReader = new FileReader();

            fileReader.onload = function (e) {
                // If it's an image, show the image preview
                if (file.type.startsWith('image/')) {
                    previewImage.attr('src', e.target.result).show();
                    previewContainer.show();
                } else {
                    previewImage.hide();
                    previewContainer.hide();
                }
            };

            fileReader.readAsDataURL(file);
        } else {
            // Hide preview if no file is selected
            previewContainer.hide();
            previewImage.hide();
        }
    });
});
