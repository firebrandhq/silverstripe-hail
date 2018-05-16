<% if $HeroImage %>
    <% include HailPageHeader %>
<% end_if %>
<section class="container hail-page-container">
    <h1 class="hail-page-title">$Title</h1>
    <% if $Content %>
        <div class="row">
            <div class="col hail-page-content">
                $Content
            </div>
        </div>
    <% end_if %>

    <% if $FilterTags %>
        <% include HailPageFilters %>
    <% end_if %>

    <div class="row" id="hail-page-previews">
        <% loop $HailList %>
            <% if $getType == "article" %>
                <% include HailArticlePreview %>
            <% else %>
                <% include HailPublicationPreview %>
            <% end_if %>
        <% end_loop %>
    </div>

    <% if $HailList.MoreThanOnePage %>
        <% include HailPagePagination %>
    <% end_if %>
</section>
