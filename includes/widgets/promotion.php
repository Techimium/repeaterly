<?php

namespace Repeaterly\Includes\Widgets;

use Elementor\Widget_Base;
use Repeaterly;

class Promotion extends Widget_Base
{
    private $widget_data;

    public function __construct($data = [], $args = null)
    {
        $this->widget_data = [
            'widget_name' => $args['widget_name'],
            'widget_title' => $args['widget_title'],
            'widget_icon' => $args['widget_icon'],
        ];

        parent::__construct($data, $args);
    }

    public function get_name()
    {
        return $this->widget_data['widget_name'];
    }

    public function get_title()
    {
        /**
         * translators: %s: Widget Title.
         */
        return esc_html($this->widget_data['widget_title']);
    }

    public function get_icon()
    {
        return $this->widget_data['widget_icon'];
    }

    public function get_categories()
    {
        return ['repeaterly'];
    }

    public function get_keywords()
    {
        return ['repeaterly', 'template', 'repeater', 'block', 'page', 'loop', 'dynamic'];
    }

    protected function render()
    {
?>
        <a
            href="<?php echo esc_attr(Repeaterly::pro_link()); ?>"
            style="display: inline-block; padding: 10px; font-weight: bold; border-radius: 5px; border: 2px solid darkred; color: darkred;"
            target="_blank">
            Upgrade to Repeaterly Pro
        </a>
<?php
    }
}
