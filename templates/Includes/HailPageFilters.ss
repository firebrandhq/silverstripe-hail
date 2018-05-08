<div id="hail-page-filters" class="d-flex align-content-center flex-wrap">
    <a class="btn btn-outline-primary mx-1 <% if $currentTagFilter == 'none' %> active <% end_if %>" href="$Top.Link">Show All</a>
    <% loop $FilterTags %>
        <a class="btn btn-outline-primary mx-1 <% if $Top.currentTagFilter == $HailID %> active <% end_if %>" href="{$Top.Link}tag/$HailID/$Name.CSSSafe">$Title</a>
    <% end_loop %>
</div>