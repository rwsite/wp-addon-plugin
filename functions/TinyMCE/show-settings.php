<?php
/**
 * @author: Aleksey Tikhomirov
 * @year: 2019-03-27
 */


function tiny_advanced()
{
    /**
     * Add custom buttons for Tiny MCE
     *
     * @param $buttons
     *
     * @return array
     */
    function editor_third_row($buttons)
    {
        $buttons[] = 'fontselect';
        $buttons[] = 'fontsizeselect';
        $buttons[] = 'styleselect'; // стили
        $buttons[] = 'backcolor'; // цвет фона
        $buttons[] = 'sup'; // верхний индекс
        $buttons[] = 'sub'; // нижний индекс
        $buttons[] = 'anchor'; // якорь

        return $buttons;
    }

    add_filter('mce_buttons_3', 'editor_third_row');
}