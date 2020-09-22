<?php

/** 
 * Plugin Name: Medijpratiba.lv jautājumi
 * Version: 1.0.3
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

    var $plugin_slug;
    var $label_singular;
    var $label_plural;
    var $plugin_td; // text domain

    var $cb_name;

    function __construct()
    {
        $this->plugin_td = 'medijpratibalv';
        $this->plugin_slug = 'mpquestions';
        $this->label_plural = __('Questions', $this->plugin_td);
        $this->label_singular = __('Question', $this->plugin_td);
        add_action('init', [&$this, 'mpquestionsPostTypes'], 15);

        add_filter('rwmb_meta_boxes',  [&$this, 'registerMb']);
        add_filter('single_template',  [&$this, 'loadSingleTemplate']);

        $this->cb_name = $this->plugin_slug . '_cbq';
    }

    /**
     * register post type mpquestions and related taxonomies
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
                    'desc' => esc_html__('1 ... 25', $this->plugin_td),
                    'std'  => 1,
                    'min'  => 1,
                    'max'  => 25,
                    'step' => 1,
                ],
                [
                    'type' => 'number',
                    'id'   => $prefix . 'solis',
                    'name' => esc_html__('Solis', 'mpc-generator'),
                    'desc' => esc_html__('-3 ... 3', 'mpc-generator'),
                    'min'  => -3,
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

    public function loadSingleTemplate($template)
    {
        global $post;

        if ($this->plugin_slug === $post->post_type && locate_template(array('single-' . $this->plugin_slug . '.php')) !== $template) {
            return plugin_dir_path(__FILE__) . 'single-' . $this->plugin_slug . '.php';
        }

        return $template;
    }
}
