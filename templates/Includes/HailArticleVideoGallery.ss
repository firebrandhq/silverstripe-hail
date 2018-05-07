<section id="hail-article-video-gallery" class="container">
    <h6 class="text-muted">
        <div class="videos-icon"></div>
        Video Gallery
    </h6>
    <div class="row">
        <% if $Article.HeroVideo %>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <% if $Article.HeroVideo.Service == "youtube" %>
                    <a href="https://www.youtube.com/watch?v={$Article.HeroVideo.ServiceData}" class="swipebox-video" title="$Article.HeroVideo.Caption">
                        <img src="$Article.HeroVideo.Url150Square" alt="image"/>
                    </a>
                <% else_if $Article.HeroVideo.Service == "vimeo" %>
                    <a href="https://vimeo.com/{$Article.HeroVideo.ServiceData}" class="swipebox-video" title="$Article.HeroVideo.Caption">
                        <img src="$Article.HeroVideo.Url150Square" alt="image"/>
                    </a>
                <% end_if %>
            </div>
        <% end_if %>
        <% if $Article.VideoGallery %>
            <% loop $Article.VideoGallery %>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <% if $Service == "youtube" %>
                        <a href="https://www.youtube.com/watch?v={$ServiceData}" class="swipebox-video" title="$Caption">
                            <img src="$Url150Square" alt="image"/>
                        </a>
                    <% else_if $Service == "vimeo" %>
                        <a href="https://vimeo.com/{$ServiceData}" class="swipebox-video" title="$Caption">
                            <img src="$Url150Square" alt="image"/>
                        </a>
                    <% end_if %>
                </div>
            <% end_loop %>
        <% end_if %>
    </div>
</section>