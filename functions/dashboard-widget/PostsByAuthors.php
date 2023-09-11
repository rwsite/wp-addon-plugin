<?php


class PostsByAuthors
{
    static public $data = [];
    static public $table_source = [];
    static public $widget_title = 'Посты по авторам';
    static public $div_id = 'chart_posts_by_authors';

    static public $wname = 'chartPostsByAuthors';

    public static function init()
    {
        add_action('admin_enqueue_scripts', function ($hook) {
            if ('index.php' != $hook) {
                return;
            }
            wp_enqueue_script('google-chart', 'https://www.gstatic.com/charts/loader.js');
            wp_enqueue_script('wp-api');
        }, 99);

        add_action('wp_dashboard_setup', [__CLASS__, 'add_widget']);
        add_action('rest_api_init', function () {
            register_rest_route('ba/v1', '/metrics/postsByAuthor', array(
                'methods'  => 'GET',
                'callback' => [__CLASS__, 'metrics_posts_data']
            ));
        });
    }


    public static function get_data()
    {

        $transient_name = 'posts_by_months_by_author';
        if (self::$data = get_transient($transient_name)) {
            return self::$data;
        }

        $args = [
            'post_type'   => ['post', 'product'],
            'order'       => 'ASC',
            'numberposts' => -1,
            'date_query'  => array(
                [
                    'after' => '-12 month',
                ],
            ),
        ];

        $posts = get_posts($args);

        foreach ($posts as $post) {
            $timestamp = strtotime($post->post_date);
            self::$table_source[] = [
                'date_year_month' => date('Y.m', $timestamp),
                'author'          => get_the_author_meta('display_name', $post->post_author),
                'count'           => 1,
            ];
        }

        self::data_prepare(self::$table_source);

        set_transient($transient_name, self::$data, HOUR_IN_SECONDS);

        return self::$data;
    }

    public static function data_prepare($source_data)
    {
        $authors = array_column($source_data, 'author');
        $authors = array_unique($authors);
        $first_row = $authors;
        array_unshift($first_row, 'Год и месяц');
        $data['first_row'] = $first_row;

        foreach ($source_data as $row) {

            $date = $row['date_year_month'];
            $author = $row['author'];

            if (!isset($data[$date])) {
                foreach ($authors as $author) {
                    $data[$date][$author] = 0;
                }
            }

            $data[$date][$author]++;
        }

        $data2 = [];
        foreach ($data as $key => $row) {
            if ($key == 'first_row') {
                $data2[] = $row;
                continue;
            }

            $new_row = array_values($row);
            array_unshift($new_row, $key);
            $data2[] = $new_row;

        }

        self::$data = $data2;
        return self::$data;

    }

    public static function metrics_posts_data($request)
    {
        $data = self::get_data();
        return new WP_REST_Response($data);
    }

    public static function add_widget()
    {
        wp_add_dashboard_widget(
            self::$wname,
            $title = self::$widget_title,
            [__CLASS__, 'render_widget']
        );
    }

    public static function render_widget()
    {
        ?>
        <div id="<?= self::$div_id ?>"></div>
        <script>
            document.addEventListener("DOMContentLoaded", function (event) {
                var data_api;

                // 1. Создаём новый объект XMLHttpRequest
                var xhr = new XMLHttpRequest();
                var apiUrl = wpApiSettings.root + 'ba/v1/metrics/postsByAuthor';

                // 2. Конфигурируем его: GET-запрос на URL 'phones.json'
                xhr.open('GET', apiUrl, false);

                // 3. Отсылаем запрос
                xhr.send();

                // 4. Если код ответа сервера не 200, то это ошибка
                if (xhr.status == 200) {
                    data_api = JSON.parse(xhr.responseText);
                }

                google.charts.load('current', {'packages': ['corechart']});
                google.charts.setOnLoadCallback(drawChart);

                function drawChart() {
                    // console.log(data_api);

                    var data = google.visualization.arrayToDataTable(data_api);

                    var options = {
                        title: "Посты по месяцам",
                         vAxis: {title: 'Посты'},
                         hAxis: {title: 'Месяцы'},
                        bar: {groupWidth: "80%"},
                        isStacked:true,
                        legend: {position: "bottom"},
                    };

                    var chart = new google.visualization.ColumnChart(document.getElementById('<?= self::$div_id ?>'));
                    chart.draw(data, options);

                }
            });

        </script>
        <?php
    }
}

PostsByAuthors::init();