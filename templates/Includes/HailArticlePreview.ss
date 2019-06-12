<div class="hail-preview col-md-6 col-lg-4 $TagList">
    <div class="card">
        <div class="card-img-top">
            <div class="embed-responsive embed-responsive-16by9">
                <% if $HeroVideo %>
                    <div class="hail-play-button" data-type="$HeroVideo.Service" data-embed="$HeroVideo.ServiceData"></div>
                    <img class="embed-responsive-item" src="$HeroVideo.Url500">
                <% else_if $HeroImage %>
                    <a href="$Link" class="Link">
                        <div class="hail-preview-image embed-responsive-item"
                             style="background-image:url('{$HeroImage.Url500}'); background-position: {$HeroImage.RelativeCenterX}% {$HeroImage.RelativeCenterY}%;"></div>
                    </a>
                <% else %>
                    <a href="$Link" class="Link">
                        <img class="hail-preview-image embed-responsive-item" src="$PlaceHolderHero"></img>
                    </a>
                <% end_if %>
            </div>
        </div>
        <div class="card-body">
            <div class="card-tags">
                <% loop $PublicTags.Limit(2) %>
                    <span class="badge badge-primary">$Name</span>
                <% end_loop %>
                <% if $PublicTags.Limit(9999, 2) %>
                    <span class="badge badge-primary">+ $PublicTags.Limit(9999, 2).Count More</span>
                <% end_if %>
            </div>
            <a href="$Link" class="Link"><h5 class="card-title">$Title</h5></a>
            <h6 class="card-subtitle mb-2 text-muted">$Created.Format('eeee MMMM d, yyyy')</h6>
            <div class="card-text">
                $Lead
            </div>
        </div>
    </div>
</div>
