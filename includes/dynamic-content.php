<?php

namespace Repeaterly\Includes;

class Dynamic_Content
{
    const STATIC = 'static';
    const CUSTOM = 'custom';
    const SUB = 'sub';
    const POST_TITLE = 'post_title';
    const POST_CONTENT = 'post_content';
    const POST_EXCERPT = 'post_exerpt';
    const POST_URL = 'post_url';
    const FEATURED_IMAGE = 'featured_image';

    const IMAGE_DESCRIPTION = "Enter the image url you want to appear. For dynamic content, enter the name of your custom field (e.g., my_field_name) or sub-field (e.g., sub_field_name).";
    const TEXT_DESCRIPTION = "Enter the text you want to appear. For dynamic content, enter the name of your custom field (e.g., my_field_name) or sub-field (e.g., sub_field_name).";
    const LINK_DESCRIPTION = "Enter the full URL (e.g., https://www.example.com) or, for dynamic content, the name of your custom field (e.g., my_link_field) or sub-field (e.g., sub_link_field) that holds the URL.";
    const REPEATER_DESCRIPTION = "Enter the name of your repeater field (e.g., my_repeater).";
    const SUBFIELD_DESCRIPTION = "Enter the name of the sub-field within the repeater (e.g., item_title).";
    const GALLERY_DESCRIPTION = "Enter the name of the custom field you created for your image gallery (e.g., gallery)";
    
    public static function get_text_sources()
    {
        $options = [
            self::STATIC => __('Static', 'repeaterly'),
            self::CUSTOM => __('ACF Custom Field', 'repeaterly'),
            self::SUB => __('ACF Custom Sub Field (Pro)', 'repeaterly'),
            self::POST_TITLE => __('Post Title', 'repeaterly'),
            self::POST_CONTENT => __('Post Content', 'repeaterly'),
            self::POST_EXCERPT => __('Post Excerpt', 'repeaterly'),
        ];

        return apply_filters(Hook::DYNAMIC_TEXT_OPTIONS, $options);
    }

    public static function get_link_sources()
    {
        $options = [
            self::STATIC => __('Static', 'repeaterly'),
            self::CUSTOM => __('ACF Custom Field', 'repeaterly'),
            self::SUB => __('ACF Custom Sub Field (Pro)', 'repeaterly'),
            self::POST_URL => __('Post URL', 'repeaterly'),
        ];

        return apply_filters(Hook::DYNAMIC_LINK_OPTIONS, $options);
    }

    public static function get_image_sources()
    {
        $options = [
            self::STATIC => __('Static', 'repeaterly'),
            self::CUSTOM => __('ACF Custom Field', 'repeaterly'),
            self::SUB => __('ACF Custom Sub Field (Pro)', 'repeaterly'),
            self::FEATURED_IMAGE => __('Featured Image', 'repeaterly'),
        ];

        return apply_filters(Hook::DYNAMIC_IMAGE_OPTIONS, $options);
    }

    public static function get_value($source_name, $source_value = null)
    {
        switch ($source_name) {
            case self::CUSTOM:
                return $source_value ? get_field($source_value) : '';
                break;
            case self::POST_TITLE:
                return get_the_title();
                break;
            case self::POST_CONTENT:
                return get_the_content();
                break;
            case self::POST_EXCERPT:
                return get_the_excerpt();
                break;
            case self::POST_EXCERPT:
                return get_the_excerpt();
                break;
            case self::POST_URL:
                return get_the_permalink();
                break;
            case self::FEATURED_IMAGE:
                return wp_get_attachment_url(get_post_thumbnail_id(get_the_ID()));
                break;
        }

        return apply_filters(Hook::DYNAMIC_VALUE, $source_value, $source_name);
    }
}
