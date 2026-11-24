<?php

namespace Repeaterly\Tests;

use Brain\Monkey\Functions;
use Repeaterly\Includes\Acf;

/**
 * Acf::get_taxonomy_term(): the first term the ACF Taxonomy tags read from, and the ACF object ID
 * their custom term fields are read with.
 */
class AcfTaxonomyTermTest extends AcfTestCase
{
    protected function set_up()
    {
        parent::set_up();

        require_once __DIR__ . '/stubs/wp-classes.php';

        Functions\when('get_term')->alias(static function ($id, $taxonomy = '') {
            if (in_array($id, [7, 8], true) && in_array($taxonomy, ['', 'country'], true)) {
                return new \WP_Term($id, 'country');
            }

            return 98 === $id ? new \WP_Error() : null;
        });
    }

    private function term_id($term)
    {
        return $term instanceof \WP_Term ? $term->term_id : $term;
    }

    public function test_first_term_for_every_return_format_and_selection()
    {
        $term_7 = new \WP_Term(7, 'country');
        $term_8 = new \WP_Term(8, 'country');

        // label => [formatted value, settings, stored value, expected term ID]
        $cases = [
            'term object' => [$term_7, ['taxonomy' => 'country', 'return_format' => 'object'], 7, 7],
            'term id' => [7, ['taxonomy' => 'country', 'return_format' => 'id'], 7, 7],
            'term id stored as a string' => ['7', ['taxonomy' => 'country', 'return_format' => 'id'], '7', 7],
            'several term objects: first' => [[$term_8, $term_7], ['taxonomy' => 'country', 'return_format' => 'object', 'field_type' => 'checkbox'], [8, 7], 8],
            'several term ids: first' => [[8, 7], ['taxonomy' => 'country', 'return_format' => 'id', 'field_type' => 'multi_select'], [8, 7], 8],
            'no taxonomy setting' => [7, ['return_format' => 'id'], 7, 7],
            'deleted first term is skipped' => [[99, 7], ['taxonomy' => 'country', 'return_format' => 'id'], [99, 7], 7],
            'deleted term object is skipped' => [[7], ['taxonomy' => 'country', 'return_format' => 'object'], [99, 7], 7],
            'only a deleted term' => [99, ['taxonomy' => 'country', 'return_format' => 'id'], 99, null],
            'term lookup error' => [98, ['taxonomy' => 'country', 'return_format' => 'id'], 98, null],
            'term in another taxonomy' => [7, ['taxonomy' => 'language', 'return_format' => 'id'], 7, null],
            'empty value' => ['', ['taxonomy' => 'country', 'return_format' => 'id'], '', null],
            'empty list' => [[], ['taxonomy' => 'country', 'return_format' => 'id'], [], null],
            'false value' => [false, ['taxonomy' => 'country', 'return_format' => 'id'], false, null],
        ];

        foreach ($cases as $label => [$formatted, $settings, $stored, $expected]) {
            $this->field('country', 'taxonomy', $formatted, $settings, $stored);

            $this->assertSame($expected, $this->term_id(Acf::get_taxonomy_term('country', self::POST_ID)), $label);
        }
    }

    public function test_all_terms_in_stored_order()
    {
        // label => [stored value, expected term IDs]
        $cases = [
            'several terms keep stored order' => [[8, 7], [8, 7]],
            'single term id' => [7, [7]],
            'string ids' => [['8', '7'], [8, 7]],
            'deleted terms are skipped' => [[8, 99, 98, 7], [8, 7]],
            'duplicates stay as stored' => [[7, 7], [7, 7]],
            'empty list' => [[], []],
            'empty value' => ['', []],
            'false value' => [false, []],
            'only deleted terms' => [[99], []],
        ];

        foreach ($cases as $label => [$stored, $expected]) {
            $this->field('country', 'taxonomy', $stored, ['taxonomy' => 'country', 'return_format' => 'id'], $stored);

            $ids = array_map([$this, 'term_id'], Acf::get_taxonomy_terms('country', self::POST_ID));

            $this->assertSame($expected, $ids, $label);
        }

        $this->field('headline', 'text', 'Hello');

        $this->assertSame([], Acf::get_taxonomy_terms('headline', self::POST_ID), 'field that is not a Taxonomy field');
        $this->assertSame([], Acf::get_taxonomy_terms('missing', self::POST_ID), 'missing field');
        $this->assertSame([], Acf::get_taxonomy_terms('', self::POST_ID), 'empty field name');
    }

    public function test_sources_and_missing_fields()
    {
        $this->field('country', 'taxonomy', 7, ['taxonomy' => 'country', 'return_format' => 'id']);
        $this->field('country', 'taxonomy', 8, ['taxonomy' => 'country', 'return_format' => 'id'], null, 'option');
        $this->sub_field('row_country', 'taxonomy', 8, ['taxonomy' => 'country', 'return_format' => 'id']);
        $this->field('headline', 'text', 'Hello');

        $this->assertSame(8, $this->term_id(Acf::get_taxonomy_term('country', 'option')), 'options page');
        $this->assertSame(7, $this->term_id(Acf::get_taxonomy_term('country')), 'current post');
        $this->assertSame(8, $this->term_id(Acf::get_taxonomy_term('row_country')), 'repeater row sub-field');
        $this->assertNull(Acf::get_taxonomy_term('headline', self::POST_ID), 'field that is not a Taxonomy field');
        $this->assertNull(Acf::get_taxonomy_term('missing', self::POST_ID), 'missing field');
        $this->assertNull(Acf::get_taxonomy_term('', self::POST_ID), 'empty field name');
        $this->assertNull(Acf::get_taxonomy_term(['x'], self::POST_ID), 'non-string field name');
    }

    public function test_term_fields_are_read_from_the_term()
    {
        $this->field('country', 'taxonomy', 7, ['taxonomy' => 'country', 'return_format' => 'id']);
        $this->field('flag', 'image', ['id' => 5, 'url' => 'https://example.test/fr.png'], ['return_format' => 'array'], 5, 'term_7');
        $this->field('native_name', 'text', 'Français', [], null, 'term_7');
        $this->field('site', 'url', 'https://example.test/fr', [], null, 'term_7');
        $this->field('flag', 'image', ['id' => 6, 'url' => 'https://example.test/es.png'], ['return_format' => 'array'], 6, 'term_8');

        $term = Acf::get_taxonomy_term('country', self::POST_ID);
        $post_id = Acf::get_term_post_id($term);

        $this->assertSame('term_7', $post_id, 'ACF object ID of the term');
        $this->assertSame(['id' => 5, 'url' => 'https://example.test/fr.png'], Acf::get_field_value('flag', $post_id), 'image');
        $this->assertSame('Français', Acf::get_field_text('native_name', $post_id), 'text');
        $this->assertSame('https://example.test/fr', Acf::get_field_url('site', $post_id), 'URL');
        $this->assertSame('', Acf::get_field_text('missing', $post_id), 'term without that field');
        $this->assertSame('', Acf::get_field_text('', $post_id), 'empty term field name');
    }

    public function test_acf_inactive_gives_no_term()
    {
        $this->field('country', 'taxonomy', 7, ['taxonomy' => 'country', 'return_format' => 'id']);
        Functions\when('get_field_object')->justReturn(false);

        $this->assertNull(Acf::get_taxonomy_term('country', self::POST_ID));
    }
}
