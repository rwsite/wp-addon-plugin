(function () {
    tinymce.PluginManager.add('ot_button', function (editor, url) {
        editor.addButton('ot_button', {
            title: 'Button',
            image: 'https://img.icons8.com/ios/50/000000/button2.png',
            //icon: 'ot',
            onclick: function () {
                editor.insertContent('[ot_button]');
            }
        });
    });
})();
