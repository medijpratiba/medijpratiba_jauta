<?php

/** 
 * Plugin Name: Medijpratiba.lv jautājumi
 * Version: 1.0.5
 * Plugin URI: https://mediabox.lv/wordpress/
 * Description: Medijpratiba.lv spēles jautājumi
 * Author: Rolands Umbrovskis
 * Author URI: https://umbrovskis.com
 *
 * License: GNU General Public License
 * 
 */

try {
    new mpQuestions();
} catch (\Throwable $e) {
    $mpquestions_debug = 'Caught throwable: mpQuestions ' . $e->getMessage() . "\n";

    if (apply_filters('mpquestions_debug_log', defined('WP_DEBUG_LOG') && WP_DEBUG_LOG)) {
        error_log(print_r(compact('mpquestions_debug'), true));
    }
}

/**
 * medijpratiba.lv mpc questions
 *
 * @author rolandinsh
 */
class mpQuestions
{

    var $vers = '1.0.5';
    var $verspatch;
    var $plugin_slug;
    var $label_singular;
    var $label_plural;
    var $plugin_td; // text domain

    var $mpqdir;
    var $cb_name;

    function __construct()
    {
        $this->plugin_td = 'medijpratibalv';
        $this->plugin_slug = 'mpquestions';

        $this->verspatch = date("yW");

        $this->label_plural = __('Questions', $this->plugin_td);
        $this->label_singular = __('Question', $this->plugin_td);

        $this->mpqdir = plugin_dir_url(__FILE__);

        add_action('init', [&$this, 'mpquestionsPostTypes'], 15);

        add_filter('rwmb_meta_boxes',  [&$this, 'registerMb']);
        add_filter('single_template',  [&$this, 'loadSingleTemplate']);

        $this->cb_name = $this->plugin_slug . '_cbq';

        add_action('wp_head', [$this, 'ajaxUrl']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueStyles'], 15);

        add_filter('wp_resource_hints', [$this, 'resourceHints'], 20, 2);

        add_action('wp_ajax_mpq_action', [$this, 'mpcAjaxAction']);
        add_action('wp_ajax_nopriv_mpq_action', [$this, 'mpcAjaxAction']);
    }

    /**
     * Register post type mpquestions and related taxonomies
     */
    public function mpquestionsPostTypes()
    {
        register_post_type(
            $this->plugin_slug,
            [
                'label'           => $this->label_plural,
                'description'     => '',
                'public'          => true,
                'show_ui'         => true,
                'show_in_menu'    => true,
                'capability_type' => 'post',
                'hierarchical'    => false,
                'rewrite'         => [
                    'slug'       => $this->plugin_slug,
                    'with_front' => false
                ],
                'query_var'       => true,
                'has_archive'     => true,
                'show_in_rest'    => true,
                'supports'        => [
                    'title',
                    'editor',
                    'excerpt',
                    'trackbacks',
                    'custom-fields',
                    'revisions',
                    'thumbnail',
                    'author',
                    'page-attributes',
                ],
                'taxonomies'      => ['post_tag', $this->plugin_slug . '_tag'],
                'labels'          => [
                    'name'               => $this->label_plural,
                    'singular_name'      => $this->label_singular,
                    'menu_name'          => 'MPC ' . $this->label_plural,
                    'add_new'            => __('Add new', $this->plugin_td),
                    'add_new_item'       => __('Add new Question', $this->plugin_td),
                    'edit'               => __('Edit', $this->plugin_td),
                    'edit_item'          => __('Edit Question', $this->plugin_td),
                    'new_item'           => __('New Question', $this->plugin_td),
                    'view'               => __('View', $this->plugin_td),
                    'view_item'          => __('View Question', $this->plugin_td),
                    'search_items'       => __('Search Questions', $this->plugin_td),
                    'not_found'          => __('Questions not found', $this->plugin_td),
                    'not_found_in_trash' => __('Questions not found in trash', $this->plugin_td),
                    'parent'             => __('Parent Questions', $this->plugin_td),
                ]
            ]
        );
        if (!taxonomy_exists($this->plugin_slug . '_tag')) {
            register_taxonomy(
                $this->plugin_slug . '_tag',
                [0 => $this->plugin_slug,],
                [
                    'hierarchical'   => true,
                    'label'          => __('Questions categories', $this->plugin_td),
                    'show_ui'        => true,
                    'query_var'      => true,
                    'show_in_rest'   => true,
                    'rewrite'        => ['slug' => 'mpquestions-tag', 'with_front' => false],
                    'singular_label' => __('Questions category', $this->plugin_td)
                ]
            );
        }

        register_taxonomy_for_object_type('post_tag', $this->plugin_slug);
    }

    /**
     * Register all meta fields at once
     */
    function registerMb($meta_boxes)
    {
        $prefix = 'mpc_';

        $meta_boxes[] = [
            'title'      => esc_html__('Questions', $this->plugin_td),
            'id'         => 'mpc_questions',
            'post_types' => [$this->plugin_slug],
            'context'    => 'normal',
            'priority'   => 'high',
            'fields'     => [
                [
                    'type' => 'range',
                    'id'   => $prefix . 'nrpk',
                    'name' => esc_html__('Nr', $this->plugin_td),
                    'desc' => esc_html__('1 ... 23', $this->plugin_td),
                    'std'  => 1,
                    'min'  => 1,
                    'max'  => 23,
                    'step' => 1,
                ],
                [
                    'type' => 'number',
                    'id'   => $prefix . 'solis',
                    'name' => esc_html__('Solis', 'mpc-generator'),
                    'desc' => esc_html__('0 ... 3', 'mpc-generator'),
                    'min'  => 0,
                    'max'  => 3,
                    'step' => 1,
                ],
                [
                    'type' => 'text',
                    'id'   => $prefix . 'atbildes_y',
                    'name' => esc_html__('Atbilde', $this->plugin_td),
                    'desc' => "pareizā atbilde",
                ],
                [
                    'type'  => 'text',
                    'id'    => $prefix . 'atbildes_n',
                    'name'  => "NEPAREIZ\xc4\x80S atbildes",
                    'desc'  => "Atbil\xc5\xbeu varianti",
                    'clone' => true,
                ],
                [
                    'type' => 'wysiwyg',
                    'id'   => $prefix . 'paskaidrojums',
                    'name' => esc_html__('Paskaidrojums', $this->plugin_td),
                ],
            ],
        ];

        return $meta_boxes;
    }

    /**
     * Template example 
     */
    public function loadSingleTemplate($template)
    {
        global $post;

        $tpl_file_single = 'single-' . $this->plugin_slug . '.php';

        if ($this->plugin_slug === $post->post_type && locate_template([$tpl_file_single]) !== $template) {
            return plugin_dir_path(__FILE__) . $tpl_file_single;
        }

        return $template;
    }

    /**
     * dirty way to include missing "ajaxurl" in some cases
     * @todo let's hope it'll not break sites
     */
    public function ajaxUrl()
    {
        echo '<script type="text/javascript">var ajaxurl = "' . admin_url('admin-ajax.php') . '";</script>';
    }

    /**
     * Register javascript file(s)
     */
    public function enqueueScripts()
    {

        $mpq_js = $this->mpqdir . 'assets/js/mpq.js';

        if (!is_admin()) {

            wp_enqueue_script('jquery');
            wp_register_script('mpq', $mpq_js, ['jquery', 'bootstrap'], $this->vers . '.' . $this->verspatch, true);
            wp_enqueue_script('mpq');
        }
    }

    /**
     * Registed CSS style(s)
     */
    public function enqueueStyles()
    {

        $mpq_css = $this->mpqdir . 'assets/css/grid5x5.css';

        if (!is_admin()) {
            wp_register_style('open-iconic-bootstrap', $this->mpqdir . 'assets/css/open-iconic-bootstrap.css', [], '1.1.1', 'all');
            wp_enqueue_style('open-iconic-bootstrap');
            wp_register_style('grid5x5', $mpq_css, ['open-iconic-bootstrap'], $this->vers . '.' . $this->verspatch . date("dHi"), 'all');
            wp_enqueue_style('grid5x5');
        }
    }

    /**
     * resource hints for fater loading
     */
    public function resourceHints($hints, $relation_type)
    {
        $rlvhv = $this->vers;

        $mpq_css = $this->mpqdir . 'assets/css/grid5x5.css';
        $openiconic_bootstrap_css = $this->mpqdir . 'assets/css/open-iconic-bootstrap.css';
        switch ($relation_type) {
            case 'prerender':
                $hints[] = $mpq_css;
                $hints[] = $openiconic_bootstrap_css;
                break;
            case 'prefetch':
                $hints[] = $mpq_css;
                $hints[] = $openiconic_bootstrap_css;
                break;
        }

        return $hints;
    }

    /**
     * AJAX calls to WordPress backend
     */
    public function mpcAjaxAction()
    {
        // global $wpdb; // this is how you get access to the database
        $postid = intval($_POST['postid']);
        $mpq_data = get_post($postid);
        $question = get_the_title($postid);
        $postid = $mpq_data->ID;

        $prefix = 'mpc_';
        $atbildes_y = rwmb_meta($prefix . 'atbildes_y', [], $postid);
        $atbildes_n = rwmb_meta($prefix . 'atbildes_n', [], $postid);
        $paskaidrojums = rwmb_meta($prefix . 'paskaidrojums', [], $postid);
        $solis = rwmb_meta($prefix . 'solis', [], $postid);
        $nrpk = rwmb_meta($prefix . 'nrpk', [], $postid);
        $atbildes = array_merge((array)$atbildes_y, $atbildes_n);

        echo '<strong>' . $question . '</strong>';

        echo '<p>';
        // echo 'Laukums: #' . $nrpk . ' | Punkti:<strong>' . $solis . "</strong><br />\n";
        echo "\n";
        echo __("Answers", 'medijpratibalv') . ':';
        echo '</p>';
        echo '<div data-mpqanswers="' . $nrpk . '" class="mpq_answers">';
        echo '<script>solis=' . $solis . ';</script>';
        $atb = 0;
        $answ_icon = '<span class="oi oi-target"></span>';
        foreach ($atbildes as $atbilde) {
            ++$atb;
            $pareiza = (($atbildes_y === $atbilde) ? 1 : 0);
            echo '<p class="mpq_correct-' . $pareiza . ' mpq_answer rounded-lg " data-mpqcorrect="' . $pareiza . '">' . $answ_icon . ' ' . $atbilde . "</p>\n";
        }
        echo "\n</div>";
        echo '<div class="mpq_description d-none bg-white text-dark p-3 mb-2 rounded-lg">' . $paskaidrojums . '</div>';

        wp_die(); // this is required to terminate immediately and return a proper response
    }
}
