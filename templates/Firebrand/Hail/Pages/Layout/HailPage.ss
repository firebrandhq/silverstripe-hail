<div class="container">
    <h1>$Title</h1>
    <div class="row">
        <div class="col">
            $Content
        </div>
    </div>
    <% if $FilterTags %>
        <div class="row">
            <div class="col" id="hail-filters">
                <a class="hail-btn-filter btn" href="$Top.Link">Show All</a>
                <% loop $FilterTags %>
                    <a class="hail-btn-filter btn" href="{$Top.Link}tag/$HailID">$Title</a>
                <% end_loop %>
            </div>
        </div>
    <% end_if %>

    <div class="row" id="hail-container">
        <% loop $HailList %>
            <% if $getType == "article" %>
                <% include Article_Preview %>
            <% else %>
                <% include Publication_Preview %>
            <% end_if %>
        <% end_loop %>
    </div>
    <% if $HailList.MoreThanOnePage %>
        <div class="row" id="hail-pagination">
            <% if $HailList.NotFirstPage %>
                <a class="prev hail-pagination-button" href="$HailList.PrevLink">Prev</a>
            <% end_if %>
            <% loop $HailList.Pages %>
                <% if $CurrentBool %>
                    <a class="hail-pagination-button" disabled>$PageNum</a>
                <% else %>
                    <% if $Link %>
                        <a class="hail-pagination-button" href="$Link">$PageNum</a>
                    <% else %>
                        ...
                    <% end_if %>
                <% end_if %>
            <% end_loop %>
            <% if $HailList.NotLastPage %>
                <a class="next hail-pagination-button" href="$HailList.NextLink">Next</a>
            <% end_if %>
        </div>
    <% end_if %>
</div>
