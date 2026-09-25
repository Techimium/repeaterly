<?php

namespace Repeaterly\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Acf
{
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
     * Resolve a field together with the ACF object it belongs to.
     *
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

    public static function get_raw_field_value($key, $post_id = false)
    {
        $resolved = self::resolve_field($key, $post_id, false, false);

        if (function_exists('get_field')) {
            if (false === $resolved['post_id'] && function_exists('get_sub_field')) {
                return get_sub_field($resolved['name']);
            }

            return Acf_Context::with_acf_post_id(
                $resolved['post_id'],
                static function () use ($resolved) {
                    return get_field($resolved['name'], $resolved['post_id']);
                }
            );
        }

        return get_post_meta(
            $resolved['post_id'] ? $resolved['post_id'] : get_the_ID(),
            $resolved['name'],
            true
        );
    }

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
     * The field's value in the type-dependent shape the ACF text tags print. Repeaterly Pro 2.4.0
     * and earlier call this directly, so its signature and return shapes must not change: choice,
     * true/false, color, date-time, oEmbed and map fields as text, image fields as
     * ['id' => ..., 'url' => ...] (or ACF's image array), anything else as ACF formats it.
     *
     * @return mixed '' when the value cannot be read.
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
                $resolved_post_id = $resolved['post_id'];

                return Acf_Context::with_acf_post_id(
                    $resolved_post_id,
                    static function () use ($key, $resolved_post_id) {
                        return get_field($key, $resolved_post_id);
                    }
                );
            }

