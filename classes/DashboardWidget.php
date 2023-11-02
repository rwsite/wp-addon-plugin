<?php
/**
 * @year: 2019-06-26
 */


class DashboardWidget
{
    public $key;
    public $title;
    public $desc;
    public $capability;

    protected $atts;

    public function __construct(string $key, string $title = '', string $desc = '', $capability = '', $atts = [])
    {
        $this->key = sanitize_key($key);
        $this->title = $title ?: esc_html__('Dashboard Widget', 'rw-addon');
        $this->desc = $desc ?: esc_html__('Dashboard Widget description', 'rw-addon');
        $this->capability = $capability ?: 'manage_options';

        $this->atts = $atts ?: [];

        add_shortcode($this->key, [$this, 'add_shortcode']);
        add_action('wp_dashboard_setup', [$this, 'DashboardWidgetInit']);
    }

    /**
     * Shortcode in admin panel
     *
     * @param $atts
     *
     * @return bool|string
     */
    public function add_shortcode($atts)
    {
        if (!is_admin() || !current_user_can($this->capability)) {
            return false;
        }

        $atts = shortcode_atts($this->atts, $atts, $this->key);

        if (!isset($atts['html'])) {
            $html = '<ol>';
            foreach ($atts as $key => $val) {
                $html .= '<li>' . $key . ' - ' . $val . '</li>';
            }
            $html .= '<ol>';
        } else {
            $html = $atts['html'];
        }

        return $html;
    }


    ## Произвольный виджет в консоли в админ-панели
    public function DashboardWidgetInit()
    {
        if (!current_user_can($this->capability)) {
            return false;
        }

        wp_add_dashboard_widget($this->key, $this->title, [$this, 'inc_html']);
    }

    /**
     * Html output
     */
    public function inc_html()
    {
        echo "<h3>$this->desc</h3>";
        echo do_shortcode("[$this->key]");
    }
}
