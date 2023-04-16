<?php
/*
Plugin Name: WP Trace slow action
Version: 1.0
Description: A simple plugin to show slow action
Author: Aleksey T
*/


class LogSlowActions
{
    public static $trace;
    public static $prev_action;

    public function add_action()
    {
        self::$prev_action = !empty(self::$prev_action) ? self::$prev_action : microtime(true);
        add_action('mu_plugin_loaded', [$this, 'logger'], 99, 1);
        add_action('shutdown', [$this, 'logger'], 99, 1);
    }

    /**
     * Logger
     * @return void
     */
    public function logger()
    {
        global $wp_filter;

        foreach ($wp_filter as $hook_name => $filter) {
            $timer = self::get_stop() - self::$prev_action;
            if (!empty(self::$prev_action) && $timer >= 0.02 ) {
                self::$trace[$hook_name] = [
                    'timer' => $timer,
                    'stats' => self::get_stats(),
                ];
            }
            self::$prev_action = self::get_stop();
        }

        if(self::$trace) {
            $this->write_log(self::$trace);
        }
    }

    /**
     * Get stop time
     * @return float
     */
    private function get_stop()
    {
        global $timestart, $timeend;
        $timeend = microtime(true);
        $timetotal = ($timeend - $timestart) * 1000;
        return $timetotal;
    }

    /**
     * To float
     *
     * @param $number
     * @return float
     */
    private function to_float($number)
    {
        return floatval(preg_replace('/[^\d.]/', '', number_format($number)));
    }

    private static function get_stats(){
        return sprintf(
            __('SQL: %d за %s sec. %.2f MB ', 'wp-addon'),
            get_num_queries(),
            timer_stop(),
            (memory_get_peak_usage() / 1024 / 1024)
        );
    }

    public function write_log($data){
        error_log(var_export($data, true));
    }
}

function trace_slow_actions()
{
    (new LogSlowActions())->add_action();
}