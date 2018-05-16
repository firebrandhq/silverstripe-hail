jQuery(function ($) {
    $('#hail-page-previews, .cms-container, #hail-article-video-gallery').on('click', '.hail-play-button', function () {
        var playButton = $(this);
        var serviceType = playButton.data('type');
        var videoContainer = playButton.closest('.embed-responsive');

        if (serviceType === "youtube") {
            var iframe = document.createElement("iframe");
            iframe.setAttribute("frameborder", "0");
            iframe.setAttribute("allowfullscreen", "");
            iframe.setAttribute("mozallowfullscreen", "");
            iframe.setAttribute("webkitallowfullscreen", "");
            iframe.setAttribute("src", "https://www.youtube.com/embed/" + playButton.data('embed') + "?rel=0&showinfo=0&autoplay=1");

            videoContainer.html("").append(iframe);
            //Hide tags
            videoContainer.closest('.card').find('.card-tags').addClass('d-none');

        } else if (serviceType === "vimeo") {
            var iframe = document.createElement("iframe");
            iframe.setAttribute("frameborder", "0");
            iframe.setAttribute("allowfullscreen", "");
            iframe.setAttribute("mozallowfullscreen", "");
            iframe.setAttribute("webkitallowfullscreen", "");
            iframe.setAttribute("src", "https://player.vimeo.com/video/" + playButton.data('embed') + "?autoplay=1&title=0&byline=0&portrait=0&badge=0");

            videoContainer.html("").append(iframe);
            //Hide tags
            videoContainer.closest('.card').find('.card-tags').addClass('d-none');

        } else {
            console.log('Could not play video, unsupported service.');
        }
    });
});