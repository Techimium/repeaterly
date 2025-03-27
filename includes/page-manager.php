<?php

namespace Repeaterly\Includes;

use Repeaterly;

class Page_Manager
{
    private static $instance;

    public function __construct()
    {
        add_action('admin_menu', [$this, 'overview_page'], 9);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles'], 100);
    }

    public static function init()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function overview_page()
    {
        add_menu_page(
            'Repeaterly',
            'Repeaterly',
            'manage_options',
            'repeaterly-overview',
            [$this, 'overview_page_html'],
            'dashicons-editor-expand',
            100
        );

        add_submenu_page(
            'repeaterly-overview',
            'Overview',
            'Overview',
            'manage_options',
            'repeaterly-overview',
            [$this, 'overview_page_html'],
            100
        );
    }

    public function overview_page_html()
    {
        require_once Repeaterly::plugin_dir() . '/views/overview.php';
    }

    public function enqueue_styles($hook)
    {
        $screen = get_current_screen();

        if ($hook === 'toplevel_page_repeaterly-overview' || $screen->id === 'toplevel_page_repeaterly-overview') {
            wp_register_style(
                'repeaterly-overview-css',
                plugins_url('/assets/css/overview.css', Repeaterly::plugin_file()),
                array(),
                '1.0.0',
                'all'
            );
            wp_enqueue_style(
                'repeaterly-overview-css'
            );
        }
    }
}
