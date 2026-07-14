<?php

namespace Repeaterly\Includes\Traits;

use Elementor\Controls_Manager;
use Repeaterly\Includes\Acf;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

trait Has_ACF_Options_Page_Source
{
    protected function register_acf_options_page_controls(array $condition = [])
    {
        $this->add_control(
            'acf_data_source',
            [
                'label' => esc_html__('Data Source', 'repeaterly'),
                'type' => Controls_Manager::SELECT,
                'default' => 'current',
                'options' => [
                    'current' => esc_html__('Current Post', 'repeaterly'),
                    'options_page' => esc_html__('ACF Options Page', 'repeaterly'),
                ],
                'condition' => $condition,
            ]
        );

        $this->add_control(
            'acf_options_page',
            [
                'label' => esc_html__('Options Page', 'repeaterly'),
                'type' => Controls_Manager::SELECT,
                'default' => 'option',
                'options' => Acf::get_options_pages(),
                'condition' => array_merge($condition, ['acf_data_source' => 'options_page']),
            ]
        );
    }

    protected function resolve_acf_post_id()
    {
        if ('options_page' !== $this->get_settings('acf_data_source')) {
            return false;
        }

        $options_page = $this->get_settings('acf_options_page');

        return $options_page ? $options_page : 'option';
    }
}
