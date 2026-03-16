<?php

/**
 * Plugin Name: Repeaterly
 * Plugin URI: https://repeaterly.com
 * Description: Seamlessly integrate dynamic tags with ACF repeater & relationship fields into Elementor, enhancing your design capabilities.
 * Version: 2.0.4
 * Author: Techimium
 * Author URI: https://techimium.com
 * License: GPLv2 or later
 * Text Domain: repeaterly
 * Domain Path: /languages
 */

use Repeaterly\Includes\Page_Manager;
use Repeaterly\Includes\Tag_Manager;
use Repeaterly\Includes\Widget_Manager;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('Repeaterly')) {
    class Repeaterly
    {
        public static $instance;
        /**
         * Plugin version
         */
        static function plugin_version()
        {
            return '2.0.4';
        }

        /**
         * Plugin name
         */
        static function plugin_name()
        {
            return 'Repeaterly';
        }

        /**
         * Plugin file
         */
        static function plugin_file()
        {
            return __FILE__;
        }

        /**
         * Plugin url
         */
        static function plugin_url()
        {
            return trailingslashit(plugin_dir_url(__FILE__));
        }

        /**
         * Plugin dir
         */
        static function plugin_dir()
        {
            return trailingslashit(plugin_dir_path(__FILE__));
        }

        /**
         * Is pro version active?
         */
        static function is_pro_activated()
        {
            return class_exists('Repeaterly_Pro');
        }

        /**
         * Link to the pro version
         */
        static function pro_link()
        {
            return 'https://repeaterly.com';
        }

        static function run()
        {
            if (!self::$instance) {
                self::$instance = new static();
            }

            return self::$instance;
        }

        /**
         * Init the plugin
         */
        public function init()
        {
            require_once self::plugin_dir() . 'autoloader.php';

            if ($this->is_compatible()) {
                Widget_Manager::init();
                Tag_Manager::init();
            }

            Page_Manager::init();
        }

        function add_premium_link($links)
        {
            if (!self::is_pro_activated()) {
                $links[] = '<a style="color: #FCB214; font-weight: 500;" href="' . self::pro_link() . '" target="_blank">Go Premium</a>';
            }
            return $links;
        }

        function plugin_deactivate()
        {
            deactivate_plugins(plugin_basename(__FILE__));
        }

        public function is_compatible()
        {
            if (! did_action('elementor/loaded')) {
                add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
                add_action('admin_init', [$this, 'plugin_deactivate']);
                return false;
            }

            if (! function_exists('get_field')) {
                add_action('admin_notices', [$this, 'admin_notice_missing_acf_plugin']);
                add_action('admin_init', [$this, 'plugin_deactivate']);
                return false;
            }

            return true;
        }

        public function admin_notice_missing_main_plugin(): void
        {
            $message = sprintf(
                /* translators: %1$s: Plugin name (Repeaterly), %2$s: Required plugin name (Elementor). */
                __('"%1$s" requires "%2$s" to be installed and activated.', 'repeaterly'),
                '<strong>' . __('Repeaterly', 'repeaterly') . '</strong>',
                '<strong>' . __('Elementor', 'repeaterly') . '</strong>'
            );

            $allowed_html = [
                'strong' => [],
            ];

            printf(
                '<div class="notice notice-error is-dismissible"><p>%1$s</p></div>',
                wp_kses($message, $allowed_html)
            );
        }

        public function admin_notice_missing_acf_plugin(): void
        {
            $message = sprintf(
                /* translators: %1$s: Plugin name (Repeaterly), %2$s: Required plugin name (Advanced Custom Fields). */
                __('"%1$s" requires "%2$s" to be installed and activated.', 'repeaterly'),
                '<strong>' . __('Repeaterly', 'repeaterly') . '</strong>',
                '<strong>' . __('Advanced Custom Fields', 'repeaterly') . '</strong>'
            );

            $allowed_html = [
                'strong' => [],
            ];

            printf(
                '<div class="notice notice-error is-dismissible"><p>%1$s</p></div>',
                wp_kses($message, $allowed_html)
            );
        }

        /**
         * Run the plugin
         */
        public function __construct()
        {
            add_action('plugins_loaded', [$this, 'init'], 100);
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_premium_link']);
        }
    }

    Repeaterly::run();
}