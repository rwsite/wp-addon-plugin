<?php

class FrontWP
{
    public $options;
	public $file;
	public $path;
	public $url;
	public $name;
	public $ver;

    private static $instance;

    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function __clone(){}
    public function __wakeup(){}

    private function __construct()
    {
        $this->file           = RW_FILE;
        $this->path           = RW_PLUGIN_DIR;
        $this->url            = RW_PLUGIN_URL;
        $this->name           = RW_LANG;
        $this->ver            = '1.1.3';

        $this->options = get_option(RW_LANG,'');
        $this->add_action();
    }

    public function add_action(){
        add_action( 'wp_head', [$this, 'action_add_meta'], 10, 0 );
        add_action( 'wp_head', [$this, 'add_header_js'], 10, 0 );
        add_action( 'wp_head', [$this, 'add_header_css'], 10, 0 );
        add_action( 'wp_head', [$this, 'rw_header_html'], 10, 0 );
        add_action( 'wp_footer', [$this, 'rw_footer_html'], 10, 0 );
       // add_action( 'the_post', [$this, 'add_post_code'], 10, 1 );
        add_action( 'wp_enqueue_scripts', [$this, 'rw_enqueue_scripts'] );
        add_action( 'wp_footer', [__CLASS__, 'add_wp_footer'], 10, 0 );
    }


    public function action_add_meta()
    {
        if(empty($this->options)){
            return;
        }
        foreach ($this->options as $option => $value) {
            if (is_string($option) && $option === 'meta_tags') {
                if(!is_array($value))
                    continue;
                foreach ($value as $val) {
                    if(!is_array($val))
                        continue;
                    foreach ($val as $item) {
                        if(!is_array($item))
                            continue;
                        echo $item;
                    }
                }
            }
        }
    }

    public function add_header_js(){
        if(!empty($this->options['rw_header_js'])){
           echo '<script>' . $this->options['rw_header_js'] . '</script>';
        }
    }

    public function add_header_css(){
        if(!empty($this->options['rw_header_css'])){
            echo '<style type="text/css">' .
                 $this->options['rw_header_css'] .
                 '</style>';
        }
    }


    public function rw_header_html(){
        if(!empty($this->options['rw_header_html'])){
            echo $this->options['rw_header_html'];
        }
    }

    public function rw_footer_html(){
        if(!empty($this->options['rw_footer_html'])){
            echo $this->options['rw_footer_html'];
        }
    }

    public function rw_enqueue_scripts(){
        if(is_admin()){
            return;
        }

        wp_enqueue_style( $this->name, $this->url . 'assets/css/min/wp-addon.min.css', false, $this->ver );

        do_action( 'rw_enqueue_scripts');
    }


    public static function add_wp_footer(){
        do_action( 'add_front');
    }

}