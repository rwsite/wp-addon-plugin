(function() {


    let tag = 'ads';

    tinymce.create('tinymce.plugins.ADs', {


        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init: function (ed, url) {
            ed.addButton(tag, {
                title: 'Ads insert',
                cmd: 'ads',
                image: '../wp-includes/images/crystal/text.png',
            });

            let editor = ed;
            //next btn or command

            ed.addCommand('ads', function() {
                editor.windowManager.open({
                    title: 'Insert banner',
                    body: [
                        {
                            type: 'textbox', // тип textbox = текстовое поле
                            name: 'img', // ID, будет использоваться ниже
                            label: 'Image url', // лейбл
                            value: '' // значение по умолчанию
                        },
                        {
                            type: 'textbox', // тип textbox = текстовое поле
                            name: 'href', // ID, будет использоваться ниже
                            label: 'Link', // лейбл
                            value: '' // значение по умолчанию
                        },
                        {
                            type: 'listbox', // тип listbox = выпадающий список select
                            name: 'align',
                            label: 'Choose alignment',
                            'values': [ // значения выпадающего списка
                                {text: 'Left', value: 'left'}, // лейбл, значение
                                {text: 'Center', value: 'center'}, // лейбл, значение
                                {text: 'Right', value: 'right'}
                            ]
                        }
                    ],
                    onsubmit: function (e) { // это будет происходить после заполнения полей и нажатии кнопки отправки
                        editor.insertContent('<p>[ads img="' + e.data.img + '" href="' + e.data.href + '" align="' + e.data.align + '"]</p>');
                    }
                });
            });


        },


        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
            return null;
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function () {
            return {
                longname    : 'ADs Buttons',
                author      : 'Alex Tihomirov',
                authorurl   : 'https://code.tutsplus.com/tutorials/guide-to-creating-your-own-wordpress-editor-buttons--wp-30182',
                infourl     : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/example',
                version     : "0.1"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add(tag, tinymce.plugins.ADs);
})();