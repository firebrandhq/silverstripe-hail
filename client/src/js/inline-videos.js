jQuery(function ($) {
    $('.hail-inline').each(function (i, element) {
        var inlineElement = $(element);
        var serviceType = inlineElement.data('external-video-service');
        var videoId = inlineElement.data('external-video-id');
        if(serviceType && videoId){
            var videoContainer = $('<div class="hail-inline-embed"><div class="embed-responsive embed-responsive-16by9"></div></div>');
            var iframe = null;

            if (serviceType === "youtube") {
                iframe = document.createElement("iframe");
                iframe.setAttribute("frameborder", "0");
                iframe.setAttribute("allowfullscreen", "");
                iframe.setAttribute("mozallowfullscreen", "");
                iframe.setAttribute("webkitallowfullscreen", "");
                iframe.setAttribute("src", "https://www.youtube.com/embed/" + videoId);
            } else if (serviceType === "vimeo") {
                iframe = document.createElement("iframe");
                iframe.setAttribute("frameborder", "0");
                iframe.setAttribute("allowfullscreen", "");
                iframe.setAttribute("mozallowfullscreen", "");
                iframe.setAttribute("webkitallowfullscreen", "");
                iframe.setAttribute("src", "https://player.vimeo.com/video/" + videoId);
            }

            if(iframe){
                videoContainer.find('.embed-responsive').append(iframe);
                //Replace element with new embed
                inlineElement.replaceWith(videoContainer);
            }
        }
    });
});
