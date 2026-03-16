<?php

/**
 * Autoloader
 *
 * This autoloader will load classes based on their class names.
 * Class names should follow the convention: Namespace\Sub_Namespace\My_Class
 * File names should follow the convention: sub-namespace/my-class.php
 *
 * @package Repeaterly
 */

namespace Repeaterly;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Autoload classes based on their class names.
 */
function autoload_classes($class_name) {
    $class_name = ltrim($class_name, '\\');
    if (strpos($class_name, __NAMESPACE__ . '\\') !== 0) {
        return;
    }
    $class_name = str_replace(__NAMESPACE__ . '\\', '', $class_name);

    $file_parts = explode('\\', $class_name);
    $file_name = strtolower(str_replace('_', '-', array_pop($file_parts)));
    $namespace_path = !empty($file_parts) ? strtolower(implode(DIRECTORY_SEPARATOR, $file_parts)) . DIRECTORY_SEPARATOR : '';

    $file_path = plugin_dir_path(__FILE__) . $namespace_path . $file_name . '.php';

    if (file_exists($file_path)) {
        require_once $file_path;
    }
}

// Register the autoloader
spl_autoload_register(__NAMESPACE__ . '\\autoload_classes');