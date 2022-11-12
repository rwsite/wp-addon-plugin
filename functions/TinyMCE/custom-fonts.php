<?php
/**
 * @year: 2019-03-27
 */


function tiny_enable_opensans()
{
    /**
     * Add fonts to the "Font Family" drop-down.
     */
    add_filter('tiny_mce_before_init', 'fb_mce_before_init');
    function fb_mce_before_init($settings)
    {

        $font_formats = 'Open Sans=Open Sans,sans-serif;';
        #  $font_formats .= 'Roboto=Roboto,sans-serif;';
        $font_formats             .= 'Andale Mono=andale mono,times;' .
                                     'Arial=arial,helvetica,sans-serif;' .
                                     'Arial Black=arial black,avant garde;' .
                                     'Book Antiqua=book antiqua,palatino;' .
                                     'Comic Sans MS=comic sans ms,sans-serif;' .
                                     'Courier New=courier new,courier;' .
                                     'Georgia=georgia,palatino;' .
                                     'Helvetica=helvetica;' .
                                     'Impact=impact,chicago;' .
                                     'Symbol=symbol;' .
                                     'Tahoma=tahoma,arial,helvetica,sans-serif;' .
                                     'Terminal=terminal,monaco;' .
                                     'Times New Roman=times new roman,times;' .
                                     'Trebuchet MS=trebuchet ms,geneva;' .
                                     'Verdana=verdana,geneva;' .
                                     'Webdings=webdings;' .
                                     'Wingdings=wingdings,zapf dingbats';
        $settings['font_formats'] = $font_formats;

        return $settings;
    }

    // Add Google Scripts for use with the editor
    if ( ! function_exists('rw_mce_google_fonts_styles')) {
        function rw_mce_google_fonts_styles()
        {
            $font_url = 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800';
            add_editor_style(str_replace(',', '%2C', $font_url));

            $font_url = 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css';
            add_editor_style(str_replace(',', '%2C', $font_url));
        }
    }
    add_action('init', 'rw_mce_google_fonts_styles');
}