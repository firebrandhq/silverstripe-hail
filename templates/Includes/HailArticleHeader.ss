<header id="hail-article-header">
    <% if $Article.HeroVideo %>
        <div class="hail-article-header-image"
             style="background-image:url('{$Article.HeroVideo.Urloriginal}'); background-position: {$Article.HeroVideo.RelativeCenterX}% {$Article.HeroVideo.RelativeCenterY}%;"></div>
    <% else_if $Article.HeroImage %>
        <div class="hail-article-header-image"
             style="background-image:url('{$Article.HeroImage.Urloriginal}'); background-position: {$Article.HeroImage.RelativeCenterX}% {$Article.HeroImage.RelativeCenterY}%;"></div>
    <% else %>
        <div class="hail-article-header-image"
             style="background-image:url('$PlaceHolderHero'); background-position: 50% 50%;"></div>
    <% end_if %>
</header>
