<section id="hail-article-image-gallery" class="container">
    <h6 class="text-muted"><div class="photos-icon"></div>Photo Gallery</h6>
    <div class="row">
        <% loop $Article.ImageGallery %>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <a href="$Urloriginal" class="swipebox" title="$Caption">
                    <img src="$Url150Square" alt="image"/>
                </a>
            </div>
        <% end_loop %>
    </div>
</section>