<?php

namespace PhpExecutor\Node\Scalar;

Class MagicConst {
        //PhpParser\Node\Scalar\MagicConst\Dir
    public static function Dir($executor, $element, $context = null) {
        // The directory of the file. If used inside an include, the directory of the included file is returned. This is equivalent to dirname(__FILE__) 
        $dir = dirname($executor->get_current_file());
        $executor->echo("MagicConst::Dir: $dir", 'B');
        return $dir;
    }
    //PhpParser\Node\Scalar\MagicConst\File
    public static function File($executor, $element, $context = null) {
        // The full path and filename of the file. If used inside an include, the name of the included file is returned. Since PHP 4.0.2, 
        // __FILE__ always contains an absolute path with symlinks resolved whereas in older versions it contained relative path under some circumstances.
        $file = $executor->get_current_file();
        $executor->echo("MagicConst::File: $file", 'B');
        return $file;

    }
}