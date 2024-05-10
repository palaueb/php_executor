<?php

use PhpExecutor\NativeEmulatedFunctions;

$functions = NativeEmulatedFunctions::get_instance();

$functions->register_function('ssstrtok', function($executor, $element, $context) {
    //($this, $element, $context);
    // https://www.php.net/manual/en/function.strtok.php
    
    echo "strtok: xxx\n";
    die();
});
