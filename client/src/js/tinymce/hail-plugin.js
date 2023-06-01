window.tinymce.PluginManager.add('sshail', function (editor) {
    // Register the 'hail' icon
    editor.ui.registry.addIcon('hail', '<svg height="23" width="28"><image href="data:image/png;base64,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" height="23" width="28" /></svg>');

    // Add a button that opens a window
    editor.ui.registry.addButton('sshail', {
        type: 'button',
        icon: 'hail',
        onAction: () => {
            jQuery.ajax({
                url: '/hail/articles',
                type: 'GET',
                contentType: 'application/json',
                success: response => {
                    editor.windowManager.open({
                        title: 'Add Hail Article Link',
                        body: {
                            type: 'panel',
                            items: [
                                {
                                    type: 'listbox',
                                    name: 'link',
                                    items: response
                                },
                                {
                                    type: 'input',
                                    name: 'title',
                                    label: 'Link text'
                                },
                                {
                                    type: 'input',
                                    name: 'description',
                                    label: 'Link description'
                                },
                                {
                                    type: 'input',
                                    name: 'anchor',
                                    label: 'Anchor'
                                },
                                {
                                    type: 'checkbox',
                                    name: 'open',
                                    label: 'Open in new window/tab'
                                }
                            ]
                        },
                        buttons: [
                            {
                                type: 'cancel',
                                name: 'closeButton',
                                text: 'Cancel'
                            },
                            {
                                type: 'submit',
                                name: 'submitButton',
                                text: 'Add Link',
                                buttonType: 'primary'
                            }
                        ],
                        initialData: {
                            open: false
                        },
                        onSubmit: (api) => {
                            const data = api.getData();
                            if (data.link !== '' && data.title !== '') {
                                const link = document.createElement('a');
                                let href = data.link;
                                href += data.anchor ? `#${data.anchor}` : '';
                                link.setAttribute('href', href);
                                link.innerText = data.title;

                                if (data.open) {
                                    link.setAttribute('target', '_blank');
                                }

                                if (data.description) {
                                    link.setAttribute('title', data.description);
                                }

                                tinymce.activeEditor.execCommand('InsertHTML', false, link.outerHTML);
                                api.close();

                                return;
                            }

                            tinymce.activeEditor.windowManager.alert('Please select an article and enter a title');
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
