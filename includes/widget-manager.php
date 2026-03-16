<?php

namespace Repeaterly\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Repeaterly;
use Repeaterly\Includes\Widgets\Accordion;
use Repeaterly\Includes\Widgets\Button;
use Repeaterly\Includes\Widgets\Heading;
use Repeaterly\Includes\Widgets\Icon_List;
use Repeaterly\Includes\Widgets\Image;
use Repeaterly\Includes\Widgets\Image_Gallery;
use Repeaterly\Includes\Widgets\Promotion;

class Widget_Manager
{

    private static $instance;

    public function __construct()
    {
        add_action('elementor/elements/categories_registered', [$this, 'register_category']);
        add_action('elementor/widgets/register', [$this, 'register_widgets'],1);
        add_action('elementor/frontend/after_enqueue_scripts', [$this, 'register_all_widget_scripts']);
    }

    public static function init()
    {
        if (!self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function register_widgets($widgets_manager)
    {
        $widgets_manager->register(new Accordion());
        $widgets_manager->register(new Heading());
        $widgets_manager->register(new Button());
        $widgets_manager->register(new Image());
        $widgets_manager->register(new Icon_List());
        $widgets_manager->register(new Image_Gallery());

        $this->pro_widgets_promote($widgets_manager);
    }

    public function register_category($elements_manager)
    {
        $elements_manager->add_category(
            'repeaterly',
            [
                'title' => esc_html__('Repeaterly', 'repeaterly'),
                'icon' => 'fa fa-plug',
            ]
        );
    }

    public function pro_widgets_promote($widgets_manager)
    {
        $promotion_widgets = [
            [
                'widget_name' => 'repeaterly-loop-grid',
                'widget_title' => 'ACF Repeater Loop Grid (Pro)',
                'widget_icon' => 'eicon-loop-builder',
            ],
            [
                'widget_name' => 'repeaterly-image-carousel',
                'widget_title' => 'ACF Image Carousel (Pro)',
                'widget_icon' => 'eicon-slider-push',
            ],
        ];

        foreach ($promotion_widgets as $promotion_widget) {
            $widgets_manager->register(new Promotion([], $promotion_widget));
        }
    }

    public function register_all_widget_scripts()
    {
        $this->register_script('repeaterly-accordion', 'accordion');
    }
    
    protected function register_script($handle, $script_name)
    {
        wp_register_script($handle, Repeaterly::plugin_url() . 'assets/js/widgets/'. $script_name .'.js', ['elementor-frontend'], Repeaterly::plugin_version(), true);
    }
}
