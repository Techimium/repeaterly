<?php

namespace Repeaterly\Tests;

use Brain\Monkey\Functions;
use Repeaterly\Includes\Acf;

/**
 * Acf::get_field_value(): the mixed, type-dependent value the ACF text tags print and Repeaterly
 * Pro 2.4.0 and earlier read. The characterisation cases pin what Free 2.2.1 already returned
 * correctly; the correction cases pin the fixes from the `acf-field-values` spec.
 */
class AcfFieldValueTest extends AcfTestCase
{
    const CHOICES = ['red' => 'Red', 'blue' => 'Blue', 'green' => 'Green'];

    public function test_values_that_already_worked_are_unchanged()
    {
        Functions\when('wp_get_attachment_image_src')->alias(static function ($id, $size) {
            return 5 === $id ? ['https://example.test/img-' . $size . '.jpg', 300, 200, true] : false;
        });

        $image_array = ['ID' => 5, 'id' => 5, 'url' => 'https://example.test/img.jpg'];
        $link_array = ['title' => 'Link', 'url' => 'https://example.test/link', 'target' => ''];
        $post = (object) ['ID' => 3];

        // label => [name, type, formatted value, settings, raw value, expected]
        $cases = [
            'text' => ['f', 'text', 'Hello', [], null, 'Hello'],
            'empty text' => ['f', 'text', '', [], null, ''],
            'number stays a number' => ['f', 'number', 42, [], null, 42],
            'true/false on' => ['f', 'true_false', true, [], null, '1'],
            'true/false off' => ['f', 'true_false', false, [], null, ''],
            'date time in display format' => ['f', 'date_time_picker', '15/03/2026 2:30 pm', ['return_format' => 'd/m/Y g:i a', 'display_format' => 'F j, Y g:i a'], null, 'March 15, 2026 2:30 pm'],
            'empty date time' => ['f', 'date_time_picker', '', ['return_format' => 'd/m/Y g:i a', 'display_format' => 'F j, Y g:i a'], null, ''],
            'image array' => ['f', 'image', $image_array, ['return_format' => 'array'], 5, $image_array],
            'image url' => ['f', 'image', 'https://example.test/img.jpg', ['return_format' => 'url'], 5, ['id' => 0, 'url' => 'https://example.test/img.jpg']],
            'image id at preview size' => ['f', 'image', 5, ['return_format' => 'id', 'preview_size' => 'medium'], 5, ['id' => 5, 'url' => 'https://example.test/img-medium.jpg']],
            'legacy save_format wins' => ['f', 'image', 'https://example.test/img.jpg', ['return_format' => 'array', 'save_format' => 'url'], 5, ['id' => 0, 'url' => 'https://example.test/img.jpg']],
            'link array' => ['f', 'link', $link_array, ['return_format' => 'array'], null, $link_array],
            'link url' => ['f', 'link', 'https://example.test/link', ['return_format' => 'url'], $link_array, 'https://example.test/link'],
            'relationship as ACF returns it' => ['f', 'relationship', [$post], ['return_format' => 'object'], ['3'], [$post]],
            'post object as ACF returns it' => ['f', 'post_object', $post, ['return_format' => 'object'], '3', $post],
            'checkbox value format shows labels' => ['f', 'checkbox', ['red', 'blue'], ['return_format' => 'value', 'choices' => self::CHOICES], null, 'Red, Blue'],
            'checkbox label format' => ['f', 'checkbox', ['Red', 'Blue'], ['return_format' => 'label', 'choices' => self::CHOICES], ['red', 'blue'], 'Red, Blue'],
            'radio value format' => ['f', 'radio', 'green', ['return_format' => 'value', 'choices' => self::CHOICES], null, 'Green'],
            'radio label format' => ['f', 'radio', 'Green', ['return_format' => 'label', 'choices' => self::CHOICES], 'green', 'Green'],
            'select value format' => ['f', 'select', 'blue', ['return_format' => 'value', 'choices' => self::CHOICES], null, 'Blue'],
            'multi select value format' => ['f', 'select', ['red', 'green'], ['return_format' => 'value', 'choices' => self::CHOICES, 'multiple' => 1], null, 'Red, Green'],
            'custom checkbox value shows itself' => ['f', 'checkbox', ['red', 'teal'], ['return_format' => 'value', 'choices' => self::CHOICES], null, 'Red, teal'],
            'nothing selected' => ['f', 'checkbox', [], ['return_format' => 'value', 'choices' => self::CHOICES], null, ''],
            'hex color' => ['f', 'color_picker', '#ff6600', ['return_format' => 'string'], null, '#ff6600'],
            'wysiwyg as formatted' => ['f', 'wysiwyg', "<p>Hi</p>\n", [], 'Hi', "<p>Hi</p>\n"],
        ];

        foreach ($cases as $label => [$name, $type, $formatted, $settings, $raw, $expected]) {
            $this->field($name, $type, $formatted, $settings, $raw);

            $this->assertEquals($expected, Acf::get_field_value($name, self::POST_ID), $label);
        }
    }

