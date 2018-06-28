<nav id="hail-page-pagination">
    <ul class="pagination">
        <% if $HailList.NotFirstPage %>
            <li class="page-item"><a class="prev page-link" href="$HailList.PrevLink">Prev</a></li>
        <% end_if %>
        <% loop $HailList.PaginationSummary(4) %>
            <% if $CurrentBool %>
                <li class="page-item"><a class="page-link disabled" disabled>$PageNum</a></li>
            <% else %>
                <% if $Link %>
                    <li class="page-item"><a class="page-link" href="$Link">$PageNum</a></li>
                <% else %>
                    <li class="page-item"><a class="page-link disabled" disabled>...</a></li>
                <% end_if %>
            <% end_if %>
        <% end_loop %>
        <% if $HailList.NotLastPage %>
            <li class="page-item"><a class="next page-link" href="$HailList.NextLink">Next</a></li>
        <% end_if %>
    </ul>
</nav>