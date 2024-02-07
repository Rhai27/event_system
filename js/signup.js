// script.js
$(document).ready(function() {
    // Empty array for no photos
    var images = [];

    function changeBackground() {
        if (images.length > 0) {
            $('.background-slider img').fadeOut(1000, function() {
                $(this).remove(); // Remove the current image
                $('.background-slider').append('<img src="" alt="Background Photo">'); // Add a new empty image
                $('.background-slider img').fadeIn(1000);
            });
        }
    }
});
