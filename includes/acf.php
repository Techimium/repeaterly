<?php

namespace Repeaterly\Includes;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Acf
{
    /**
     * Get the ACF options pages as post ID => title, for a select control.
     *
     * @return array<string, string>
     */
    public static function get_options_pages()
    {
        if (! function_exists('acf_get_options_pages')) {
            return ['option' => __('Default Options Page', 'repeaterly')];
        }

        $pages = acf_get_options_pages();

        if (empty($pages)) {
            return ['option' => __('Default Options Page', 'repeaterly')];
        }

        $options = [];

        foreach ($pages as $page) {
            $post_id = $page['post_id'] ?? 'option';
            $options[$post_id] = $page['page_title'] ?? ($page['menu_title'] ?? $post_id);
        }

        return $options;
    }

    /**
     * Find the field object for a name, and the post ID it was read from.
     *
     * @param string $key          Field name.
     * @param mixed  $post_id      Post, term or options ID; empty for the current post.
     * @param bool   $format_value Apply the field's Return Format.
     * @param bool   $load_value   Load the field's value into the object.
     * @return array{name: string, post_id: mixed, field: array|false}
     */
    public static function resolve_field($key, $post_id = false, $format_value = true, $load_value = true)
    {
        $resolved_post_id = Acf_Context::resolve_field_post_id($key, $post_id);
        $field = false;

        if (function_exists('get_field_object')) {
            if (false === $resolved_post_id && function_exists('get_sub_field_object')) {
                $field = get_sub_field_object($key, $format_value, $load_value);
            } else {
                $field = Acf_Context::with_acf_post_id(
                    $resolved_post_id,
                    static function () use ($key, $resolved_post_id, $format_value, $load_value) {
                        return get_field_object($key, $resolved_post_id, $format_value, $load_value);
                    }
                );
            }
        }

        return [
            'name' => $key,
            'post_id' => $resolved_post_id,
            'field' => $field,
        ];
    }

    /**
     * Get ACF's formatted value, without Repeaterly's per-type shaping.
     *
     * @param string $key      Field name.
     * @param mixed  $post_id  Post, term or options ID; empty for the current post.
     * @return mixed
     */
    public static function get_raw_field_value($key, $post_id = false)
    {
        $resolved_post_id = Acf_Context::resolve_field_post_id($key, $post_id);

        if (function_exists('get_field')) {
            if (false === $resolved_post_id && function_exists('get_sub_field')) {
                return get_sub_field($key);
            }

            return Acf_Context::with_acf_post_id(
                $resolved_post_id,
                static function () use ($key, $resolved_post_id) {
                    return get_field($key, $resolved_post_id);
                }
            );
        }

        return get_post_meta($resolved_post_id ? $resolved_post_id : get_the_ID(), $key, true);
    }

    /**
     * Check whether a repeater or flexible content field has rows left to loop.
     *
     * @param string $key      Field name.
     * @param mixed  $post_id  Post, term or options ID; empty for the current post.
     * @return bool
     */
    public static function have_rows($key, $post_id = false)
    {
        if (!function_exists('have_rows')) {
            return false;
        }

        return Acf_Context::with_acf_post_id(
            $post_id,
            static function () use ($key, $post_id) {
                return have_rows($key, $post_id);
            }
        );
    }

