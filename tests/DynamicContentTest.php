<?php

namespace Repeaterly\Tests;

use Brain\Monkey\Functions;
use Repeaterly\Includes\Dynamic_Content;

/**
 * Dynamic_Content's typed helpers, which the Dynamic Text, Button, Image and ACF Gallery widgets
 * render through. get_value() itself is a contract for older Pro releases and is pinned too.
 */
class DynamicContentTest extends AcfTestCase
{
    const CHOICES = ['red' => 'Red', 'blue' => 'Blue'];

    protected function set_up()
    {
        parent::set_up();

        require_once __DIR__ . '/stubs/wp-classes.php';
        require_once dirname(__DIR__) . '/includes/hook.php';
        require_once dirname(__DIR__) . '/includes/dynamic-content.php';

        Functions\when('get_the_title')->justReturn('Post title');
        Functions\when('get_the_permalink')->justReturn('https://example.test/current/');
        Functions\when('get_permalink')->alias(static function ($post) {
            return 3 === ($post instanceof \WP_Post ? $post->ID : $post) ? 'https://example.test/post-3/' : false;
        });
        Functions\when('wp_get_attachment_url')->alias(static function ($id) {
            return in_array($id, [5, 6], true) ? 'https://example.test/img-' . $id . '.jpg' : false;
        });
        Functions\when('wp_get_attachment_image_src')->alias(static function ($id, $size) {
            return 5 === $id ? ['https://example.test/img-5.jpg', 1, 1, false] : false;
        });
    }

    public function test_get_value_is_unchanged()
    {
        $this->field('choice', 'checkbox', ['red', 'blue'], ['return_format' => 'value', 'choices' => self::CHOICES]);
        $this->sub_field('row_choice', 'checkbox', ['blue'], ['return_format' => 'value', 'choices' => self::CHOICES]);

        $this->assertSame(['red', 'blue'], Dynamic_Content::get_value(Dynamic_Content::CUSTOM, 'choice', self::POST_ID), 'ACF formatted value, as before');
        $this->assertSame(['blue'], Dynamic_Content::get_value(Dynamic_Content::SUB, 'row_choice'), 'sub-field formatted value, as before');
        $this->assertSame('', Dynamic_Content::get_value(Dynamic_Content::CUSTOM, '', self::POST_ID), 'empty field name');
        $this->assertSame('Post title', Dynamic_Content::get_value(Dynamic_Content::POST_TITLE));
        $this->assertSame('typed', Dynamic_Content::get_value(Dynamic_Content::STATIC, 'typed'), 'static passes through the filter');
    }

