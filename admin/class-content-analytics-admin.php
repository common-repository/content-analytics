<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://m.smithworx.com
 * @since      1.0.0
 *
 * @package    Content_Analytics
 * @subpackage Content_Analytics/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Content_Analytics
 * @subpackage Content_Analytics/admin
 * @author     Matt Smith <matt@smithwox.com>
 */
class Content_Analytics_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    const REMOTE_BASE_URL = "https://gmc.lingotek.com/v1";

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Content_Analytics_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Content_Analytics_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/content-analytics-admin.css', array(), $this->version, 'all');
        wp_enqueue_style('font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), '4.7.0', 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Content_Analytics_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Content_Analytics_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/content-analytics-admin.js', array( 'jquery' ), $this->version, false);
        wp_localize_script($this->plugin_name, 'ajax', array( 'url' => admin_url('admin-ajax.php') ));
    }

    public function add_menus()
    {

        /*
        add_menu_page(
            __('Analytics', 'content-analytics'),
            __('Analytics', 'content-analytics'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_dashboard_page'), 'dashicons-lightbulb'
        );

        add_submenu_page(
            $this->plugin_name,//'tools.php',
            __('Dashboard', 'content-analytics'),
            __('Dashboard', 'content-analytics'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_dashboard_page')
        );

        add_submenu_page(
            $this->plugin_name,//'tools.php',
            __('Settings', 'content-analytics'),
            __('Settings', 'content-analytics'),
            'manage_options',
            $this->plugin_name.'_settings',
            array($this, 'display_settings_page')
        );
        */

    }

    public function display_dashboard_page()
    {
        include('partials/dashboard.php');
    }

    public function display_settings_page()
    {
        include('partials/settings.php');
    }

    /*
     * adds a column to display the post or page translation profile
     *
     * @since 1.0
     */
    public function add_columns($columns)
    {
        $n = array_search('date', array_keys($columns));
        if ($n) {
            $end = array_slice($columns, $n);
            $columns = array_slice($columns, 0, $n);
        }

        $columns['profile'] = 'Profile';
        return isset($end) ? array_merge($columns, $end) : $columns;
    }


    public function add_meta_boxes()
    {

        // Meta Boxes
        add_meta_box('lca_post_meta_box', __('Lingotek Content Analytics', 'content-analytics'), array( $this, 'display_meta_box_html'), 'post', 'side', 'default');
        add_meta_box('lca_meta_box', __('Lingotek Content Analytics', 'content-analytics'), array( $this, 'display_meta_box_html'), 'page', 'side', 'default');
    }

    /*
     * adds a column to display the post or page translation profile
     *
     * @since 1.0
     */
    public function display_meta_box_html()
    {
        global $post;// $post -> ID, post_type, post_name, post_content, post_modified
        $post_type = $post->post_type;//get_post_type($post->ID);
        include("partials/meta-box.php");
    }

    public function save_post()
    {
        // TODO: consider saving meta
    }

    public function content_analytics_callback()
    {
        $valid_reports = array('analysis', 'language', 'statistics', 'authorship');// 'debug'
        $input_reports = filter_input(INPUT_POST, 'reports');
        $input_reports = $input_reports !== NULL ? $input_reports : filter_input(INPUT_GET, 'reports');
        $reports_requested = $input_reports !== NULL ? split(',', $input_reports) : $valid_reports;

        $input_post = $_REQUEST['post'];//filter_input(INPUT_POST, 'post');
        $post = $input_post !== NULL ? $input_post : array();
        // ['post_ID','post_author','post_type','auto_draft','post_title']

        $reports = array_intersect($reports_requested, $valid_reports);
        $content = stripslashes($_REQUEST['content']);
        $results = array(
            'reports' => $reports
        );

        foreach ($reports as $report) {
            switch ($report) {

                // CLOUD
                case "analysis":
                    $results[$report] = $this->ca_remote_analyze($content);
                    break;
                case "language":
                    $results[$report] = $this->ca_remote_language($content);
                    break;

                // LOCAL
                case "statistics":
                    $results[$report] = $this->ca_local_content_analyze($content);
                    break;
                case "authorship":
                    $results[$report] = $this->ca_local_post_analyze($post);
                    break;

                case "debug":
                    $results[$report] = array(
                      "post" => $post,
                      "post-json" => json_encode($post, JSON_PRETTY_PRINT),
                      "timestamp" => date("r")
                    );
                    break;

            }
        }
        $results['updated'] = date(DATE_RFC850);

        wp_send_json($results);
        wp_die();
    }

    public function rip_tags($string)
    {
        // ----- remove HTML TAGs -----
        $string = preg_replace('/<[^>]*>/', ' ', $string);

        // ----- remove control characters -----
        $string = str_replace("\r", '', $string);    // --- replace with empty space
        $string = str_replace("\n", ' ', $string);   // --- replace with space
        $string = str_replace("\t", ' ', $string);   // --- replace with space
        //$string = stripslashes($string); // --- removes slashes

        // ----- remove multiple spaces -----
        $string = trim(preg_replace('/ {2,}/', ' ', $string));

        return $string;
    }

    private function ca_local_content_analyze($content)
    {
      $cleaned_content = html_entity_decode($this->rip_tags($content));
      $report = array (
        "words" => $this->ca_words($cleaned_content),
        "segments" => $this->ca_segments($content),
        "media" => $this->ca_media($content),
        "keywords" =>  $this->ca_keywords($cleaned_content),
      );
      return $report;
    }

    private function ca_words($content)
    {
        $words = str_word_count($content, 1);

        $results = array(
            'total' => count($words),
            'unique' => count(array_unique($words)),
            'repetitions' => 0,
        );
        $results['repetitions'] = $results['total'] - $results['unique'];
        $results['summary'] = '<b>'.$results['total'].'</b>';//' ('.$results['unique'].' unique)';
        return $results;
    }

    private function stopwords()
    {
        return array("a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount",  "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");
    }

    private function ca_keywords($content)
    {
        $top_n = 10;
        $words = str_word_count(strtolower($content), 1);
        $stop_words = $this->stopwords();
        $words = array_diff($words, $stop_words);
        $word_counts = array_count_values($words);
        arsort($word_counts);
        $word_counts = array_slice($word_counts, 0, $top_n);
        $results = array();
        $i = 0;
        foreach ($word_counts as $k => $v) {
            $number= '';
            $label = $number.'<b>'.$k.'</b>';
            $results[$label] = $v;
        }
        $results['summary'] = current(array_keys($results)).' ('.current($results).')';
        return $results;
    }

    private function ca_segments($content)
    {
        $content = html_entity_decode($this->rip_tags($content));
        //TODO: improve segmentation by replacing block tags with segment terminator
        //TODO: preg_replace('#<[^>]+>#', ' ', '<h1>Foo</h1>bar');
        $segments = preg_split('/(?<=[.\n?!;:])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);

        $results = array(
            'total' => count($segments),
            'unique' => count(array_unique($segments)),
            'repetitions' => 0,
        );
        $results['repetitions'] = $results['total'] - $results['unique'];
        $results['summary'] = '<b>'.$results['total'].'</b>';
        return $results;
    }

    private function ca_local_post_analyze($post)
    {
      $revisions = wp_get_post_revisions($post['post_ID']);
      $timestamps = array();
      foreach($revisions as $revision){
        $ts = strtotime($revision->post_date);
        $timestamps[] = $ts;
      }
      $min_ts = min($timestamps);
      $max_ts = max($timestamps);
      $days = abs($max_ts - $min_ts)/60/60/24;

      // counts
      $post_status = isset($post['hidden_post_status']) ? $post['hidden_post_status'] : 'publish';
      $count_by_author = count_user_posts( $post['post_author'] );
      $count_total = array_sum((array)wp_count_posts( $post['post_type'] ));

      $results = array(
        'velocity' => array(
          'revisions' => count($timestamps),
          'days' => round($days, self::PRECISION),
          'revs_per_day' => round(count($timestamps) / $days, self::PRECISION),
        ),
        'posts' => array(
            'author' => $count_by_author." (".round(100 * $count_by_author/$count_total)."%)",
            'total' => $count_total,
            'summary' => "<b>$count_by_author</b> (".round(100 * $count_by_author/$count_total)."%)"
        )
      );
      $results['velocity']['summary'] = '<b>'.$results['velocity']['revs_per_day'].'</b>';

      return $results;
    }

    private function ca_media($content)
    {
        //print_r($content);
        $results = array(
            'total' => 0,
            'images' => substr_count($content, '<img'),
            'links' => substr_count($content, 'href='),
            //'videos' => preg_match_all("/\.(avi|AVI|wmv|WMV|flv|FLV|mpg|MPG|mp4|MP4)/", $content),
            'youtube' => preg_match_all(
                '@(https?://)?(?:www\.)?(youtu(?:\.be/([-\w]+)|be\.com/watch\?v=([-\w]+)))\S*@im',
                $content,
                $matches
            )
        );
        $results['total'] = $results['images'] + $results['links'] + $results['youtube'];
        $results['summary'] = '<b>' . ($results['total']) . '</b>';
        return $results;
    }

    const ICONS = array(
        'POSITIVE' => '<i class="lca-good fa fa-lg fa-smile-o"></i>',
        'NEUTRAL' => '<i class="lca-muted fa fa-lg fa-meh-o"></i>',
        'NEGATIVE' => '<i class="lca-bad fa fa-lg fa-frown-o"></i>',
    );

    const PRECISION = 3; // number of digits after the decimal point

    private function ca_remote_analyze($content)
    {
      $response = $this->remote_api_call("/analyze", $content);
      $results = $response;

      // statistics
      unset($results->statistics);

      // readability
      $summary_stat_threshold = 60;
      $readability_summary_stat = $results->readability->flesch_kincaid_reading_ease;
      $readability_summary_class = $readability_summary_stat < $summary_stat_threshold ? "lca-warn" : "lca-good";
      $results->readability->summary = "<b class=\"$readability_summary_class\">$readability_summary_stat</b>";

      // sentiment
      unset($results->sentiment->tokens);
      unset($results->sentiment->words);

      $score_string = ($results->sentiment->class == "POSITIVE") ? "+".$results->sentiment->score : $results->sentiment->score;
      $score_string = ($results->sentiment->class == "NEUTRAL") ? '' : '<b>'.$score_string.'</b> ';
      $results->sentiment->comparative = round($results->sentiment->comparative, self::PRECISION);
      if(isset($results->sentiment->positive) && isset($results->sentiment->negative)){
        $results->sentiment->positive_words = count($results->sentiment->positive);
        $results->sentiment->negative_words = count($results->sentiment->negative);
      }
      $results->sentiment->summary = $score_string . self::ICONS[$results->sentiment->class];

      unset($results->sentiment->positive);
      unset($results->sentiment->negative);

      return $results;
    }

    private function ca_remote_language($content)
    {
      $start_time = time();
      $data = array(); // return value container

      $results = $this->remote_api_call("/detect", $content);
      //var_dump($results);
      $icon_reliable = '<span class="lca-good dashicons dashicons-yes"></span>';
      $icon_unreliable = '<span class="lca-bad dashicons dashicons-no-alt"></span>';
      $results->summary = '<b>' . $results->locale . '</b> ' . ($results->reliable? $icon_reliable : $icon_unreliable);
      $results->reliable = $results->reliable ? $icon_reliable . " Yes" : $icon_unreliable . " No";
      //echo "<pre>"; print_r($results); echo "</pre>";
      $results->details = json_encode($results->details, JSON_PRETTY_PRINT);

      $data['detected'] = $results;

      // Language facts (whenever applicable)
      $response = wp_remote_get("https://gmc.lingotek.com/language");
      if (!is_wp_error($response)) {
        $language_facts = json_decode($response['body']);
        if( isset( $language_facts->{$results->code} ) ){
          $reach = $language_facts->{$results->code};
          $reach->summary = "<b>".round($reach->wow * 100)."% WOW</b>";
          $data['reach'] = $reach;
        }
      } else {
        return array();
      }
      $end_time = time();
      // $data['timing'] = array(
      //   'start' => $start_time,
      //   'end' => $end_time,
      //   'summary' => ($end_time - $start_time).'s'
      // );
      return $data;
    }

    private function remote_api_call($api_endpoint, &$content) {
      $results = array();
      $url = self::REMOTE_BASE_URL . $api_endpoint;
      $args = array(
          'body' => array(
              'content' => $this->rip_tags($content)
          )
      );
      $response = wp_remote_post($url, $args);
      if (is_wp_error($response)) {
          $results['error'] = $response->get_error_message();
          return $results;
      } else {
          //echo "<pre>"; print_r($response); echo "</pre>";
          $results = json_decode($response['body']);
          //var_dump($results);
          return $results;
      }
    }
}
