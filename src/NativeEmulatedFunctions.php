<?php

namespace PhpExecutor;

final class NativeEmulatedFunctions {
    private static $instance = null;
    private $functions = [];

    private function __construct() {}

    public function init_load_libs() {
        $this->load_functions_from_file(__DIR__.'/Functions/SPL.php');
        $this->load_functions_from_file(__DIR__.'/Functions/strings.php');
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_function($name, callable $function) {
        $this->functions[$name] = $function;
    }

    public function function_exists($name) {
        return array_key_exists($name, $this->functions);
    }
    public function get_list_registered_functions() {
        return array_keys($this->functions);
    }

    public function execute_function($name, ...$args) {
        if ($this->function_exists($name)) {
            return call_user_func_array($this->functions[$name], $args);
        }
        throw new \Exception("Function '{$name}' is not registered.");
    }

    public function load_functions_from_file($filePath) {
        require_once $filePath;
    }
}
