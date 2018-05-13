<% include HailArticleHeader %>

<section id="hail-article-container" class="container">
    <div id="hail-article-tags">
        <% loop $Article.PublicTags %>
            <span class="badge badge-primary">$Name</span>
        <% end_loop %>
    </div>
    <h1 id="hail-article-title">$Article.Title</h1>
    <h6 class="mb-2 text-muted">$Article.Date.Format('eeee MMMM d, yyyy')</h6>
    <div id="hail-article-content">
        $Article.Content
    </div>
</section>

<% include HailArticleImageGallery %>

<% include HailArticleVideoGallery %>

<% include HailArticleAttachments %>
