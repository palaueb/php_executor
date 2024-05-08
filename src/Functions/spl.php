<?php

use PhpExecutor\NativeEmulatedFunctions;

$functions = NativeEmulatedFunctions::get_instance();

$functions->register_function('getcwd', function($executor, $element, $context) {
    //($this, $element, $context);
    // https://www.php.net/manual/en/function.getcwd.php
    // no arguments
    // Returns the current working directory on success, or FALSE on failure.
    //var_export($executor);

    // returns or the current __DIR__ or the path of the code that is being executed (inside some function or method)

    //implemenent context for the function execution
    
    return $executor->get_current_dir();
    echo "CWD: xxx\n";
    die();
});
