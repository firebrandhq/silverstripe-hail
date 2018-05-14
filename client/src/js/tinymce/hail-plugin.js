window.tinymce.PluginManager.add('sshail', function (editor) {
    // Add a button that opens a window
    editor.addButton('sshail', {
        title: 'Add a link to a Hail article',
        type: 'button',
        icon: 'hail',
        onclick: function () {
            // Open window and get Hail Article List
            jQuery.ajax({
                url: '/hail/articles',
                type: 'GET',
                contentType: 'application/json',
                success: function (response) {
                    editor.windowManager.open({
                        title: 'Add a link to a Hail article',
                        body: [
                            {
                                type: 'listbox',
                                name: 'link',
                                values: response
                            },
                            {
                                type: 'textbox',
                                name: 'title',
                                label: 'Link text'
                            },
                            {
                                type: 'textbox',
                                name: 'description',
                                label: 'Link description'
                            },
                            {
                                type: 'textbox',
                                name: 'anchor',
                                label: 'Anchor'
                            },
                            {
                                type: 'checkbox',
                                name: 'open',
                                label: 'Open in new window/tab'
                            }
                        ],
                        onsubmit: function (e) {
                            if (e.data.link !== "") {
                                var anchor = e.data.anchor && e.data.anchor.length ? `#${e.data.anchor}` : '';
                                var href = `${e.data.link}${anchor}`;
                                var target = e.data.open ? "_blank" : '_self';

                                editor.insertContent('<a href="' + href + '" title="' + e.data.description + '" target="' + target + '" rel="noopener">' + e.data.title + '</a>');
                            }
                        }
                    });
                }
            });

        }
    });

    return {
        getMetadata: function () {
            return {
                name: "Hail Plugin for SilverStripe 4",
                url: "https://github.com/firebrandhq/silverstripe-hail"
            };
        }
    };
});