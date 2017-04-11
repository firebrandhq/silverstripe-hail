<% if $MoreThanOnePage %>
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="center-block pagination-wrapper">
				<ul class="pagination pagination-source">
                    <% if $NotFirstPage %>
                        <li><a href="$FirstLink" title="First Page"><i class="fa fa-angle-double-left"></i></a></li>
                        <li><a href="$PrevLink" title="Previous Page"><i class="fa fa-angle-left"></i></a></li>
                    <% end_if %>
					<% loop $Pages %>
						<% if $CurrentBool %>
                                <li class="active"><a class="source-page-$PageNum" href="$Link">$PageNum</a></li>
                        <% else %>
                                <li><a class="source-page-$PageNum" href="$Link">$PageNum</a></li>
                        <% end_if %>
					<% end_loop %>
                    <% if $NotLastPage %>
                        <li><a href="$NextLink" title="Next Page"><i class="fa fa-angle-right"></i></a></li>
                        <li><a href="$LastLink" title="Last Page"><i class="fa fa-angle-double-right"></i></a></li>
                    <% end_if %>
				</ul>
			</div>
		</div>
	</div>
<% end_if %>