    /**
     * Get the field's value in the shape the ACF text tags print.
     *
     * Pro 2.4.0 and earlier call this directly, so its signature and return shapes must not change.
     *
     * @param string $key      Field name.
     * @param mixed  $post_id  Post, term or options ID; empty for the current post.
     * @return mixed Text for choice, true/false, color, date-time, oEmbed and map fields;
     *               ['id' => ..., 'url' => ...] or ACF's array for images; otherwise ACF's value;
     *               '' when the value cannot be read.
     */
    public static function get_field_value($key, $post_id = false)
    {
        try {
            if (!function_exists('get_field')) {
                $post_id = Acf_Context::resolve_field_post_id($key, $post_id);

                return get_post_meta($post_id ? $post_id : get_the_ID(), $key, true);
            }

            $resolved = self::resolve_field($key, $post_id);
            $field = $resolved['field'];

            if (!is_array($field) || !isset($field['type'])) {
                return self::get_value_without_acf_field($key, $resolved['post_id']);
            }

            return self::get_display_value($field, $key, $post_id, false);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Read a name that has no ACF field behind it, such as plain post meta, straight from ACF.
     *
     * @param string $key              Meta key.
     * @param mixed  $resolved_post_id Post ID from Acf_Context::resolve_field_post_id().
     * @return mixed
     */
    private static function get_value_without_acf_field($key, $resolved_post_id)
    {
        return Acf_Context::with_acf_post_id(
            $resolved_post_id,
            static function () use ($key, $resolved_post_id) {
                return get_field($key, $resolved_post_id);
            }
        );
    }

    /**
     * Get the field as the text the ACF text tags print.
     *
     * @param string $key      Field name.
     * @param mixed  $post_id  Post, term or options ID; empty for the current post.
     * @param bool   $sub_only Only read a sub-field of the current repeater row.
     * @return string '' when the value has no text form.
     */
    public static function get_field_text($key, $post_id = false, $sub_only = false)
    {
        try {
            if (!$sub_only) {
                return self::to_text(self::get_field_value($key, $post_id));
            }

            $field = self::load_sub_field($key, true);

            return $field ? self::to_text(self::get_display_value($field, $key, $post_id, $sub_only)) : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Get the field as the URL the ACF URL tag links to.
     *
     * @param string $key      Field name.
     * @param mixed  $post_id  Post, term or options ID; empty for the current post.
     * @param bool   $sub_only Only read a sub-field of the current repeater row.
     * @return string Unescaped URL, or '' when there is none.
     */
    public static function get_field_url($key, $post_id = false, $sub_only = false)
    {
        try {
            if (!is_string($key) || '' === $key) {
                return '';
            }

            if (!function_exists('get_field')) {
                return self::to_text(self::get_field_value($key, $post_id));
            }

            $field = $sub_only ? self::load_sub_field($key, true) : self::load_field($key, $post_id, true);

            if (!$field) {
                if ($sub_only) {
                    return '';
                }

                $resolved_post_id = Acf_Context::resolve_field_post_id($key, $post_id);

                return self::to_text(self::get_value_without_acf_field($key, $resolved_post_id));
            }

            return self::get_url_value($field, $key, $post_id, $sub_only);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Get a Gallery field's images in stored order, whatever its Return Format.
     *
     * @param string $key      Field name.
     * @param mixed  $post_id  Post, term or options ID; empty for the current post.
     * @param bool   $sub_only Only read a sub-field of the current repeater row.
     * @return array<int, array{id: int, url: string}> Deleted images are left out.
     */
    public static function get_gallery_images($key, $post_id = false, $sub_only = false)
    {
        try {
            if (!is_string($key) || '' === $key || !function_exists('get_field')) {
                return [];
            }

            $field = $sub_only ? self::load_sub_field($key, false) : self::load_field($key, $post_id, false);

            if (!$field || 'gallery' !== $field['type'] || !isset($field['value']) || !is_array($field['value'])) {
                return [];
            }

            $images = [];

            foreach ($field['value'] as $item) {
                $attachment_id = self::extract_id($item);
                $url = $attachment_id ? wp_get_attachment_url($attachment_id) : false;

                if (is_string($url) && '' !== $url) {
                    $images[] = ['id' => $attachment_id, 'url' => $url];
                }
            }

            return $images;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get get_field_value()'s shape from an already loaded field object.
     *
     * @param array  $field    Field object with its formatted value.
     * @param string $key      Field name.
     * @param mixed  $post_id  Post, term or options ID; empty for the current post.
     * @param bool   $sub_only Only read a sub-field of the current repeater row.
     * @return mixed
     */
    private static function get_display_value(array $field, $key, $post_id, $sub_only)
    {
        $value = array_key_exists('value', $field) ? $field['value'] : null;

        switch ($field['type']) {
            case 'radio':
            case 'select':
            case 'checkbox':
                return self::get_choice_labels($field);
            case 'oembed':
                return self::to_text(self::get_unformatted_value($key, $post_id, $sub_only));
            case 'google_map':
                return self::get_map_address(self::get_unformatted_value($key, $post_id, $sub_only));
            case 'true_false':
                return is_scalar($value) ? (string) $value : '';
            case 'color_picker':
                return is_string($value) ? $value : self::to_text(self::get_unformatted_value($key, $post_id, $sub_only));
            case 'date_time_picker':
                return self::format_date_time($field);
            case 'image':
                return self::get_image_value($field);
        }

        return $value;
    }

    /**
     * Load a field object with its value.
     *
     * @param string $key          Field name.
     * @param mixed  $post_id      Post, term or options ID; empty for the current post.
     * @param bool   $format_value Apply the field's Return Format.
     * @return array|false False when there is no such field.
     */
    private static function load_field($key, $post_id, $format_value)
    {
        $field = self::resolve_field($key, $post_id, $format_value, true)['field'];

        return self::is_valid_field($field) ? $field : false;
    }

    /**
     * Load a sub-field object of the current repeater row with its value.
     *
     * @param string $key          Sub-field name.
     * @param bool   $format_value Apply the field's Return Format.
     * @return array|false False when the current row has no such sub-field.
     */
    private static function load_sub_field($key, $format_value)
    {
        $field = function_exists('get_sub_field_object') ? get_sub_field_object($key, $format_value, true) : false;

        return self::is_valid_field($field) ? $field : false;
    }

    /**
     * Check whether ACF returned a usable field object.
     *
     * @param mixed $field
     * @return bool
     */
    private static function is_valid_field($field)
    {
        return is_array($field) && isset($field['type']) && is_string($field['type']);
    }

    /**
     * Get the value as stored, before its Return Format is applied.
     *
     * On a taxonomy archive, a top-level field is read from the queried term.
     *
     * @param string $key      Field name.
     * @param mixed  $post_id  Post, term or options ID; empty for the current post.
     * @param bool   $sub_only Only read a sub-field of the current repeater row.
     * @return mixed
     */
    private static function get_unformatted_value($key, $post_id, $sub_only)
    {
        if (!$sub_only && Acf_Context::is_empty_post_id($post_id) && self::is_term_archive() && false !== Acf_Context::resolve_field_post_id($key, false)) {
            return get_term_meta(get_queried_object_id(), $key, true);
        }

        $field = $sub_only ? self::load_sub_field($key, false) : self::load_field($key, $post_id, false);

        return $field && array_key_exists('value', $field) ? $field['value'] : null;
    }

    /**
     * Check whether the request is a category, tag or taxonomy archive.
     *
     * @return bool
     */
    private static function is_term_archive()
    {
        return function_exists('is_tax') && (is_tax() || is_category() || is_tag());
    }

    /**
     * Get the labels of the selected choices, joined with ", ".
     *
     * Works for every Return Format. A value that is not a choice (a custom value) shows as itself.
     *
     * @param array $field Checkbox, Radio or Select field object.
     * @return string
     */
    private static function get_choice_labels(array $field)
    {
        $choices = self::flatten_choices(isset($field['choices']) && is_array($field['choices']) ? $field['choices'] : []);
        $value = array_key_exists('value', $field) ? $field['value'] : null;

        if (is_object($value)) {
            return '';
        }

        // "Both (Array)" on a single choice is one value/label pair, not two items.
        if (is_array($value) && array_key_exists('value', $value)) {
            $value = [$value];
        }

        $labels = [];

        foreach ((array) $value as $item) {
            if (is_array($item) && array_key_exists('value', $item)) {
                $item = $item['value'];
            }

            if (!is_scalar($item) || is_bool($item) || '' === (string) $item) {
                continue;
            }

            $choice = (string) $item;
            $labels[] = array_key_exists($choice, $choices) ? $choices[$choice] : $choice;
        }

        return implode(', ', $labels);
    }

    /**
     * Flatten choices grouped under headings into value => label.
     *
     * @param array $choices
     * @return array<string|int, string>
     */
    private static function flatten_choices(array $choices)
    {
        $flat = [];

        foreach ($choices as $choice => $label) {
            if (is_array($label)) {
                foreach (self::flatten_choices($label) as $nested_choice => $nested_label) {
                    $flat[$nested_choice] = $nested_label;
                }
                continue;
            }

            $flat[$choice] = is_scalar($label) ? (string) $label : (string) $choice;
        }

        return $flat;
    }

    /**
     * Get a Google Map value's address.
     *
     * @param mixed $map
     * @return string
     */
    private static function get_map_address($map)
    {
        return is_array($map) && isset($map['address']) ? self::to_text($map['address']) : '';
    }

    /**
     * Get an Image field as ['id' => ..., 'url' => ...], or ACF's array for the Array format.
     *
     * @param array $field
     * @return mixed '' when there is no image.
     */
    private static function get_image_value($field)
    {
        if (!is_array($field)) {
            return '';
        }

        $value = array_key_exists('value', $field) ? $field['value'] : null;
        $return_format = isset($field['save_format']) ? $field['save_format'] : (isset($field['return_format']) ? $field['return_format'] : '');

        switch ($return_format) {
            case 'object':
            case 'array':
                return $value;
            case 'url':
                return is_string($value) && '' !== $value ? ['id' => 0, 'url' => $value] : '';
            case 'id':
                if (!self::extract_id($value)) {
                    return '';
                }

                $source = wp_get_attachment_image_src(self::extract_id($value), isset($field['preview_size']) && $field['preview_size'] ? $field['preview_size'] : 'medium');

                return is_array($source) && isset($source[0]) && is_string($source[0]) && '' !== $source[0]
                    ? ['id' => $value, 'url' => $source[0]]
                    : '';
        }

        return '';
    }

    /**
     * Reformat a Date Time Picker value with the field's display format.
     *
     * @param array $field
     * @return string '' when the value cannot be parsed.
     */
    private static function format_date_time($field)
    {
        if (!isset($field['value']) || !is_string($field['value']) || '' === $field['value']) {
            return '';
        }

        $return_format = isset($field['return_format']) && is_string($field['return_format']) ? $field['return_format'] : 'd/m/Y g:i a';
        $date_time = \DateTime::createFromFormat($return_format, $field['value']);

        $display_format = $field['display_format'] ?? 'Y-m-d H:i:s';

        return $date_time instanceof \DateTime ? $date_time->format($display_format) : '';
    }

    /**
     * Get the URL a loaded field points to, per field type and Return Format.
     *
     * @param array  $field    Field object with its formatted value.
     * @param string $key      Field name.
     * @param mixed  $post_id  Post, term or options ID; empty for the current post.
     * @param bool   $sub_only Only read a sub-field of the current repeater row.
     * @return string Unescaped URL, or ''.
     */
    private static function get_url_value(array $field, $key, $post_id, $sub_only)
    {
        $value = array_key_exists('value', $field) ? $field['value'] : null;

        switch ($field['type']) {
            case 'email':
                $email = self::to_text($value);

                return '' === $email ? '' : 'mailto:' . $email;
            case 'link':
                return self::to_text(is_array($value) ? ($value['url'] ?? '') : $value);
            case 'image':
            case 'file':
                if (is_array($value)) {
                    return self::to_text($value['url'] ?? '');
                }

                if (is_numeric($value)) {
                    if ('image' === $field['type']) {
                        $source = wp_get_attachment_image_src((int) $value, 'full');

                        return is_array($source) && isset($source[0]) ? self::to_text($source[0]) : '';
                    }

                    return self::to_text(wp_get_attachment_url((int) $value));
                }

                return self::to_text($value);
            case 'post_object':
            case 'relationship':
                $post = self::first_item($value);
                $post = $post instanceof \WP_Post ? $post : self::extract_id($post);

                return $post ? self::to_text(get_permalink($post)) : '';
            case 'page_link':
                return self::to_text(self::first_item($value));
            case 'taxonomy':
                $term = self::first_item($value);
                $term = $term instanceof \WP_Term ? $term : self::extract_id($term);

                if (!$term) {
                    return '';
                }

                $link = get_term_link($term, isset($field['taxonomy']) && is_string($field['taxonomy']) ? $field['taxonomy'] : '');

                return is_wp_error($link) ? '' : self::to_text($link);
            case 'oembed':
                return self::to_text(self::get_unformatted_value($key, $post_id, $sub_only));
        }

        return self::to_text(self::get_display_value($field, $key, $post_id, $sub_only));
    }

    /**
     * Convert strings and numbers to text; anything else becomes ''.
     *
     * @param mixed $value
     * @return string
     */
    private static function to_text($value)
    {
        return is_string($value) || is_int($value) || is_float($value) ? (string) $value : '';
    }

    /**
     * Get the first item of a list, or the value itself when it is not a list.
     *
     * @param mixed $value
     * @return mixed
     */
    private static function first_item($value)
    {
        return is_array($value) ? ($value ? reset($value) : null) : $value;
    }

    /**
     * Get a positive ID from an ID, numeric string, WP_Post, WP_Term or array.
     *
     * @param mixed $value
     * @return int 0 when there is none.
     */
    private static function extract_id($value)
    {
        if (is_object($value)) {
            $value = isset($value->ID) ? $value->ID : (isset($value->term_id) ? $value->term_id : null);
        } elseif (is_array($value)) {
            $value = isset($value['ID']) ? $value['ID'] : (isset($value['id']) ? $value['id'] : null);
        }

        return is_numeric($value) && (int) $value > 0 ? (int) $value : 0;
    }
}
