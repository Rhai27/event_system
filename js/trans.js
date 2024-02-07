// Add this script in your HTML or a separate JavaScript file
document.addEventListener("DOMContentLoaded", function() {
    const imageContainer = document.querySelector(".image-container");
    const images = document.querySelectorAll(".welcome-image");

    let currentIndex = 0;

    function showNextImage() {
        images[currentIndex].style.opacity = "0";
        currentIndex = (currentIndex + 1) % images.length;
        images[currentIndex].style.opacity = "1";
    }

    setInterval(showNextImage, 300000); // Adjust the interval (in milliseconds) as needed
});

