jQuery(function ($) {
    $('#hail-container').on('click', '.hail-video-youtube', function () {
        var iframe = document.createElement("iframe");

        iframe.setAttribute("frameborder", "0");
        iframe.setAttribute("allowfullscreen", "");
        iframe.setAttribute("src", "https://www.youtube.com/embed/" + this.dataset.embed + "?rel=0&showinfo=0&autoplay=1");

        $(this).html("");
        $(this).append(iframe);
    });
});