    public function test_get_text()
    {
        $map = ['address' => 'Map Address', 'lat' => 1];
        $rgba = ['red' => 255, 'green' => 0, 'blue' => 0, 'alpha' => 1];

        // label => [type, formatted value, settings, raw value, expected text]
        $cases = [
            'text' => ['text', 'Hello', [], null, 'Hello'],
            'number' => ['number', 42, [], null, '42'],
            'true' => ['true_false', true, [], null, '1'],
            'false' => ['true_false', false, [], null, ''],
            'select Value keeps the stored value' => ['select', 'blue', ['return_format' => 'value', 'choices' => self::CHOICES], null, 'blue'],
            'select Label' => ['select', 'Blue', ['return_format' => 'label', 'choices' => self::CHOICES], 'blue', 'Blue'],
            'select Both shows the label' => ['select', ['value' => 'blue', 'label' => 'Blue'], ['return_format' => 'array', 'choices' => self::CHOICES], 'blue', 'Blue'],
            'checkbox Value joins stored values' => ['checkbox', ['red', 'blue'], ['return_format' => 'value', 'choices' => self::CHOICES], null, 'red, blue'],
            'checkbox Label joins labels' => ['checkbox', ['Red', 'Blue'], ['return_format' => 'label', 'choices' => self::CHOICES], ['red', 'blue'], 'Red, Blue'],
            'checkbox Both shows labels' => ['checkbox', [['value' => 'red', 'label' => 'Red'], ['value' => 'blue', 'label' => 'Blue']], ['return_format' => 'array', 'choices' => self::CHOICES], ['red', 'blue'], 'Red, Blue'],
            'map shows its address' => ['google_map', $map, [], null, 'Map Address'],
            'RGBA shows the color string' => ['color_picker', $rgba, ['return_format' => 'array'], '#ff0000', '#ff0000'],
            'image array has no text' => ['image', ['ID' => 5, 'url' => 'x'], ['return_format' => 'array'], 5, ''],
            'post object has no text' => ['post_object', new \WP_Post(3), ['return_format' => 'object'], '3', ''],
            'empty checkbox' => ['checkbox', [], ['return_format' => 'value', 'choices' => self::CHOICES], null, ''],
            'null' => ['text', null, [], null, ''],
        ];

        foreach ($cases as $label => [$type, $formatted, $settings, $raw, $expected]) {
            $this->field('f', $type, $formatted, $settings, $raw);

            $this->assertSame($expected, Dynamic_Content::get_text(Dynamic_Content::CUSTOM, 'f', self::POST_ID), $label);
        }

        $this->sub_field('row_choice', 'checkbox', [['value' => 'blue', 'label' => 'Blue']], ['return_format' => 'array', 'choices' => self::CHOICES], ['blue']);
        $this->field('row_choice', 'text', 'Top-level value');

        $this->assertSame('Blue', Dynamic_Content::get_text(Dynamic_Content::SUB, 'row_choice'), 'sub-field Both shows the label');
        $this->assertSame('Post title', Dynamic_Content::get_text(Dynamic_Content::POST_TITLE), 'non-ACF source unchanged');
        $this->assertSame('', Dynamic_Content::get_text(Dynamic_Content::CUSTOM, '', self::POST_ID), 'empty field name');
        $this->assertSame('', Dynamic_Content::get_text(Dynamic_Content::CUSTOM, 'missing', self::POST_ID), 'missing field');
    }

    public function test_sub_source_never_reads_a_top_level_field()
    {
        $this->field('headline', 'url', 'https://example.test/top');

        $this->assertSame('', Dynamic_Content::get_text(Dynamic_Content::SUB, 'headline'), 'text');
        $this->assertSame('', Dynamic_Content::get_url(Dynamic_Content::SUB, 'headline'), 'url');
        $this->assertSame([], Dynamic_Content::get_gallery(Dynamic_Content::SUB, 'headline'), 'gallery');
    }

