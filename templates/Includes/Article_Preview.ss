<div class="hail-preview col-sm-6 col-md-4 col-lg-3 $TagList">
    <div class="hail-preview-wrapper">
        <div class="hail-preview-hero">
            <% if $HeroVideo %>
                $HeroVideo.ThumbNail
            <% else_if $HeroImage %>
                <a href="$Link" class="Link">
                    <div class="hail-preview-image" style="background-image:url('{$HeroImage.Url500}'); background-position: {$HeroImage.RelativeCenterX}% {$HeroImage.RelativeCenterY}%;"></div>
                </a>
            <% else %>
                <a href="$Link" class="Link">
                    <div class="hail-preview-hero-placeholder"></div>
                </a>
            <% end_if %>
        </div>
        <a class="hail-preview-title" href="$Link" class="Link">$Title</a>
        <div class="hail-preview-desc">
            $Lead
        </div>
    </div>
</div>