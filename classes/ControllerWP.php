<?php


namespace classes;

final class ControllerWP
{
    public $options;

    private static $instance = null;

    /**
     * @return ControllerWP
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    public function __clone()
    {
    }

    public function __wakeup()
    {
    }

    private function __construct()
    {
        $this->options = self::get_settings();
    }

    /**
     * Get plugin settings from DB
     *
     * @return array
     */
    public static function get_settings()
    {
        return get_option(RW_LANG, '') ?: [];
    }

    /**
     * Fire! Run tweak options
     */
    public function options_loader()
    {
        if (!is_array($this->options)) {
            return;
        }

        foreach ($this->options as $key => $value) {
            if ($value == '1') {
                $this->add_action($key, $value);
            } elseif (is_array($value)) { // block options

                foreach ($value as $name => $val) {
                    if ($val == '1') { // включаем все что не выключено
                        $this->add_action($name, $val);
                    }
                }
            }
        }
    }

    /**
     * Запускаем если есть такая функция
     *
     * @param $function
     * @param $settings
     */
    protected function add_action($function, $settings)
    {
        $function = str_replace('-', '_', $function);
        if (function_exists($function)) {
            add_action('plugins_loaded', $function, 10);
        }
    }


}