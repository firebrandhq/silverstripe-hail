<% include HailArticleHeader %>

<section id="hail-article-container" class="container">
    <h1 id="hail-article-title">$Article.Title</h1>
    <h6 class="mb-2 text-muted">$Article.Date.Format('eeee MMMM d, yyyy')</h6>
    <div id="hail-article-content">
        $Article.Content
    </div>
</section>

<% if $Article.ImageGallery %>
    <% include HailArticleImageGallery %>
<% end_if %>

<% if $Article.VideoGallery %>
    <% include HailArticleVideoGallery %>
<% end_if %>

<% if $Article.Attachments %>
    <% include HailArticleAttachments %>
<% end_if %>