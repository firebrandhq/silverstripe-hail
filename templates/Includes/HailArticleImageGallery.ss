<section id="hail-article-image-gallery" class="container">
    <h6 class="text-muted">
        <div class="photos-icon"></div>
        Photo Gallery
    </h6>
    <div class="row">
        <% if $Article.HeroImage %>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <a href="$Article.HeroImage.Urloriginal" class="swipebox" title="$Article.HeroImage.Caption">
                    <img src="$Article.HeroImage.Url150Square" alt="image"/>
                </a>
            </div>
        <% end_if %>
        <% if $Article.ImageGallery %>
            <% loop $Article.ImageGallery %>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <a href="$Urloriginal" class="swipebox" title="$Caption">
                        <img src="$Url150Square" alt="image"/>
                    </a>
                </div>
            <% end_loop %>
        <% end_if %>
    </div>
</section>