    public function test_plain_meta_without_a_field_definition()
    {
        $this->meta('subtitle', 'Stored subtitle');

        $this->assertSame('Stored subtitle', Acf::get_field_value('subtitle', self::POST_ID));
        $this->assertNull(Acf::get_field_value('missing', self::POST_ID), 'an unknown key reads as ACF returns it');
    }

    public function test_options_page_source()
    {
        $this->field('headline', 'text', 'Post headline');
        $this->field('headline', 'text', 'Options headline', [], null, 'option');

        $this->assertSame('Options headline', Acf::get_field_value('headline', 'option'));
        $this->assertSame('Post headline', Acf::get_field_value('headline', self::POST_ID));
    }
    public function test_field_is_read_from_the_selected_source()
    {
        $this->field('headline', 'text', 'Post headline');
        $this->field('headline', 'text', 'Options headline', [], null, 'option');

        $this->assertSame('Post headline', Acf::get_field_text('headline', self::POST_ID), 'explicit post');
        $this->assertSame('Options headline', Acf::get_field_text('headline', 'option'), 'options page');
        $this->assertSame('Post headline', Acf::get_field_text('headline'), 'current post');
        $this->assertSame('', Acf::get_field_text('headline', false, true), 'sub-field only: a top-level field is never read');
        $this->assertSame('', Acf::get_field_text('', self::POST_ID), 'empty field name');
        $this->assertSame('', Acf::get_field_text(null, self::POST_ID), 'non-string field name');

        $this->sub_field('row_title', 'text', 'Row one');

        $this->assertSame('Row one', Acf::get_field_text('row_title'), 'active row sub-field from the current context');
        $this->assertSame('Row one', Acf::get_field_text('row_title', false, true), 'sub-field only');
    }

    public function test_choice_fields_show_labels_for_every_return_format()
    {
        $grouped = ['Warm' => ['red' => 'Red'], 'blue' => 'Blue'];
        $numeric = ['0' => 'Zero', '1' => 'One'];

        // label => [type, formatted value, settings, raw value, expected]
        $cases = [
            'checkbox Both' => ['checkbox', [['value' => 'red', 'label' => 'Red'], ['value' => 'blue', 'label' => 'Blue']], ['return_format' => 'array', 'choices' => self::CHOICES], ['red', 'blue'], 'Red, Blue'],
            'radio Both' => ['radio', ['value' => 'green', 'label' => 'Green'], ['return_format' => 'array', 'choices' => self::CHOICES], 'green', 'Green'],
            'single select Both shows the label once' => ['select', ['value' => 'blue', 'label' => 'Blue'], ['return_format' => 'array', 'choices' => self::CHOICES], 'blue', 'Blue'],
            'multi select Both' => ['select', [['value' => 'red', 'label' => 'Red'], ['value' => 'green', 'label' => 'Green']], ['return_format' => 'array', 'choices' => self::CHOICES, 'multiple' => 1], ['red', 'green'], 'Red, Green'],
            'grouped choice shows its label' => ['checkbox', ['red', 'blue'], ['return_format' => 'value', 'choices' => $grouped], null, 'Red, Blue'],
            'grouped Both with a custom value' => ['checkbox', [['value' => 'red', 'label' => 'Red'], ['value' => 'blue', 'label' => 'Blue'], ['value' => 'teal', 'label' => 'teal']], ['return_format' => 'array', 'choices' => $grouped], ['red', 'blue', 'teal'], 'Red, Blue, teal'],
            '"0" is a real choice key' => ['radio', '0', ['return_format' => 'value', 'choices' => $numeric], null, 'Zero'],
            'integer choice key' => ['select', 1, ['return_format' => 'value', 'choices' => $numeric], null, 'One'],
            'null' => ['radio', null, ['return_format' => 'value', 'choices' => self::CHOICES], null, ''],
            'empty string' => ['select', '', ['return_format' => 'value', 'choices' => self::CHOICES], null, ''],
            'false' => ['checkbox', false, ['return_format' => 'value', 'choices' => self::CHOICES], null, ''],
            'empty Both pair list' => ['checkbox', [], ['return_format' => 'array', 'choices' => self::CHOICES], null, ''],
            'missing choices setting' => ['radio', 'green', ['return_format' => 'value'], null, 'green'],
            'nested array from a filter' => ['select', [['nested' => ['deeply']]], ['return_format' => 'value', 'choices' => self::CHOICES], null, ''],
            'object from a filter' => ['select', (object) ['value' => 'red'], ['return_format' => 'value', 'choices' => self::CHOICES], null, ''],
            'booleans in a list are skipped' => ['checkbox', [true, 'red'], ['return_format' => 'value', 'choices' => self::CHOICES], null, 'Red'],
        ];

        foreach ($cases as $label => [$type, $formatted, $settings, $raw, $expected]) {
            $this->field('choice', $type, $formatted, $settings, $raw);

            $this->assertSame($expected, Acf::get_field_value('choice', self::POST_ID), $label);
        }
    }

