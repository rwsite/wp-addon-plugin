<?php
/**
 * @author: Aleksey Tikhomirov
 * @year: 2019-04-12
 */

function add_sidebar_1()
{
    function additional_sidebar_1()
    {
        register_sidebar(
            [
                'name'          => __('Additional sidebar 1', 'wp-addon'),
                'id'            => 'additional_sidebar_1',
                'description'   => __('Additional sidebar 1', 'wp-addon'),
                'before_widget' => '<div class="widget-content">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="widget-title">',
                'after_title'   => '</h3>',
            ]
        );
    }
    add_action('widgets_init', 'additional_sidebar_1');
}

function add_sidebar_2()
{
    function additional_sidebar_2()
    {
        register_sidebar(
            [
                'name'          => __('Additional sidebar 2', 'wp-addon'),
                'id'            => 'additional_sidebar_2',
                'description'   => __('Additional sidebar 2', 'wp-addon'),
                'before_widget' => '<div class="widget-content">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="widget-title">',
                'after_title'   => '</h3>',
            ]
        );
    }
    add_action('widgets_init', 'additional_sidebar_2');
}

function add_sidebar_3()
{
    function additional_sidebar_3()
    {
        register_sidebar(
            [
                'name'          => __('Additional sidebar 3', 'wp-addon'),
                'id'            => 'additional_sidebar_3',
                'description'   => __('Additional sidebar 3', 'wp-addon'),
                'before_widget' => '<div class="widget-content">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="widget-title">',
                'after_title'   => '</h3>',
            ]
        );
    }
    add_action('widgets_init', 'additional_sidebar_3');
}