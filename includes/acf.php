<?php

namespace Repeaterly\Includes;

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

    public static function get_field_value($key, $post_id = false)
    {
        if (function_exists('get_field')) {
            $resolved = self::resolve_field($key, $post_id);
            $field = $resolved['field'];
            $post_id = $resolved['post_id'];

            if ($field && isset($field['type'])) {
                $value = $field['value'];

                switch ($field['type']) {
                    case 'radio':
                        $value = $field['choices'][$value] ?? $value;
                        break;

                    case 'select':
                    case 'checkbox':
                        $values = (array) $value;
                        $value = implode(', ', array_map(function ($item) use ($field) {
                            return $field['choices'][$item] ?? $item;
                        }, $values));
                        break;

                    case 'oembed':
                        $value = self::get_queried_object_meta($key, $post_id);
                        break;
                    case 'google_map':
                        $meta = self::get_queried_object_meta($key, $post_id);
                        $value = isset($meta['address']) ? $meta['address'] : '';
                        break;
                    case 'true_false':
                        $value = (string) $value;
                        break;
                    case 'color_picker':
                        $value = (string) $value;
                        break;
                    case 'date_time_picker':
                        $value = static::get_date_time_value($field);
                        break;
                    case 'image':
                        $value = static::get_image_value($field);
                        break;
                    case 'link':
                        $value = static::get_url_value($field);
                        break;
                    case 'relationship':
                        break;
                }
            } else {
                $value = Acf_Context::with_acf_post_id(
                    $post_id,
                    static function () use ($key, $post_id) {
                        return get_field($key, $post_id);
                    }
                );
            }
        } else {
            // Fallback if ACF not installed.
            $post_id = Acf_Context::resolve_field_post_id($key, $post_id);
            $value = get_post_meta($post_id ? $post_id : get_the_ID(), $key, true);
        }

        return $value;
    }

    protected static function get_queried_object_meta($key, $post_id = false)
    {
        $value = '';

        if (is_singular()) {
            $value = get_post_meta($post_id ? $post_id : get_the_ID(), $key, true);
        } elseif (is_tax() || is_category() || is_tag()) {
            $value = get_term_meta(get_queried_object_id(), $key, true);
        }

        return $value;
    }

    protected static function get_image_value($field)
    {
        $value = '';

        if ($field && is_array($field)) {
            $field['return_format'] = isset($field['save_format']) ? $field['save_format'] : $field['return_format'];
            switch ($field['return_format']) {
                case 'object':
                case 'array':
                    $value = $field['value'];
                    break;
                case 'url':
                    $value = [
                        'id' => 0,
                        'url' => $field['value'],
                    ];
                    break;
                case 'id':
                    $src = wp_get_attachment_image_src($field['value'], $field['preview_size']);
                    $value = [
                        'id' => $field['value'],
                        'url' => $src[0],
                    ];
                    break;
            }
        }

        return $value;
    }

    protected static function get_date_time_value($field)
    {
        $date_time = \DateTime::createFromFormat($field['return_format'], $field['value']);

        $display_format = $field['display_format'] ?? 'Y-m-d H:i:s';

        $value = $date_time instanceof \DateTime
            ? $date_time->format($display_format)
            : '';

        return $value;
    }

    protected static function get_url_value($field)
    {
        if (empty($field)) {
            return null;
        }

        $value = $field['value'];

        if (is_array($value) && isset($value[0])) {
            $value = $value[0];
        }

        if ($value) {
            if (! isset($field['return_format'])) {
                $field['return_format'] = isset($field['save_format']) ? $field['save_format'] : '';
            }


            switch ($field['type']) {
                case 'email':
                    if ($value) {
                        $value = 'mailto:' . $value;
                    }
                    break;
                case 'image':
                case 'file':
                    switch ($field['return_format']) {
                        case 'array':
                        case 'object':
                            $value = $value['url'];
                            break;
                        case 'id':
                            if ('image' === $field['type']) {
                                $src = wp_get_attachment_image_src($value, 'full');
                                $value = $src[0];
                            } else {
                                $value = wp_get_attachment_url($value);
                            }
                            break;
                    }
                    break;
                case 'post_object':
                case 'relationship':
                    $value = get_permalink($value);
                    break;
                case 'taxonomy':
                    $value = get_term_link($value, $field['taxonomy']);
                    break;
            } // End switch().
        }

        return $value;
    }
}
