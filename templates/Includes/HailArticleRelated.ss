<% if $Related %>
    <section id="hail-article-related" class="container">
        <h4 class="text-muted">
            <div class="news-icon"></div>
            See also...
        </h4>
        <div class="row">
            <% loop $Related %>
                <% include HailArticlePreview %>
            <% end_loop %>
        </div>
    </section>
<% end_if %>