    public function test_color_is_the_stored_css_string_in_every_format()
    {
        // label => [formatted value, raw value, expected]
        $cases = [
            'hex string' => ['#ff6600', null, '#ff6600'],
            'RGBA array' => [['red' => 255, 'green' => 102, 'blue' => 0, 'alpha' => 1], '#ff6600', '#ff6600'],
            'RGBA array with alpha' => [['red' => 255, 'green' => 0, 'blue' => 0, 'alpha' => 0.5], 'rgba(255,0,0,0.5)', 'rgba(255,0,0,0.5)'],
            'empty' => ['', null, ''],
            'null' => [null, null, ''],
            'unexpected raw value' => [['red' => 1], ['not' => 'a string'], ''],
        ];

        foreach ($cases as $label => [$formatted, $raw, $expected]) {
            $this->field('color', 'color_picker', $formatted, ['return_format' => is_array($formatted) ? 'array' : 'string'], $raw);

            $this->assertSame($expected, Acf::get_field_value('color', self::POST_ID), $label);
        }

        $this->sub_field('row_color', 'color_picker', ['red' => 0, 'green' => 0, 'blue' => 255, 'alpha' => 1], ['return_format' => 'array'], '#0000ff');
        $this->assertSame('#0000ff', Acf::get_field_value('row_color'), 'RGBA sub-field in a repeater row');
    }

    public function test_oembed_and_map_read_the_selected_source()
    {
        $this->field('video', 'oembed', '<iframe src="https://video.test/post"></iframe>', [], 'https://video.test/post');
        $this->field('video', 'oembed', '<iframe src="https://video.test/options"></iframe>', [], 'https://video.test/options', 'option');
        $this->field('map', 'google_map', ['address' => 'Post Address', 'lat' => 1], [], null);
        $this->field('map', 'google_map', ['address' => 'Options Address', 'lat' => 2], [], null, 'option');
        $this->sub_field('row_video', 'oembed', '<iframe></iframe>', [], 'https://video.test/row');
        $this->sub_field('row_map', 'google_map', ['address' => 'Row Address'], [], null);

        $this->assertSame('https://video.test/post', Acf::get_field_value('video', self::POST_ID), 'oEmbed on the post is its source URL');
        $this->assertSame('https://video.test/options', Acf::get_field_value('video', 'option'), 'oEmbed on an Options Page');
        $this->assertSame('https://video.test/row', Acf::get_field_value('row_video'), 'oEmbed in a repeater row');
        $this->assertSame('Post Address', Acf::get_field_value('map', self::POST_ID), 'map on the post');
        $this->assertSame('Options Address', Acf::get_field_value('map', 'option'), 'map on an Options Page');
        $this->assertSame('Row Address', Acf::get_field_value('row_map'), 'map in a repeater row');

        // label => [raw map value, expected]
        $cases = [
            'no address key' => [['lat' => 1], ''],
            'not an array' => ['Somewhere', ''],
            'empty' => ['', ''],
            'address not text' => [['address' => ['x']], ''],
        ];

        foreach ($cases as $label => [$raw, $expected]) {
            $this->field('map_edge', 'google_map', $raw, [], $raw);

            $this->assertSame($expected, Acf::get_field_value('map_edge', self::POST_ID), $label);
        }

        $this->field('video_edge', 'oembed', '', [], ['not' => 'a url']);
        $this->assertSame('', Acf::get_field_value('video_edge', self::POST_ID), 'oEmbed with a non-string stored value');
    }

