jQuery(document).ready(function ($) {
    const watchVideoBtn = $('#ime-watch-video-btn');
    const videoPopup    = $('#ime-wizard-video-popup');
    const videoFrame    = $('#ime-wizard-video-frame');
    const closePopup    = $('#ime-wizard-close-popup');

    // YouTube Video URL - replace with your own
    const videoURL = "https://www.youtube.com/embed/NUmruo8gIVg?si=tKXhObIuUBTrZHcN&autoplay=1";

    // Open the popup and set video source
    watchVideoBtn.on('click', function () {
        videoFrame.attr('src', videoURL);
        videoPopup.css('display', 'flex');
    });

    // Close popup on close button click
    closePopup.on('click', function () {
        videoFrame.attr('src', '');
        videoPopup.css('display', 'none');
    });

    // Close popup when clicking outside the video frame
    videoPopup.on('click', function (e) {
        if (e.target === this) {
            videoFrame.attr('src', '');
            videoPopup.css('display', 'none');
        }
    });
});