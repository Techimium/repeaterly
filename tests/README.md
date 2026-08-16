# Tests

Dev-only PHPUnit + Brain Monkey test suite. Never shipped — see `.distignore` and the
`deploy-wordpress-plugin.yml` `rsync --exclude` list.

## Running

```
composer install
composer test
```

`composer test` runs `vendor/bin/phpunit` (see `phpunit.xml.dist`) followed by
`tests/check-js.php`, which runs `node --check` over every file under `assets/js/`. Either half
can be run alone via `composer test-php` / `composer test-js`.

No `composer.lock` is committed: `phpunit/phpunit` is constrained to
`^9.6 || ^11.5 || ^12.0` (the range `yoast/phpunit-polyfills` has tested), and no single locked
version satisfies both the PHP 7.4 and PHP 8.4 floors this plugin's CI matrix covers — PHP 7.4
resolves PHPUnit 9.6.x, PHP 8.4 resolves PHPUnit 12.x. Run `composer install` fresh under
whichever PHP version you're testing against.

## Writing a new test

Add a class under `tests/` ending in `Test.php`, extending `Repeaterly\Tests\TestCase`
(`tests/TestCase.php`). That base class wires
[Brain Monkey](https://giuseppe-mazzapica.gitbook.io/brain-monkey/) through
`Yoast\PHPUnitPolyfills\TestCases\TestCase`'s `set_up()`/`tear_down()` overload points (not
PHPUnit's native `setUp()`/`tearDown()` directly — the polyfilled names keep the test compatible
across every PHPUnit major the CI matrix installs).

Inside a test method, mock any WordPress/ACF/Elementor function the code under test calls:

```php
use Brain\Monkey\Functions;

Functions\when('get_field')->justReturn('some value');
Functions\when('get_field_object')->alias(function ($key, $post_id) {
    return $key === 'projects' ? ['type' => 'repeater'] : false;
});
```

**Brain Monkey requires every reached function to be stubbed** — production code's
`function_exists('some_wp_function')` guards do not let you skip stubbing it: once
`Brain\Monkey\setUp()` is active, `function_exists()` reports true for any function name, and an
unstubbed call throws `MissingFunctionExpectations` rather than silently doing nothing.

For WordPress hook globals (`global $post;`), set `$GLOBALS['post']` directly in the test; Brain
Monkey does not manage WP globals, only function calls.

For `add_filter`/`remove_filter`/`apply_filters` assertions, use
`Brain\Monkey\Filters\expectAdded()` / `expectRemoved()` rather than reading a hand-rolled global
filter array.

Keep each test self-contained: set up everything the test needs at the top of the method (or in
`set_up()`) rather than relying on state left behind by another test. PHPUnit does not guarantee
test order.
