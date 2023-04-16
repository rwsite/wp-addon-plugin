<?php


return [
    [
        'type'    => 'content',
        'content' => __('Functional in development', 'wp-addon'),
    ],

    [
        'id'      => 'disable_guttenberg_widget',
        'type'    => 'switcher',
        'title'   => __( 'Disable guttenberg for widgets', 'wp-addon' ),
        'default' => true,
    ],
    [
        'id'      => 'add_clone_widget',
        'type'    => 'switcher',
        'title'   => __( 'Enable Duplicate widgets function', 'wp-addon' ),
        'default' => true,
    ],

    [   // Shortcodes
        'id'       => 'components',
        'type'     => 'tabbed',
        'title'    => __('Components', 'wp-addon'),
        'subtitle' => '',
        'tabs'     => [
            [
                'title'  => __('Sidebars', 'wp-addon'),
                'icon'   => 'fa fa-desktop',
                'fields' => [
                    [
                        'id'      => 'add_sidebar_1',
                        'type'    => 'switcher',
                        'title'   => __('Additional sidebar 1', 'wp-addon'),
                        'default' => true,
                    ],
                    [
                        'id'      => 'add_sidebar_2',
                        'type'    => 'switcher',
                        'title'   => __('Additional sidebar 2', 'wp-addon'),
                        'default' => true,
                    ],
                    [
                        'id'      => 'add_sidebar_3',
                        'type'    => 'switcher',
                        'title'   => __('Additional sidebar 3', 'wp-addon'),
                        'default' => true,
                    ],
                ],
            ],
            [
                'title'  => __('Widgets', 'rw-addon'),
                'icon'   => 'fa fa-connectdevelop',
                'fields' => [
                    [
                        'id'      => 'archive_widget',
                        'type'    => 'switcher',
                        'title'   => __('Yearly archive widget', 'wp-addon'),
                        'default' => true,
                    ],
                ],
            ],
        ],
    ],

    [
        'id'      => 'faq_shortcode',
        'type'    => 'switcher',
        'title'   => __('Enable FAQ shortcode', 'wp-addon'),
        'desc'   => __('WPBakery Page Builder support ', 'wp-addon'),
        'default' => true,
    ],
    [
        'id'      => 'table_of_contents',
        'type'    => 'switcher',
        'title'   => __('Enable Table of Content shortcode', 'wp-addon'),
        'desc'   => __('WPBakery Page Builder support ', 'wp-addon'),
        'default' => true,
    ],
];