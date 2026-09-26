<?php

namespace Repeaterly\Tests;

use Repeaterly\Includes\Acf;
use Repeaterly\Includes\Acf_Context;
use Repeaterly\Includes\Dynamic_Content;

/**
 * Pins the public ACF API that Repeaterly Pro calls directly. Pro updates separately from Free, so
 * a Pro release already on a site keeps calling these after Free updates: renaming, reordering or
 * re-defaulting a parameter, or dropping a method or constant, breaks live sites.
 *
 * Adding a method or an optional trailing parameter is fine; this test only guards what exists.
 */
class PublicApiContractTest extends TestCase
{
    protected function set_up()
    {
        parent::set_up();

        require_once dirname(__DIR__) . '/includes/acf-context.php';
        require_once dirname(__DIR__) . '/includes/acf.php';
        require_once dirname(__DIR__) . '/includes/dynamic-content.php';
    }

    public function test_public_methods_keep_their_signatures()
    {
        // method => [parameter name => default, or REQUIRED when it has none], in order.
        $contract = [
            Acf::class => [
                'get_options_pages' => [],
                'resolve_field' => ['key' => self::REQUIRED, 'post_id' => false, 'format_value' => true, 'load_value' => true],
                'get_raw_field_value' => ['key' => self::REQUIRED, 'post_id' => false],
                'have_rows' => ['key' => self::REQUIRED, 'post_id' => false],
                'get_field_value' => ['key' => self::REQUIRED, 'post_id' => false],
            ],
            Acf_Context::class => [
                'normalize_post_id' => ['post_id' => self::REQUIRED],
                'get_render_post_id' => [],
                'get_main_queried_post_id' => [],
                'resolve_post_id' => ['post_id' => false],
                'resolve_field_post_id' => ['field_name' => self::REQUIRED, 'post_id' => false],
                'with_acf_post_id' => ['post_id' => self::REQUIRED, 'callback' => self::REQUIRED],
                'is_empty_post_id' => ['post_id' => self::REQUIRED],
            ],
            Dynamic_Content::class => [
                'get_text_sources' => [],
                'get_link_sources' => [],
                'get_image_sources' => [],
                'get_value' => ['source_name' => self::REQUIRED, 'source_value' => null, 'post_id' => false],
            ],
        ];

        foreach ($contract as $class => $methods) {
            foreach ($methods as $method => $parameters) {
                $label = $class . '::' . $method;

                $this->assertTrue(method_exists($class, $method), $label . ' exists');

                $reflection = new \ReflectionMethod($class, $method);

                $this->assertTrue($reflection->isPublic() && $reflection->isStatic(), $label . ' is public static');
                $this->assertSame(array_keys($parameters), array_map(static function (\ReflectionParameter $parameter) {
                    return $parameter->getName();
                }, array_slice($reflection->getParameters(), 0, count($parameters))), $label . ' parameter names and order');

                foreach ($reflection->getParameters() as $position => $parameter) {
                    if ($position >= count($parameters)) {
                        $this->assertTrue($parameter->isOptional(), $label . ' only adds optional parameters');
                        continue;
                    }

                    $expected = $parameters[$parameter->getName()];

                    if (self::REQUIRED === $expected) {
                        $this->assertFalse($parameter->isOptional(), $label . ' $' . $parameter->getName() . ' stays required');
                        continue;
                    }

                    $this->assertTrue($parameter->isDefaultValueAvailable(), $label . ' $' . $parameter->getName() . ' keeps a default');
                    $this->assertSame($expected, $parameter->getDefaultValue(), $label . ' $' . $parameter->getName() . ' default');
                }
            }
        }
    }

    public function test_dynamic_content_source_constants_keep_their_values()
    {
        $constants = [
            'STATIC' => 'static',
            'CUSTOM' => 'custom',
            'SUB' => 'sub',
            'POST_TITLE' => 'post_title',
            'POST_CONTENT' => 'post_content',
            'POST_EXCERPT' => 'post_exerpt',
            'POST_URL' => 'post_url',
            'FEATURED_IMAGE' => 'featured_image',
        ];

        foreach ($constants as $name => $value) {
            $this->assertSame($value, constant(Dynamic_Content::class . '::' . $name), 'Dynamic_Content::' . $name . ' is saved in Elementor data');
        }
    }

    private const REQUIRED = '__required__';
}
