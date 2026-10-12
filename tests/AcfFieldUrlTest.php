<?php

namespace Repeaterly\Tests;

use Brain\Monkey\Functions;
use Repeaterly\Includes\Acf;

/**
 * Acf::get_field_url(): what the ACF URL tag and the Dynamic widgets' links point to, for every
 * field type the URL tag lists and every Return Format of that type.
 */
class AcfFieldUrlTest extends AcfTestCase
{
    protected function set_up()
    {
        parent::set_up();

        require_once __DIR__ . '/stubs/wp-classes.php';

        Functions\when('wp_get_attachment_url')->alias(static function ($id) {
            return in_array($id, [5, 6], true) ? 'https://example.test/file-' . $id . '.jpg' : false;
        });
        Functions\when('wp_get_attachment_image_src')->alias(static function ($id, $size) {
            return 5 === $id ? ['https://example.test/file-5-' . $size . '.jpg', 1, 1, false] : false;
        });
        Functions\when('get_permalink')->alias(static function ($post) {
            $id = $post instanceof \WP_Post ? $post->ID : $post;

            return in_array($id, [3, 4], true) ? 'https://example.test/post-' . $id . '/' : false;
        });
        Functions\when('get_term_link')->alias(static function ($term, $taxonomy = '') {
            $id = $term instanceof \WP_Term ? $term->term_id : $term;

            return in_array($id, [7, 8], true) ? 'https://example.test/term-' . $id . '/' : new \WP_Error();
        });
        Functions\when('is_wp_error')->alias(static function ($thing) {
            return $thing instanceof \WP_Error;
        });
    }

    public function test_every_listed_field_type_and_return_format()
    {
        $post_3 = new \WP_Post(3);
        $post_4 = new \WP_Post(4);
        $term_7 = new \WP_Term(7);
        $term_8 = new \WP_Term(8);
        $image_5 = ['ID' => 5, 'id' => 5, 'url' => 'https://example.test/file-5.jpg'];

        // label => [type, formatted value, settings, raw value, expected URL]
        $cases = [
            'text' => ['text', 'https://example.test/typed', [], null, 'https://example.test/typed'],
            'url' => ['url', 'https://example.test/page', [], null, 'https://example.test/page'],
            'empty url' => ['url', '', [], null, ''],
            'email' => ['email', 'hello@example.test', [], null, 'mailto:hello@example.test'],
            'empty email' => ['email', '', [], null, ''],
            'link array' => ['link', ['title' => 'T', 'url' => 'https://example.test/link', 'target' => ''], ['return_format' => 'array'], null, 'https://example.test/link'],
            'link url' => ['link', 'https://example.test/link', ['return_format' => 'url'], null, 'https://example.test/link'],
            'link array without url' => ['link', ['title' => 'T'], ['return_format' => 'array'], null, ''],
            'image array' => ['image', $image_5, ['return_format' => 'array'], 5, 'https://example.test/file-5.jpg'],
            'image url' => ['image', 'https://example.test/file-5.jpg', ['return_format' => 'url'], 5, 'https://example.test/file-5.jpg'],
            'image id is the full size' => ['image', 5, ['return_format' => 'id'], 5, 'https://example.test/file-5-full.jpg'],
            'image deleted (id)' => ['image', 99, ['return_format' => 'id'], 99, ''],
            'image deleted (url)' => ['image', false, ['return_format' => 'url'], 99, ''],
            'file array' => ['file', ['ID' => 6, 'url' => 'https://example.test/file-6.jpg'], ['return_format' => 'array'], 6, 'https://example.test/file-6.jpg'],
            'file url' => ['file', 'https://example.test/file-6.jpg', ['return_format' => 'url'], 6, 'https://example.test/file-6.jpg'],
            'file id' => ['file', 6, ['return_format' => 'id'], 6, 'https://example.test/file-6.jpg'],
            'file deleted (id)' => ['file', 99, ['return_format' => 'id'], 99, ''],
            'post object' => ['post_object', $post_3, ['return_format' => 'object'], '3', 'https://example.test/post-3/'],
            'post id' => ['post_object', 3, ['return_format' => 'id'], '3', 'https://example.test/post-3/'],
            'post numeric string id' => ['post_object', '3', ['return_format' => 'id'], '3', 'https://example.test/post-3/'],
            'multi post object: first' => ['post_object', [$post_4, $post_3], ['return_format' => 'object', 'multiple' => 1], ['4', '3'], 'https://example.test/post-4/'],
            'multi post id: first' => ['post_object', [4, 3], ['return_format' => 'id', 'multiple' => 1], ['4', '3'], 'https://example.test/post-4/'],
            'relationship object: first' => ['relationship', [$post_4, $post_3], ['return_format' => 'object'], ['4', '3'], 'https://example.test/post-4/'],
            'relationship id: first' => ['relationship', [4, 3], ['return_format' => 'id'], ['4', '3'], 'https://example.test/post-4/'],
            'deleted post' => ['post_object', 99, ['return_format' => 'id'], '99', ''],
            'empty relationship' => ['relationship', [], ['return_format' => 'object'], [], ''],
            'post object false' => ['post_object', false, ['return_format' => 'object'], '', ''],
            'page link' => ['page_link', 'https://example.test/post-3/', [], '3', 'https://example.test/post-3/'],
            'multi page link: first' => ['page_link', ['https://example.test/post-4/', 'https://example.test/post-3/'], ['multiple' => 1], ['4', '3'], 'https://example.test/post-4/'],
            'taxonomy object' => ['taxonomy', $term_7, ['taxonomy' => 'category', 'return_format' => 'object'], '7', 'https://example.test/term-7/'],
            'taxonomy id' => ['taxonomy', 7, ['taxonomy' => 'category', 'return_format' => 'id'], '7', 'https://example.test/term-7/'],
            'multi taxonomy: first' => ['taxonomy', [8, 7], ['taxonomy' => 'category', 'return_format' => 'id'], ['8', '7'], 'https://example.test/term-8/'],
            'multi taxonomy objects: first' => ['taxonomy', [$term_8, $term_7], ['taxonomy' => 'category', 'return_format' => 'object'], ['8', '7'], 'https://example.test/term-8/'],
            'deleted term' => ['taxonomy', 99, ['taxonomy' => 'category', 'return_format' => 'id'], '99', ''],
            'taxonomy without a taxonomy setting' => ['taxonomy', 7, ['return_format' => 'id'], '7', 'https://example.test/term-7/'],
            'oembed is its source URL' => ['oembed', '<iframe></iframe>', [], 'https://video.test/v', 'https://video.test/v'],
            'choice is its text' => ['select', ['value' => 'blue', 'label' => 'Blue'], ['return_format' => 'array', 'choices' => ['blue' => 'Blue']], 'blue', 'Blue'],
            'number is its text' => ['number', 42, [], null, '42'],
            'gallery has no URL' => ['gallery', [$image_5], ['return_format' => 'array'], ['5'], ''],
            'repeater has no URL' => ['repeater', [['a' => 1]], [], null, ''],
            'object from a filter on a text field' => ['text', (object) ['x' => 1], [], null, ''],
            'object from a filter on a link field' => ['link', (object) ['url' => 'x'], ['return_format' => 'array'], null, ''],
        ];

        foreach ($cases as $label => [$type, $formatted, $settings, $raw, $expected]) {
            $this->field('f', $type, $formatted, $settings, $raw);

            $this->assertSame($expected, Acf::get_field_url('f', self::POST_ID), $label);
        }
    }