    public function test_taxonomy_archive_keeps_reading_term_meta_for_oembed_and_map()
    {
        Functions\when('is_tax')->justReturn(false);
        Functions\when('is_category')->justReturn(true);
        Functions\when('get_queried_object_id')->justReturn(33);
        Functions\when('get_term_meta')->alias(static function ($term_id, $key, $single) {
            $meta = ['video' => 'https://video.test/term', 'map' => ['address' => 'Term Address']];

            return 33 === $term_id ? ($meta[$key] ?? '') : '';
        });

        $this->field('video', 'oembed', '<iframe></iframe>', [], 'https://video.test/post');
        $this->field('map', 'google_map', ['address' => 'Post Address'], [], null);
        $this->field('video', 'oembed', '<iframe></iframe>', [], 'https://video.test/options', 'option');
        $this->sub_field('row_video', 'oembed', '<iframe></iframe>', [], 'https://video.test/row');

        $this->assertSame('https://video.test/term', Acf::get_field_value('video'), 'current context on a category archive reads the term');
        $this->assertSame('Term Address', Acf::get_field_value('map'), 'map on a category archive reads the term');
        $this->assertSame('https://video.test/options', Acf::get_field_value('video', 'option'), 'an explicit Options Page still wins');
        $this->assertSame('https://video.test/row', Acf::get_field_value('row_video'), 'a repeater row still reads the row');
    }

    public function test_image_edge_cases_never_warn()
    {
        Functions\when('wp_get_attachment_image_src')->alias(static function ($id, $size) {
            return 5 === $id ? ['https://example.test/img-' . $size . '.jpg', 300, 200, true] : false;
        });

        // label => [formatted value, settings, expected]
        $cases = [
            'id format, deleted attachment' => [99, ['return_format' => 'id', 'preview_size' => 'medium'], ''],
            'id format, empty' => [false, ['return_format' => 'id', 'preview_size' => 'medium'], ''],
            'id format, no preview_size' => [5, ['return_format' => 'id'], ['id' => 5, 'url' => 'https://example.test/img-medium.jpg']],
            'id format, numeric string' => ['5', ['return_format' => 'id', 'preview_size' => 'thumbnail'], ['id' => '5', 'url' => 'https://example.test/img-thumbnail.jpg']],
            'url format, deleted attachment' => [false, ['return_format' => 'url'], ''],
            'url format, empty string' => ['', ['return_format' => 'url'], ''],
            'url format, array from a filter' => [['url' => 'x'], ['return_format' => 'url'], ''],
            'array format, empty' => [false, ['return_format' => 'array'], false],
            'unknown format' => ['x', ['return_format' => 'weird'], ''],
            'no format setting' => ['x', [], ''],
        ];

        foreach ($cases as $label => [$formatted, $settings, $expected]) {
            $this->field('img', 'image', $formatted, $settings, $formatted);

            $this->assertSame($expected, Acf::get_field_value('img', self::POST_ID), $label);
        }
    }

    public function test_date_time_edge_cases_never_warn()
    {
        // label => [formatted value, settings, expected]
        $cases = [
            'value not matching the return format' => ['not a date', ['return_format' => 'd/m/Y g:i a', 'display_format' => 'F j, Y'], ''],
            'no return format setting' => ['15/03/2026 2:30 pm', ['display_format' => 'Y'], '2026'],
            'no display format setting' => ['15/03/2026 2:30 pm', ['return_format' => 'd/m/Y g:i a'], '2026-03-15 14:30:00'],
            'array from a filter' => [['x'], ['return_format' => 'd/m/Y g:i a'], ''],
            'null' => [null, ['return_format' => 'd/m/Y g:i a'], ''],
        ];

        foreach ($cases as $label => [$formatted, $settings, $expected]) {
            $this->field('when', 'date_time_picker', $formatted, $settings);

            $this->assertSame($expected, Acf::get_field_value('when', self::POST_ID), $label);
        }
    }

    public function test_true_false_edge_cases()
    {
        // label => [formatted value, expected]
        $cases = ['true' => [true, '1'], 'false' => [false, ''], 'int 1' => [1, '1'], 'null' => [null, ''], 'array from a filter' => [['x'], '']];

        foreach ($cases as $label => [$formatted, $expected]) {
            $this->field('flag', 'true_false', $formatted);

            $this->assertSame($expected, Acf::get_field_value('flag', self::POST_ID), $label);
        }
    }

    public function test_a_throwing_acf_call_returns_empty_instead_of_breaking_the_page()
    {
        Functions\when('get_field_object')->alias(static function () {
            throw new \RuntimeException('ACF extension failed');
        });

        $this->assertSame('', Acf::get_field_value('anything', self::POST_ID));
        $this->assertSame('', Acf::get_field_text('anything', self::POST_ID));
        $this->assertSame('', Acf::get_field_url('anything', self::POST_ID));
        $this->assertSame([], Acf::get_gallery_images('anything', self::POST_ID));
    }
}
