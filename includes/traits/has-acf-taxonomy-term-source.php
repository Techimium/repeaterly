<?php

namespace Repeaterly\Includes\Traits;

use Elementor\Controls_Manager;
use Repeaterly\Includes\Acf;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

trait Has_ACF_Taxonomy_Term_Source
{
    use Has_ACF_Options_Page_Source;

    protected function register_acf_taxonomy_controls($with_term_field = false)
    {
        $this->add_control(
            'key',
            [
                'label' => esc_html__('Field Name (Meta Key)', 'repeaterly'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__('Enter ACF Taxonomy field name (e.g. country)', 'repeaterly'),
                'description' => esc_html__('Reads the first term selected in this ACF Taxonomy field.', 'repeaterly'),
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        if ($with_term_field) {
            $this->add_control(
                'term_field',
                [
                    'label' => esc_html__('Term Field Name', 'repeaterly'),
                    'type' => Controls_Manager::TEXT,
                    'placeholder' => esc_html__('Enter the ACF field name saved on the term (e.g. flag)', 'repeaterly'),
                    'ai' => [
                        'active' => false,
                    ],
                ]
            );
        }

        $this->register_acf_options_page_controls();
    }

    protected function get_selected_terms()
    {
        $key = $this->get_settings('key');

        if (empty($key)) {
            return [];
        }

        return Acf::get_taxonomy_terms($key, $this->resolve_acf_post_id($key));
    }

    protected function get_selected_term()
    {
        $terms = $this->get_selected_terms();

        return $terms ? $terms[0] : null;
    }

    /**
     * @return array{0: string, 1: string}|null The term field name and the ACF object ID of the term.
     */
    protected function get_term_field_source()
    {
        $term_field = $this->get_settings('term_field');
        $term = $this->get_selected_term();

        if (empty($term_field) || !$term) {
            return null;
        }

        return [$term_field, Acf::get_term_post_id($term)];
    }
}