            return self::value_from_field($field, $key, $post_id, false);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * The field as text, as the ACF text tags print it: '' when its value has no text form.
     *
     * @param bool $sub_only Read only a sub-field of the active repeater/flexible row.
     */
    public static function get_field_text($key, $post_id = false, $sub_only = false)
    {
        try {
            if (!$sub_only) {
                return self::scalar_string(self::get_field_value($key, $post_id));
            }

            $field = self::load_field($key, false, true, true);

            return $field ? self::scalar_string(self::value_from_field($field, $key, false, true)) : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * The field as an unescaped URL, as the ACF URL tag outputs it: '' when it points nowhere.
     *
     * @param bool $sub_only Read only a sub-field of the active repeater/flexible row.
     */
    public static function get_field_url($key, $post_id = false, $sub_only = false)
    {
        try {
            if (!is_string($key) || '' === $key) {
                return '';
            }

            if (!function_exists('get_field')) {
                return self::scalar_string(self::get_field_value($key, $post_id));
            }

            $field = self::load_field($key, $post_id, $sub_only, true);

            if (!$field) {
                return $sub_only ? '' : self::scalar_string(self::get_field_value($key, $post_id));
            }

            return self::get_url_value($field, $key, $post_id, $sub_only);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * A Gallery field's images in stored order, whatever its Return Format; deleted images are
     * skipped.
     *
     * @param bool $sub_only Read only a sub-field of the active repeater/flexible row.
     * @return array<int, array{id: int, url: string}>
     */
    public static function get_gallery_images($key, $post_id = false, $sub_only = false)
    {
        try {
            if (!is_string($key) || '' === $key || !function_exists('get_field')) {
                return [];
            }

            $field = self::load_field($key, $post_id, $sub_only, false);

            if (!$field || 'gallery' !== $field['type'] || !isset($field['value']) || !is_array($field['value'])) {
                return [];
            }

            $images = [];

            foreach ($field['value'] as $item) {
                $attachment_id = self::id($item);
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
     * get_field_value()'s shape for an already loaded (formatted) field object.
     *
     * @return mixed
     */
    private static function value_from_field(array $field, $key, $post_id, $sub_only)
    {
        $value = array_key_exists('value', $field) ? $field['value'] : null;

        switch ($field['type']) {
            case 'radio':
            case 'select':
            case 'checkbox':
                return self::get_choice_text($field);
            case 'oembed':
                return self::scalar_string(self::get_stored_value($key, $post_id, $sub_only));
            case 'google_map':
                return self::get_map_address(self::get_stored_value($key, $post_id, $sub_only));
            case 'true_false':
                return is_scalar($value) ? (string) $value : '';
            case 'color_picker':
                return is_string($value) ? $value : self::scalar_string(self::get_stored_value($key, $post_id, $sub_only));
            case 'date_time_picker':
                return self::get_date_time_value($field);
            case 'image':
                return self::get_image_value($field);
        }

        return $value;
    }

    /**
     * The field object with or without ACF's Return Format applied; false when there is none.
     *
     * @return array|false
     */
    private static function load_field($key, $post_id, $sub_only, $format_value)
    {
        if ($sub_only) {
            $field = function_exists('get_sub_field_object') ? get_sub_field_object($key, $format_value, true) : false;
        } else {
            $field = self::resolve_field($key, $post_id, $format_value, true)['field'];
        }

        return is_array($field) && isset($field['type']) && is_string($field['type']) ? $field : false;
    }

    /**
     * The value as stored, before any Return Format — for types whose display form is the stored
     * form (oEmbed URL, map, color string).
     *
     * On a taxonomy archive, a field read from the current page (not a repeater row) comes from
     * the queried term, as it always has.
     *
     * @return mixed
     */
    private static function get_stored_value($key, $post_id, $sub_only)
    {
        $implicit = false === $post_id || null === $post_id || '' === $post_id || 0 === $post_id || '0' === $post_id;

        if (!$sub_only && $implicit && self::is_term_archive() && false !== Acf_Context::resolve_field_post_id($key, false)) {
            return get_term_meta(get_queried_object_id(), $key, true);
        }

        $field = self::load_field($key, $post_id, $sub_only, false);

        return $field && array_key_exists('value', $field) ? $field['value'] : null;
    }

    private static function is_term_archive()
    {
        return function_exists('is_tax') && (is_tax() || is_category() || is_tag());
    }

    /**
     * Checkbox, Radio and Select fields as the labels of the selected choices, joined with ", ",
     * whichever Return Format (Value, Label or Both) the field uses. A value that is not a choice
     * key (a custom value, or a label) shows as itself.
     */
    private static function get_choice_text(array $field)
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
     * Choices grouped under headings flattened into key => label; headings are not choices.
     *
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

    /** @param mixed $map */
    private static function get_map_address($map)
    {
        return is_array($map) && isset($map['address']) ? self::scalar_string($map['address']) : '';
    }

    /**
     * Image fields as ['id' => ..., 'url' => ...], or ACF's image array for the Array format; '' when
     * there is no image.
     *
     * @return mixed
     */
    protected static function get_image_value($field)
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
                if (!self::id($value)) {
                    return '';
                }

                $source = wp_get_attachment_image_src(self::id($value), isset($field['preview_size']) && $field['preview_size'] ? $field['preview_size'] : 'medium');

                return is_array($source) && isset($source[0]) && is_string($source[0]) && '' !== $source[0]
                    ? ['id' => $value, 'url' => $source[0]]
                    : '';
        }

        return '';
    }

    protected static function get_date_time_value($field)
    {
        if (!isset($field['value']) || !is_string($field['value']) || '' === $field['value']) {
            return '';
        }

        $return_format = isset($field['return_format']) && is_string($field['return_format']) ? $field['return_format'] : 'd/m/Y g:i a';
        $date_time = \DateTime::createFromFormat($return_format, $field['value']);

        $display_format = $field['display_format'] ?? 'Y-m-d H:i:s';

        return $date_time instanceof \DateTime ? $date_time->format($display_format) : '';
    }

    /** The field as an unescaped URL, per field type and Return Format. */
    private static function get_url_value(array $field, $key, $post_id, $sub_only)
    {
        $value = array_key_exists('value', $field) ? $field['value'] : null;

        switch ($field['type']) {
            case 'email':
                $email = self::scalar_string($value);

                return '' === $email ? '' : 'mailto:' . $email;
            case 'link':
                return self::scalar_string(is_array($value) ? ($value['url'] ?? '') : $value);
            case 'image':
            case 'file':
                if (is_array($value)) {
                    return self::scalar_string($value['url'] ?? '');
                }

                if (is_numeric($value)) {
                    if ('image' === $field['type']) {
                        $source = wp_get_attachment_image_src((int) $value, 'full');

                        return is_array($source) && isset($source[0]) ? self::scalar_string($source[0]) : '';
                    }

                    return self::scalar_string(wp_get_attachment_url((int) $value));
                }

                return self::scalar_string($value);
            case 'post_object':
            case 'relationship':
                $post = self::first($value);
                $post = $post instanceof \WP_Post ? $post : self::id($post);

                return $post ? self::scalar_string(get_permalink($post)) : '';
            case 'page_link':
                return self::scalar_string(self::first($value));
            case 'taxonomy':
                $term = self::first($value);
                $term = $term instanceof \WP_Term ? $term : self::id($term);

                if (!$term) {
                    return '';
                }

                $link = get_term_link($term, isset($field['taxonomy']) && is_string($field['taxonomy']) ? $field['taxonomy'] : '');

                return is_wp_error($link) ? '' : self::scalar_string($link);
            case 'oembed':
                return self::scalar_string(self::get_stored_value($key, $post_id, $sub_only));
        }

        return self::scalar_string(self::value_from_field($field, $key, $post_id, $sub_only));
    }

    /**
     * Strings and numbers as text; anything else as ''.
     *
     * @param mixed $value
     */
    private static function scalar_string($value)
    {
        return is_string($value) || is_int($value) || is_float($value) ? (string) $value : '';
    }

    /**
     * The first item of a list, or the value itself when it is not a list.
     *
     * @param mixed $value
     * @return mixed
     */
    private static function first($value)
    {
        return is_array($value) ? ($value ? reset($value) : null) : $value;
    }

    /**
     * A positive ID from an ID, a numeric string, a WP_Post/WP_Term or an array carrying one; 0
     * otherwise.
     *
     * @param mixed $value
     */
    private static function id($value)
    {
        if (is_object($value)) {
            $value = isset($value->ID) ? $value->ID : (isset($value->term_id) ? $value->term_id : null);
        } elseif (is_array($value)) {
            $value = isset($value['ID']) ? $value['ID'] : (isset($value['id']) ? $value['id'] : null);
        }

        return is_numeric($value) && (int) $value > 0 ? (int) $value : 0;
    }
}
