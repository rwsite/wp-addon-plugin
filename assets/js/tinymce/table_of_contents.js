(function() {
    tinymce.PluginManager.add('table_of_contents', function( editor, url ) {
        editor.addButton('table_of_contents', {
            title : 'Table of content',
            image: 'https://img.icons8.com/wired/64/000000/overview-pages-4.png',
            //'https://img.icons8.com/dotty/80/000000/overview-pages-1.png',
            //icon: 'dashicons-text-page',
            onclick: function() {
                editor.windowManager.open( {
                    title: 'Shortcode params',
                    body: [
                        {
                            type: 'textbox', // тип textbox = текстовое поле
                            name: 'title', // ID, будет использоваться ниже
                            label: 'Title', // лейбл
                            value: '' // значение по умолчанию
                        },
                        {
                            type: 'textbox', // тип textbox = текстовое поле
                            name: 'headings', // ID, будет использоваться ниже
                            label: 'Headings', // лейбл
                            value: 'h2,h3,h4' // значение по умолчанию
                        },
                        {
                            type: 'textbox', // тип textbox = текстовое поле
                            name: 'content', // ID, будет использоваться ниже
                            label: 'jQuery Selector of content', // лейбл
                            value: 'div.post-content' // значение по умолчанию
                        },
                        {
                            type: 'listbox', // тип listbox = выпадающий список select
                            name: 'style',
                            label: 'Choose style',
                            'values': [ // значения выпадающего списка
                                {text: 'Default', value: 'default'}, // лейбл, значение
                                {text: 'Style 1', value: 'style1'}, // лейбл, значение
                                {text: 'Style 2', value: 'style2'}
                            ]
                        }
                    ],
                    onsubmit: function( e ) { // это будет происходить после заполнения полей и нажатии кнопки отправки
                        //editor.insertContent( '<h3 class="toc-title">' + e.data.title + '</h3>');
                        editor.insertContent( '<p>[table_of_contents style="' + e.data.style + '" headings="' + e.data.headings + '" content="' + e.data.content + '" title="' + e.data.title + '"]</p>');
                        editor.insertContent( '<hr>');
                    }
                });
            }
        });
    });
})();