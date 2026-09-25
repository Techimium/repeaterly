<?php

namespace Repeaterly\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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

    /**
     * The source's value as it has always been returned (ACF's formatted value for the ACF
     * sources). Repeaterly Pro 2.4.0 and earlier call this directly, so its signature and return
     * shapes must not change; widgets use get_text()/get_url()/get_link()/get_gallery().
     *
     * @return mixed
     */
    public static function get_value($source_name, $source_value = null, $post_id = false)
    {
        switch ($source_name) {
            case self::SUB:
                return $source_value ? get_sub_field($source_value) : '';
                break;
            case self::CUSTOM:
                return $source_value ? Acf::get_raw_field_value($source_value, $post_id) : '';
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
            case self::POST_URL:
                return get_the_permalink();
                break;
            case self::FEATURED_IMAGE:
                return wp_get_attachment_url(get_post_thumbnail_id(get_the_ID()));
                break;
        }

        return apply_filters(Hook::DYNAMIC_VALUE, $source_value, $source_name);
    }
    /**
     * The source as text for the Dynamic Text and Dynamic Button widgets. An ACF value shows as its
     * Return Format presents it — several values joined with ", " — and a value with no text form
     * of its own (a "Both (Array)" choice, a map, an RGBA color) shows as the ACF Field tag prints
     * it. Never "Array", never an error.
     */
    public static function get_text($source_name, $source_value = null, $post_id = false)
    {
        try {
            $value = self::get_value($source_name, $source_value, $post_id);

            if (is_scalar($value)) {
                return (string) $value;
            }

            if (is_array($value) && $value && array_values($value) === $value && count(array_filter($value, 'is_scalar')) === count($value)) {
                return implode(', ', array_map('strval', $value));
            }

            if (null === $value || [] === $value || !self::is_acf_source($source_name) || !$source_value) {
                return '';
            }

            return Acf::get_field_text($source_value, self::SUB === $source_name ? false : $post_id, self::SUB === $source_name);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /** The source as an unescaped URL; for an ACF source, as the ACF URL tag resolves it. */
    public static function get_url($source_name, $source_value = null, $post_id = false)
    {
        try {
            if (self::is_acf_source($source_name)) {
                return $source_value ? Acf::get_field_url($source_value, self::SUB === $source_name ? false : $post_id, self::SUB === $source_name) : '';
            }

            $value = self::get_value($source_name, $source_value, $post_id);

            if (is_array($value) && isset($value['url'])) {
                $value = $value['url'];
            }

            return is_string($value) || is_int($value) || is_float($value) ? (string) $value : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * The source as a widget link. An ACF link, image or file array replaces the widget's link
     * options, as it always has, and comes back as ['url' => ...]; any other ACF value is its URL
     * and keeps the widget's options. Other sources are returned as get_value() returns them.
     *
     * @return array{url: string}|mixed
     */
    public static function get_link($source_name, $source_value = null, $post_id = false)
    {
        if (!self::is_acf_source($source_name)) {
            return self::get_value($source_name, $source_value, $post_id);
        }

        try {
            $value = self::get_value($source_name, $source_value, $post_id);

            if (is_array($value) && isset($value['url']) && is_string($value['url'])) {
                return ['url' => $value['url']];
            }
        } catch (\Throwable $e) {
            return '';
        }

        return self::get_url($source_name, $source_value, $post_id);
    }

    /**
     * The source as gallery images, [['id' => ..., 'url' => ...], ...] in stored order. For an ACF
     * source this is the Gallery field's images whatever its Return Format.
     *
     * @return array
     */
    public static function get_gallery($source_name, $source_value = null, $post_id = false)
    {
        try {
            if (self::is_acf_source($source_name)) {
                return $source_value ? Acf::get_gallery_images($source_value, self::SUB === $source_name ? false : $post_id, self::SUB === $source_name) : [];
            }

            $value = self::get_value($source_name, $source_value, $post_id);

            return is_array($value) ? $value : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private static function is_acf_source($source_name)
    {
        return self::CUSTOM === $source_name || self::SUB === $source_name;
    }
}
