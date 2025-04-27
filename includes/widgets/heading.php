<?php

namespace Repeaterly\Includes\Widgets;

use Elementor\Plugin;
use Repeaterly\Includes\Dynamic_Content;
use Elementor\Utils;
use Elementor\Widget_Heading;

class Heading extends Widget_Heading
{

    public function get_name()
    {
        return 'repeaterly-heading';
    }

    public function get_title()
    {
        return __('Dynamic Text', 'repeaterly');
    }

    public function get_icon()
    {
        return 'eicon-t-letter';
    }

    public function get_categories()
    {
        return ['repeaterly'];
    }

    protected function register_controls()
    {
		parent::register_controls();

		$this->remove_control(Utils::ANIMATED_HEADLINE . '_promotion');

        $this->update_control('title', [
			'label' => __('Text', 'repeaterly'),
			'label_block' => true,
			'description' => Dynamic_Content::TEXT_DESCRIPTION,
			'type' => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__('Dynamic Text', 'repeaterly'),
			'placeholder' => __('Enter The Value or ACF Field Name or Sub Field Name', 'repeaterly'),
			'condition' => [
				'field_type' => [Dynamic_Content::STATIC, Dynamic_Content::CUSTOM, Dynamic_Content::SUB],
			],
		]);
		$this->update_control('link', [
			'label' => esc_html__('Link', 'repeaterly'),
			'description' => Dynamic_Content::LINK_DESCRIPTION,
			'type' => \Elementor\Controls_Manager::URL,
			'placeholder' => 'Enter the URL or Field Name or Sub Field Name',
			'options' => ['url', 'is_external', 'nofollow'],
			'default' => [
				'url' => '',
				'is_external' => true,
				'nofollow' => true,
			],
			'label_block' => true,
			'condition' => [
				'link_type' => [Dynamic_Content::STATIC, Dynamic_Content::CUSTOM, Dynamic_Content::SUB],
			],
		]);
		
		$this->start_injection( [
			'type' => 'section',
			'at' => 'start',
			'of' => 'section_title',
		] );

        $this->add_control(
			'field_type',
			[
				'label' => esc_html__('Text Source', 'repeaterly'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => Dynamic_Content::STATIC,
				'options' => Dynamic_Content::get_text_sources(),
			]
		);
		$this->add_control(
			'link_type',
			[
				'label' => esc_html__('Link Source', 'repeaterly'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => Dynamic_Content::STATIC,
				'options' => Dynamic_Content::get_link_sources(),
				'separator' => 'after',
			]
		);

        $this->end_injection();
    }

    protected function is_dynamic_content(): bool
    {
        return true;
    }

    public function get_settings_for_display($setting_key = null)
	{
		$settings = parent::get_settings_for_display();

		if ($this->get_settings('field_type')) {
			$settings['title'] = Dynamic_Content::get_value($this->get_settings('field_type'), $this->get_settings('title'));
		}

		if ( Plugin::$instance->editor->is_edit_mode() && $settings['title'] == '' ) {
			$settings['title'] = esc_html__('Dynamic Text', 'repeaterly');
		}

		if ($this->get_settings('link_type')) {
			$link = Dynamic_Content::get_value($this->get_settings('link_type'), $this->get_settings('link')['url']);

			if (is_array($link)) {
				$settings['link'] = $link;
			} else {
				$settings['link']['url'] = $link;
			}
		}

		if ($setting_key) {
			return $settings[$setting_key];
		}

		return $settings;
	}

    protected function content_template()
    {
        return false;
    }
}
