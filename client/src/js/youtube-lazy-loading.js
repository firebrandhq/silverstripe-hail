var youtubeLazyLoaders = document.querySelectorAll(".hail-video-youtube");
for (var i = 0; i < youtubeLazyLoaders.length; i++) {
    youtubeLazyLoaders[i].addEventListener("click", function () {

        var iframe = document.createElement("iframe");

        iframe.setAttribute("frameborder", "0");
        iframe.setAttribute("allowfullscreen", "");
        iframe.setAttribute("src", "https://www.youtube.com/embed/" + this.dataset.embed + "?rel=0&showinfo=0&autoplay=1");

        this.innerHTML = "";
        this.appendChild(iframe);
    });
}