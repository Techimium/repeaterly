<?php

namespace Repeaterly\Includes\Widgets;

use Repeaterly\Includes\Dynamic_Content;
use Elementor\Widget_Image;
use Elementor\Group_Control_Image_Size;
use Elementor\Plugin;
use Elementor\Utils;

class Image extends Widget_Image
{
    public function get_name()
    {
        return 'repeaterly-image';
    }

    public function get_title()
    {
        return esc_html__('Dynamic Image', 'repeaterly');
    }

    public function get_icon()
    {
        return 'eicon-image';
    }

    public function get_categories()
    {
        return ['repeaterly'];
    }

    public function get_keywords()
    {
        return ['image', 'photo', 'visual'];
    }

    protected function is_dynamic_content(): bool
    {
        return true;
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'section_image',
            [
                'label' => esc_html__('Heading', 'repeaterly'),
            ]
        );

        $this->add_control(
            'field_type',
            [
                'label' => esc_html__('Image Source', 'repeaterly'),
                'type' => \Elementor\Controls_Manager::SELECT,
				'default' => Dynamic_Content::FEATURED_IMAGE,
				'options' => Dynamic_Content::get_image_sources(),
            ]
        );
        $this->add_control(
            'link_to',
            [
                'label' => esc_html__('Link Source', 'repeaterly'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'file',
                'options' => array_merge(Dynamic_Content::get_link_sources(), ['file'  => esc_html__('Lightbox', 'repeaterly')]),
                'separator' => 'after',
            ]
        );

        $this->end_controls_section();

        parent::register_controls();

        $this->update_control('image', [
            'label' => __('Image', 'repeaterly'),
            'label_block' => true,
			'description' => Dynamic_Content::IMAGE_DESCRIPTION,
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => '',
            'placeholder' => __('Enter the value', 'repeaterly'),
            'condition' => [
                'field_type' => [Dynamic_Content::STATIC, Dynamic_Content::CUSTOM, Dynamic_Content::SUB],
            ],
        ]);

        $this->update_control('link', [
            'label' => esc_html__('Link', 'repeaterly'),
            'show_label' => true,
            'label_block' => true,
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
                'link_to' => [Dynamic_Content::STATIC, Dynamic_Content::CUSTOM, Dynamic_Content::SUB],
            ],
        ]);
    }

    private function has_caption($settings)
    {
        return (! empty($settings['caption_source']) && 'none' !== $settings['caption_source']);
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $image = Dynamic_Content::get_value($settings['field_type'], $settings['image']);
        if(is_array($image)){
            $image = $image['url'];
        }

        $settings['image'] = [
            'id' => null,
            'url' => $image ? $image : Utils::get_placeholder_image_src(),
        ];

        if (empty($settings['image']['url'])) {
            return;
        }

        $has_caption = $this->has_caption($settings);

        $link = Dynamic_Content::get_value($settings['link_to'], isset($settings['link']) ? $settings['link']['url'] : null);
        if(!is_array($link)){
            $link = ['url' => $link];
        }

        if ($link) {
            $this->add_link_attributes('link', $link);

            if (Plugin::$instance->editor->is_edit_mode()) {
                $this->add_render_attribute('link', [
                    'class' => 'elementor-clickable',
                ]);
            }

            if ('file' == $settings['link_to']) {
                if($settings['open_lightbox'] != 'no'){
                    $this->add_render_attribute('link', 'href', $image, true);
                }
                $this->add_lightbox_data_attributes('link', $settings['image']['id'], $settings['open_lightbox']);
            }
        } ?>
        <?php if ($has_caption) : ?>
            <figure class="wp-caption">
            <?php endif; ?>
            <?php if ($link) : ?>
                <a <?php $this->print_render_attribute_string('link'); ?>>
                <?php endif; ?>
                <?php Group_Control_Image_Size::print_attachment_image_html($settings); ?>
                <?php if ($link) : ?>
                </a>
            <?php endif; ?>
            <?php if ($has_caption) : ?>
                <figcaption class="widget-image-caption wp-caption-text"></figcaption>
            <?php endif; ?>
            <?php if ($has_caption) : ?>
            </figure>
<?php endif;
        }

        protected function content_template()
        {
            return false;
        }
    }
