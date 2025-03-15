<?php

namespace Repeaterly\Includes\Widgets;

use Repeaterly\Includes\Dynamic_Content;
use Elementor\Controls_Manager;
use Elementor\Plugin;
use Elementor\Widget_Image_Gallery;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class Image_Gallery extends Widget_Image_Gallery
{

	public function get_name()
	{
		return 'repeaterly-image-gallery';
	}

	public function get_title()
	{
		return esc_html__('ACF Gallery', 'repeaterly');
	}

	public function get_icon()
	{
		return 'eicon-gallery-grid';
	}

	public function get_categories()
	{
		return ['repeaterly'];
	}

	public function get_keywords()
	{
		return ['image', 'photo', 'visual', 'gallery', 'dynamic'];
	}

	protected function is_dynamic_content(): bool
	{
		return true;
	}

	public function has_widget_inner_wrapper(): bool
	{
		return ! Plugin::$instance->experiments->is_feature_active('e_optimized_markup');
	}

	protected function register_controls()
	{
		parent::register_controls();

		$this->update_control(
			'wp_gallery',
			[
				'label' => esc_html__('Gallery Field Name', 'repeaterly'),
				'label_block' => true,
				'show_label' => true,
				'description' => Dynamic_Content::GALLERY_DESCRIPTION,
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('gallery', 'repeaterly'),
				'separator' => 'after',
			]
		);
	}

	public function get_settings_for_display($setting_key = null)
	{
		$settings = parent::get_settings_for_display();

		if ($this->get_settings('wp_gallery')) {
			if ($this->get_settings('wp_gallery')) {
				$items = Dynamic_Content::get_value(Dynamic_Content::CUSTOM, $this->get_settings('wp_gallery'));

				if (empty($items)) {
					$items = Dynamic_Content::get_value(Dynamic_Content::SUB, $this->get_settings('wp_gallery'));
				}

				$settings['wp_gallery'] = $items;
			}
		}

		return $settings;
	}
}