    public function test_sources_and_missing_fields()
    {
        $this->field('target', 'url', 'https://example.test/post');
        $this->field('target', 'url', 'https://example.test/options', [], null, 'option');
        $this->sub_field('row_target', 'link', ['url' => 'https://example.test/row'], ['return_format' => 'array']);
        $this->meta('plain_url', 'https://example.test/meta');

        $this->assertSame('https://example.test/options', Acf::get_field_url('target', 'option'), 'options page');
        $this->assertSame('https://example.test/post', Acf::get_field_url('target'), 'current post');
        $this->assertSame('https://example.test/row', Acf::get_field_url('row_target'), 'row sub-field from the current context');
        $this->assertSame('https://example.test/row', Acf::get_field_url('row_target', false, true), 'sub-field only');
        $this->assertSame('', Acf::get_field_url('target', false, true), 'sub-field only never reads a top-level field');
        $this->assertSame('https://example.test/meta', Acf::get_field_url('plain_url', self::POST_ID), 'plain meta with no field definition');
        $this->assertSame('', Acf::get_field_url('missing', self::POST_ID), 'missing field');
        $this->assertSame('', Acf::get_field_url('', self::POST_ID), 'empty field name');
        $this->assertSame('', Acf::get_field_url(['x'], self::POST_ID), 'non-string field name');
    }

    public function test_plain_meta_is_looked_up_as_an_acf_field_only_once()
    {
        $this->meta('plain_url', 'https://example.test/meta');

        $lookups = 0;
        Functions\when('get_field_object')->alias(static function () use (&$lookups) {
            $lookups++;

            return false;
        });

        $this->assertSame('https://example.test/meta', Acf::get_field_url('plain_url', self::POST_ID));
        $this->assertSame(1, $lookups, 'the fallback reads the meta directly instead of repeating the field lookup');
    }
}