    public function test_get_url_and_get_link()
    {
        $link_array = ['title' => 'T', 'url' => 'https://example.test/link', 'target' => '_blank'];

        // label => [type, formatted value, settings, raw value, expected URL, expected link]
        $cases = [
            'url' => ['url', 'https://example.test/page', [], null, 'https://example.test/page', 'https://example.test/page'],
            'email' => ['email', 'a@example.test', [], null, 'mailto:a@example.test', 'mailto:a@example.test'],
            'link array replaces the link options' => ['link', $link_array, ['return_format' => 'array'], null, 'https://example.test/link', ['url' => 'https://example.test/link']],
            'link url keeps them' => ['link', 'https://example.test/link', ['return_format' => 'url'], null, 'https://example.test/link', 'https://example.test/link'],
            'image array replaces them' => ['image', ['ID' => 5, 'url' => 'https://example.test/img-5.jpg'], ['return_format' => 'array'], 5, 'https://example.test/img-5.jpg', ['url' => 'https://example.test/img-5.jpg']],
            'image id' => ['image', 5, ['return_format' => 'id'], 5, 'https://example.test/img-5.jpg', 'https://example.test/img-5.jpg'],
            'file id' => ['file', 6, ['return_format' => 'id'], 6, 'https://example.test/img-6.jpg', 'https://example.test/img-6.jpg'],
            'post object' => ['post_object', new \WP_Post(3), ['return_format' => 'object'], '3', 'https://example.test/post-3/', 'https://example.test/post-3/'],
            'deleted image' => ['image', 99, ['return_format' => 'id'], 99, '', ''],
            'array without a string url' => ['link', ['url' => ['x']], ['return_format' => 'array'], null, '', ''],
        ];

        foreach ($cases as $label => [$type, $formatted, $settings, $raw, $url, $link]) {
            $this->field('f', $type, $formatted, $settings, $raw);

            $this->assertSame($url, Dynamic_Content::get_url(Dynamic_Content::CUSTOM, 'f', self::POST_ID), $label . ' (url)');
            $this->assertSame($link, Dynamic_Content::get_link(Dynamic_Content::CUSTOM, 'f', self::POST_ID), $label . ' (link)');
        }

        $this->field('f', 'url', 'https://example.test/options', [], null, 'option');
        $this->assertSame('https://example.test/options', Dynamic_Content::get_link(Dynamic_Content::CUSTOM, 'f', 'option'), 'options page source');

        $this->sub_field('row_link', 'link', $link_array, ['return_format' => 'array']);
        $this->assertSame(['url' => 'https://example.test/link'], Dynamic_Content::get_link(Dynamic_Content::SUB, 'row_link'), 'sub-field link array');
        $this->assertSame('https://example.test/link', Dynamic_Content::get_url(Dynamic_Content::SUB, 'row_link'), 'sub-field URL');

        $this->assertSame('https://example.test/current/', Dynamic_Content::get_url(Dynamic_Content::POST_URL), 'post URL source');
        $this->assertSame('https://example.test/current/', Dynamic_Content::get_link(Dynamic_Content::POST_URL), 'post URL link unchanged');
        $this->assertSame('https://example.test/typed', Dynamic_Content::get_link(Dynamic_Content::STATIC, 'https://example.test/typed'), 'static link unchanged');
        $this->assertSame('', Dynamic_Content::get_url(Dynamic_Content::CUSTOM, '', self::POST_ID), 'empty field name');
        $this->assertSame('', Dynamic_Content::get_link(Dynamic_Content::CUSTOM, 'missing', self::POST_ID), 'missing field');
    }

    public function test_get_gallery()
    {
        $this->field('gallery', 'gallery', ['https://example.test/img-6.jpg', 'https://example.test/img-5.jpg'], ['return_format' => 'url'], ['6', '5']);
        $this->sub_field('row_gallery', 'gallery', [5], ['return_format' => 'id'], ['5']);

        $this->assertSame(
            [['id' => 6, 'url' => 'https://example.test/img-6.jpg'], ['id' => 5, 'url' => 'https://example.test/img-5.jpg']],
            Dynamic_Content::get_gallery(Dynamic_Content::CUSTOM, 'gallery', self::POST_ID),
            'URL-format gallery as ids and urls'
        );
        $this->assertSame([['id' => 5, 'url' => 'https://example.test/img-5.jpg']], Dynamic_Content::get_gallery(Dynamic_Content::SUB, 'row_gallery'), 'sub-field gallery');
        $this->assertSame([], Dynamic_Content::get_gallery(Dynamic_Content::CUSTOM, '', self::POST_ID), 'empty field name');
        $this->assertSame([], Dynamic_Content::get_gallery(Dynamic_Content::CUSTOM, 'missing', self::POST_ID), 'missing field');
    }

    public function test_a_throwing_source_never_breaks_the_widget()
    {
        Functions\when('get_field')->alias(static function () {
            throw new \RuntimeException('ACF extension failed');
        });
        Functions\when('get_sub_field')->alias(static function () {
            throw new \RuntimeException('ACF extension failed');
        });

        $this->assertSame('', Dynamic_Content::get_text(Dynamic_Content::CUSTOM, 'f', self::POST_ID));
        $this->assertSame('', Dynamic_Content::get_text(Dynamic_Content::SUB, 'f'));
        $this->assertSame('', Dynamic_Content::get_link(Dynamic_Content::SUB, 'f'));
        $this->assertSame([], Dynamic_Content::get_gallery(Dynamic_Content::STATIC, null));
    }
}
