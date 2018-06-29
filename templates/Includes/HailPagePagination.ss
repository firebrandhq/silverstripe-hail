<nav id="hail-page-pagination">
    <nav id="hail-page-pagination" data-totalitems="$HailList.TotalItems" data-totalpages="$HailList.TotalPages" data-pagelength="$HailList.PageLength">
        <ul class="pagination">
            <% if $HailList.CurrentPage == 1%>
                <li class="page-item"><a class="prev page-link disabled" disabled>Prev</a></li>
            <% else %>
                <li class="page-item"><a class="prev page-link" href="$HailList.PrevLink">Prev</a></li>
            <% end_if %>

            <% if $HailList.CurrentPage == $HailList.TotalPages %>
                <li class="page-item"><a class="next page-link disabled" disabled>Next</a></li>
            <% else %>
                <li class="page-item"><a class="next page-link" href="$HailList.NextLink">Next</a></li>
            <% end_if %>

            <li class="page-item"><div class="pagination-go-to"><input data-current="$HailList.CurrentPage" max="$HailList.TotalPages" min="0" type="number" value="$HailList.CurrentPage" />/ $HailList.TotalPages</div></li>
        </ul>
    </nav>
</nav>