<% if $Article.Attachments %>
    <section id="hail-article-attachments" class="container">
        <h6 class="text-muted">
            <div class="attachments-icon"></div>
            Attachments
        </h6>
        <div class="row">
            <% loop $Article.Attachments %>
                <div class="col-12 attachments-col">
                    <a href="$Url">
                        <div class="files-icon"></div> {$UploadedName}.{$UploadedExtension}</a>
                </div>
            <% end_loop %>
        </div>
    </section>
<% end_if %>