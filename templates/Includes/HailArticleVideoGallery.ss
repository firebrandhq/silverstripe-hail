<% if $Article.HasVideos %>
    <section id="hail-article-video-gallery" class="container">
        <h6 class="text-muted">
            <div class="videos-icon"></div>
            Video Gallery
        </h6>
        <div class="row">
            <% loop $Article.AllVideos %>
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
        </div>
    </section>
<% end_if %>