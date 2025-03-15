<?php
/**
 * Autoloader
 *
 * This autoloader will load classes based on their class names.
 * Class names should follow the convention: My_Class
 * File names should follow the convention: my-class.php
 *
 * @package Repeaterly
 */

namespace Repeaterly;

/**
 * Autoload classes based on their class names.
 */
function autoload_classes($class_name) {
    $class_name = ltrim($class_name, '\\');
    $class_name = str_replace(__NAMESPACE__.'\\', '', $class_name);
    $file_name = strtolower(str_replace('_', '-', $class_name));
    $file_path = plugin_dir_path(__FILE__) . $file_name . '.php';

    if (file_exists($file_path)) {
        require_once $file_path;
    }
}

// Register the autoloader
spl_autoload_register(__NAMESPACE__ . '\\autoload_classes');