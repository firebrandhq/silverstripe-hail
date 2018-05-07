<div class="hail-preview col-md-6 col-lg-4">
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
                        <img class="hail-preview-image embed-responsive-item" src="resources/vendor/firebrand/silverstripe-hail/client/dist/images/placeholder-hero.jpg"></img>
                    </a>
                <% end_if %>
            </div>
        </div>
        <div class="card-body">
            <a href="$Link/$Title.CSSSafe" class="Link"><h5 class="card-title">$Title</h5></a>
            <h6 class="card-subtitle mb-2 text-muted">$DueDate.Format('eeee MMMM d, yyyy')</h6>
        </div>
    </div>
</div>