<?php

namespace Repeaterly\Tests;

use Brain\Monkey\Functions;
use Repeaterly\Includes\Acf;

/**
 * Acf::get_gallery_images(): the images a Gallery field stores, in stored order, whatever its
 * Return Format, as the ACF Gallery tag and the gallery widgets need them.
 */
class AcfGalleryImagesTest extends AcfTestCase
{
    protected function set_up()
    {
        parent::set_up();

        Functions\when('wp_get_attachment_url')->alias(static function ($id) {
            return in_array($id, [5, 6, 7], true) ? 'https://example.test/img-' . $id . '.jpg' : false;
        });
    }

    public function test_stored_images_in_order_for_every_return_format()
    {
        $expected = [
            ['id' => 6, 'url' => 'https://example.test/img-6.jpg'],
            ['id' => 5, 'url' => 'https://example.test/img-5.jpg'],
        ];

        // The raw value is the same for every Return Format: that is the point.
        foreach (['array', 'url', 'id'] as $format) {
            $this->field('gallery', 'gallery', ['formatted differently'], ['return_format' => $format], ['6', '5']);

            $this->assertSame($expected, Acf::get_gallery_images('gallery', self::POST_ID), $format . ' format');
        }
    }

    public function test_edge_values()
    {
        // label => [raw value, expected ids]
        $cases = [
            'integer ids' => [[7, 5], [7, 5]],
            'deleted image skipped' => [['5', '99', '6'], [5, 6]],
            'empty list' => [[], []],
            'empty string' => ['', []],
            'null' => [null, []],
            'false' => [false, []],
            'single id, not a list' => ['5', []],
            'non-numeric items skipped' => [['abc', '', null, '5', ['x'], '-3', '0'], [5]],
            'items carrying an ID' => [[['ID' => 6], ['id' => 7]], [6, 7]],
            'duplicates kept in order' => [['5', '5'], [5, 5]],
        ];

        foreach ($cases as $label => [$raw, $ids]) {
            $this->field('gallery', 'gallery', $raw, ['return_format' => 'array'], $raw);

            $this->assertSame($ids, array_column(Acf::get_gallery_images('gallery', self::POST_ID), 'id'), $label);
        }
    }

    public function test_sources_and_non_gallery_fields()
    {
        $this->field('gallery', 'gallery', [], ['return_format' => 'url'], ['5']);
        $this->field('gallery', 'gallery', [], ['return_format' => 'url'], ['6', '7'], 'option');
        $this->sub_field('row_gallery', 'gallery', [], ['return_format' => 'id'], ['7']);
        $this->field('related', 'relationship', [], ['return_format' => 'id'], ['5', '6']);
        $this->field('image', 'image', [], ['return_format' => 'id'], '5');
        $this->meta('plain_gallery', ['5']);

        $this->assertSame([6, 7], array_column(Acf::get_gallery_images('gallery', 'option'), 'id'), 'options page');
        $this->assertSame([7], array_column(Acf::get_gallery_images('row_gallery'), 'id'), 'row sub-field from the current context');
        $this->assertSame([7], array_column(Acf::get_gallery_images('row_gallery', false, true), 'id'), 'sub-field only');
        $this->assertSame([], Acf::get_gallery_images('gallery', false, true), 'sub-field only never reads a top-level field');
        $this->assertSame([], Acf::get_gallery_images('related', self::POST_ID), 'a relationship is not a gallery, even of attachments');
        $this->assertSame([], Acf::get_gallery_images('image', self::POST_ID), 'an image field is not a gallery');
        $this->assertSame([], Acf::get_gallery_images('plain_gallery', self::POST_ID), 'plain meta with no field definition');
        $this->assertSame([], Acf::get_gallery_images('missing', self::POST_ID), 'missing field');
        $this->assertSame([], Acf::get_gallery_images('', self::POST_ID), 'empty field name');
        $this->assertSame([], Acf::get_gallery_images(5, self::POST_ID), 'non-string field name');
    }
}
