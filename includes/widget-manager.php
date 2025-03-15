<?php

namespace Repeaterly\Includes;

use Repeaterly;
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
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
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
        if (! Repeaterly::is_pro_activated()) {
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
    }
}
