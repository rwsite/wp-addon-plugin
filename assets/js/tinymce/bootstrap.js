
var selection = window.getSelection().toString();
//console.log(selection);

(function() {
    tinymce.PluginManager.add('bootstrap', function( editor, url ) { // id кнопки true_mce_button должен быть везде один и тот же

        editor.addButton( 'bootstrap', { // id кнопки true_mce_button
            icon:  'bootstrap', // мой собственный CSS класс, благодаря которому я задам иконку кнопки
            type:  'menubutton', // unic type
            text:  'Bootstrap 3',
            title: 'Bootstrap shortcode (v3)', // всплывающая подсказка при наведении
            menu: [ // тут начинается первый выпадающий список
                /* {
                     text: 'Элементы форм',
                     menu: [ // тут начинается второй выпадающий список внутри первого
                         {
                             text: 'Разделитель',
                             onclick: function() {
                                 editor.windowManager.open( {
                                     title: 'Задайте параметры поля',
                                     body: [
                                         {
                                             type: 'textbox', // тип textbox = текстовое поле
                                             name: 'textboxName', // ID, будет использоваться ниже
                                             label: 'ID и name текстового поля', // лейбл
                                             value: 'comment' // значение по умолчанию
                                         },
                                         {
                                             type: 'textbox', // тип textbox = текстовое поле
                                             name: 'multilineName',
                                             label: 'Значение текстового поля по умолчанию',
                                             value: 'Привет',
                                             multiline: true, // большое текстовое поле - textarea
                                             minWidth: 300, // минимальная ширина в пикселях
                                             minHeight: 100 // минимальная высота в пикселях
                                         },
                                         {
                                             type: 'listbox', // тип listbox = выпадающий список select
                                             name: 'listboxName',
                                             label: 'Заполнение',
                                             'values': [ // значения выпадающего списка
                                                 {text: 'Обязательное', value: '1'}, // лейбл, значение
                                                 {text: 'Необязательное', value: '2'}
                                             ]
                                         }
                                     ],
                                     onsubmit: function( e ) { // это будет происходить после заполнения полей и нажатии кнопки отправки
                                         editor.insertContent( '[textarea id="' + e.data.textboxName + '" value="' + e.data.multilineName + '" required="' + e.data.listboxName + '"]');
                                     }
                                 });
                             }
                         },
                     ]
                 },
                 { // второй элемент первого выпадающего списка, просто вставляет [misha]
                     text: 'Шорткод [misha]',
                     onclick: function() {
                         editor.insertContent('[misha]');
                     }
                 },*/
                {
                    text: 'Dividers',
                    menu: [
                        { // второй элемент вложенного выпадающего списка, прост вставляет шорткод [button]
                            text: 'Divider Full',
                            onclick: function () {
                                editor.insertContent('<hr />');
                            }
                        },
                        { // второй элемент вложенного выпадающего списка, прост вставляет шорткод [button]
                            text: 'Divider Short',
                            onclick: function () {
                                editor.insertContent('<hr class="short">');
                            }
                        },
                        { // второй элемент вложенного выпадающего списка, прост вставляет шорткод [button]
                            text: 'Divider Dashed',
                            onclick: function () {
                                editor.insertContent('<hr class="dashed">');
                            }
                        }
                    ]
                },
                {
                    text: 'Lists',
                    menu: [
                        {
                            text: 'Check List',
                            /*onclick: function (e) {
                                editor.insertContent('<ul class="list list-style-check"><li>Item 1</li><li>Item 2</li><li>Item 3</li></ul>');
                            },*/
                            onclick: function(e) {
                                tinyMCE.activeEditor.selection.setContent('<ul class="list list-style-check"><li>Item 1</li><li>Item 2</li><li>Item 3</li></ul>');
                            },
                        },
                        {
                            text: 'Star list',
                            onclick: function (e) {
                                editor.insertContent('<ul class="list list-style-star"><li>Item 1</li><li>Item 2</li><li>Item 3</li></ul>');
                            }
                        }
                    ]
                },
                {
                    text: 'Columns',
                    menu: [
                        {
                            text: 'Columns 2',
                            onclick: function(e) {
                                tinyMCE.activeEditor.selection.setContent('<div class="row"><div class="col-sm-6">Column 1</div><div class="col-md-6">Column 2</div></div>');
                            },
                        },
                        {
                            text: 'Columns 3',
                            onclick: function (e) {
                                editor.insertContent('<div class="row"><div class="col-sm-4">Column 1</div><div class="col-sm-4">Column 2</div><div class="col-sm-4">Column 3</div></div>');
                            }
                        },
                        {
                            text: 'Columns 4',
                            onclick: function (e) {
                                editor.insertContent('<div class="row"><div class="col-sm-3">Column 1</div><div class="col-sm-3">Column 2</div><div class="col-sm-3">Column 3</div><div class="col-md-3">Column 4</div></div>');
                            }
                        }
                    ]
                },
                {
                    text: 'FAQ',
                    onclick: function (e) {
                        editor.insertContent('[faq]\n' +
                            '[question title="Вопрос 1"] ответ 1... [/question]\n' +
                            '[question title="Вопрос 2"] ответ 2... [/question]\n' +
                            '[question title="Вопрос 3"] ответ 3... [/question]\n' +
                            '[question title="Вопрос 4"] ответ 4... [/question]\n' +
                            '[question title="Вопрос 5"] ответ 5... [/question]\n' +
                            '[/faq]');
                    }
                }
            ]
        });
    });
})();