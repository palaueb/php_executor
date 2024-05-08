<?php
// YES I KNOW: Go and use xdebug, this is my headache.

// TODO: Different levels of debug, and different outputs
// all class, namespace, function, etc. declarations and execution, levels of execution.


/*
 things to know:
 - The use of "," (comma) inside loops, if statements, etc. is to separate expressions, and they are executed in order, but only the last one is returned. For example: for($i=0;$i<10,$i<20;$i++){} $i will be 20 at the end of the loop. It means that is not the same echo("Hello world"), than echo "Hello"," ","world"; 
*/
namespace PhpExecutor;

class PhpExecutor
{
    public $isDebug = 3;

    // sometimes we need to execute the PHP as it was in a different path
    // so you can use this to replace the paths in the code
    //TODO in a near future, not important now
    //private $path_replace = [];

    //path to the initial script to be executed 
    private $init_script = ''; 
    //php_ini configuration values
    private $php_ini = [];

    // here we save the paths of the files that we have already processed
    // [virtual_path => real_path]
    private $virtual_path_table = [];
    // here we save the ast of the files that we have already processed
    // the virtual path is the key (not the real path)
    private $file_content_table = [];

    //here we save the current variables and their values
    //in the execution stack we mimic the architecture of the functions and objects instantiated, also the namespaces.
    private $execution_stack = [];
    //here we save the code that will be or not executed futurely,
    // also we need to have the current environment stack defined
    private $code_stack = []; //this must be a multidimensional array

    // as the code_stack, it needs to mymyc the architecture of the code_stack
    private $current_environment = [];

    // here we save the contants that we want to override
    private $custom_constants = [];

    // this value is the current namespace that we are working on
    // we save it because we use it to call the right class to execute the right node type
    private $current_ns = null;

    // this value is the current script that we are working on processing its ast
    private $current_file = [];

    // the root folder of the PhpExecutor execution
    private $root_path = null;
    // the root folder of the main file we are executing
    private $php_path = null;
    
    private $deep_ast = 0;
    public function __construct() {
        list($namespace, $node) = $this->split_nameclass(get_class($this));
        $this->current_ns = '\\'.$namespace.'\\';

        return true;
    }

    public function init_execution() {
        $this->echo("Init execution", 'Y');

        //real path of the script
        $this->root_path = $this->get_root_path($this->init_script);
        //virtual path of the main script
        $this->php_path = $this->get_php_path($this->init_script);

        $initial_ast = $this->prepare_ast($this->php_path);
        $this->execute_ast($initial_ast, '\\');

        $this->halt_execution();
        return;
    }

    //this function get content of PHP file and saves his ast into the file_content_table array
    private function prepare_ast(string $raw_path_filename) {
        $this->echo("Prepare AST", 'Y');

        $raw_path_filename = $this->populate_path($raw_path_filename);

        //echo "FILE GET CONTENTS: $raw_path_filename \n";die();

        $script_content = @file_get_contents($raw_path_filename);
        if($script_content === false){
            //TODO: search for a better solution, in PHP this is usually a warning, 
            // to maintain this behavior it needs to be able to implement wich version of PHP we are using
            $this->echo("File not found: $raw_path_filename", 'R');
            die(); 
        }
        /*
        $virtual_path = $this->normalize_path_name($raw_path_filename);
        $this->virtual_path_table[$virtual_path] = $raw_path_filename; // we need to know the processed files to avoid loops
        */
        $this->get_in_file($raw_path_filename);

        $current_ast = $this->load_ast($script_content);
        
        $this->file_content_table[$raw_path_filename] = $current_ast;
        
        return $current_ast;
    }
    public function load_ast(string $string_php_code) 
    {
        $this->echo("Load AST", 'Y');

        $parser = (new \PhpParser\ParserFactory())->createForNewestSupportedVersion();
        try {
            $ast = $parser->parse($string_php_code);
        } catch (\Error $error) {
            throw new \Exception("Parse error: {$error->getMessage()}\n");
        }

        return $ast;
    }
    
    public function execute_ast($ast_code, $context) {
        //this function executes an ast code list, usually a stmt and returns the result
        $this->echo("Execute AST in context [$context]", 'G');

        foreach($ast_code as $element){
            $last = $this->execute_ast_element($element, $context);
        }
        return $last;
    }

    //this function executes the ast element and returns the result
    public function execute_ast_element($element, $context = null) {
        //TODO: refactor to encapsulate each nodetype into a custom class that being called dinamically
        //      and that must be extendable by ouside user configuration to inject and modify the PHP flow
        
        $class_name = get_class($element);
        $this->echo( "Execute AST element: (" .$class_name. ") in context: [$context]", 'E');

        //IMPLEMENT THE EXECUTION OF EACH NODE TYPE DYNAMICALLY FROM OUTSIDE THIS FILE
        $class_name = str_replace('PhpParser\\', $this->current_ns, $class_name);

        list($nameclass, $node) = $this->split_nameclass($class_name);
        if (class_exists($nameclass, true) && method_exists($nameclass, $node)) {   
            //call the static method $node from the class $nameclass passing 3 arguments, $this, $element and $context
            $result = $nameclass::$node($this, $element, $context);
            return $result;
        }

        // IF THE NODE TYPE IS NOT IMPLEMENTED, THEN THROW AN EXCEPTION
        if(is_null($element)) {
            $this->echo("NULL DETECTED ON ELEMENT!!!!!", 'R');
            die();
        }
       
        throw new \Exception("Element not recognized: (" . get_class($element) . ")\n". var_export($element, true) . "\n");
        die();
    }




    public function get_in_file($filepath){
        $depth = count($this->current_file);
        $message = str_pad("Get in file: $filepath", $depth, ">", STR_PAD_LEFT);
        $this->echo($message , 'M');
        $this->current_file[] = $filepath;
    }
    public function get_out_file(){
        $this->echo( "Get out file: [" . end($this->current_file) . "]", 'M');
        array_pop($this->current_file);
    }
    public function get_current_file(){
        $this->echo("Get current file: [" . end($this->current_file) . "]" , 'M');
        return end($this->current_file);
    }
    public function include_file($filepath, $type, $context = null) {
        $this->echo( "Include file: [$filepath] as once: ($type)" , 'M');
        //2: include_once, 3: require, 4: require_once, 1: include, 0: include_once

        //normalize means that we save the virtual path to the file, 
        // this means that we have a real context of execution and an emulated virtual context
        //$virtual_path = $this->normalize_path_name($filepath);
    
        $once = ($type == 2 || $type == 4);
        if($once && isset($this->virtual_path_table[$filepath])){
            // when you do a _once, it means that you only want to include the file once, if it is already included, then skip it.
            return true;
        }
        
        $ast = $this->prepare_ast($filepath);
        $result = $this->execute_ast($ast, $context);

        $this->get_out_file();

        return $result;
    }
    public function execute_for($element, $context) {
        //init variables
        foreach($element->init as $init){
            $this->execute_ast_element($init);
        }
        $condCount = count($element->cond);
        $isTrue = false;
        if($condCount == 0){ //if there is no condition, then it is always true
            $isTrue = true;
        }else{ //if there is a list of conditions, then we need to get the last one
            $lastCond = $element->cond[$condCount - 1];
        }
        
        while ($isTrue || $this->execute_ast_element($lastCond)) {
            //iterate for each condition except the last one and execute_ast_element on all of them
            for ($i = 0; $i < $condCount - 1; $i++) {
                $this->execute_ast_element($element->cond[$i]);
            }
            //TODO implement the break count

            $return = $this->execute_ast($element->stmts, $context);
            var_export($element->stmts);die();
            
            //check if the element is a break, if it is, then break the loop
            
            foreach ($element->loop as $loop) {
                $this->execute_ast_element($loop);
            }
        }
        //if the return value is a break with a count, we need to populate that count up to the upper loops
    }
    public function execute_function($element, $context) {
        $name = $this->execute_ast_element($element->name, $context);

        $this->echo("Execute function: $name", 'M');

        var_export($element);
        
        //$this->execute_ast($element->stmts, $context);

        $args = [];
        foreach($element->args as $arg) {
            $args[] = $this->execute_ast_element($arg, $context);
        }
        //TODO: we need to have our own functions proxy to call native functions or user defined functions
        //$result = call_user_func_array($name, $args);
        die("BEST FUNCTION EVER");
        return true;
    }





    public function create_function($element, $context) {
        $name = $this->execute_ast_element($element->name, $context);
        $this->code_stack[$context][$name] = $element;
        return true;
    }
    public function create_class($element, $context) {
        $name = $this->execute_ast_element($element->name, $context);
        $element->metadata = [
            'context' => $context,
            'filename' => $this->get_current_file()
        ];
        $this->code_stack[$context][$name] = $element;
        return true;
    }
    public function create_namespace($element, $context) {
        $name = $this->execute_ast_element($element->name, $context);
        $new_context = $context . $name . "\\";
        $stmts = $element->stmts;
        $this->execute_ast($stmts, $new_context);
        
        var_export($this->code_stack);
        die("CREATE NAMESPACE");
        return true;
    }


    public function get_variable_name($var) {
        //TODO code this to the right namespace / class object / function environment
        $name=$var->name;
        if(!is_string($name)) $name = $this->execute_ast_element($name); 
        return $name;
    }
    public function exists_variable_name($var) {
        //TODO code this to the right namespace / class object / function environment
        $name=$var->name;
        if(!is_string($name)) $name = $this->execute_ast_element($name); 
        return isset($this->execution_stack[$name]);
    }
    public function get_variable_value($name) {
        //TODO code this to the right namespace / class object / function environment
        if(!is_string($name)) $name = $this->execute_ast_element($name);
        return $this->execution_stack[$name];
    }
    public function set_variable_value($var, $expr) {
        //TODO code this to the right namespace / class object / function environment
        $this->echo("Set variable: " . var_export($var, true) . " = " . var_export($expr, true), 'M');
        $this->execution_stack[$var] = $expr;
    }
    public function get_constant($name) {
        $this->echo("Get constant: $name", 'M');
        if(!isset($this->custom_constants[$name])){
            if(defined($name)){
                return constant($name);
            }
            return null;
        }
        return $this->custom_constants[$name];
    }
    public function halt_execution() {
        $this->echo("Halt execution", 'R');

        $this->echo(var_export($this->code_stack, true), 'E');
        //TODO: implement an extendable way to process data after the end of the execution.

        die();
    }

    //convert relative paths to absolute paths from __DIR__
    private function populate_path($path) {
        $this->echo("Populate path: $path", 'M');

        if(substr($path, 0, 1) == '/'){
            return $path;
        }
        if(substr($path, 0, 2) == './'){
            $path = substr($path, 2);
        }
        $pop_path = dirname($this->get_current_file()) . '/' . $path;
        $this->echo("Populated path: $pop_path", 'M');

        return $pop_path;
    }
    
    private function get_root_path($filepath) {
        $this->echo("Get root path: $filepath", 'M');
        if(substr($filepath, 0, 1) == '/'){
            return dirname($filepath);
        }
        if(substr($filepath, 0, 2) == './'){
            $filepath = substr($filepath, 2);
        }
        $cwd = getcwd();
        //$filepath = basename($filepath);

        $root_path = $cwd . '/' . $filepath;
        return dirname($root_path);
    }
    private function get_php_path($filepath){
        $this->echo("Get PHP path: $filepath", 'M');
        if(substr($filepath, 0, 1) == '/'){
            return $filepath;
        }
        if(substr($filepath, 0, 2) == './'){
            $filepath = substr($filepath, 2);
        }
        $filepath = getcwd() . '/' . $filepath;

        return $filepath;
    }
    /*
    private function normalize_path_name($path) {
        $path = str_replace(array_keys($this->path_replace), array_values($this->path_replace), $path);
        return $path;
    }
    private function set_replace_path($path_replace) {
        $this->path_replace = $path_replace;
        return true;
    }
    */
    private function set_init_script($init_script) {
        $this->init_script = $init_script;
        return true;
    }
    private function set_php_ini($php_ini) {
        $this->php_ini = $php_ini;
        return true;
    }
    private function split_nameclass($fullClassName) {
        $lastBackslashPos = strrpos($fullClassName, '\\');
    
        if ($lastBackslashPos !== false) {
            $namespace = substr($fullClassName, 0, $lastBackslashPos);
            $className = substr($fullClassName, $lastBackslashPos + 1);
            return [$namespace, $className];
        } else {
            // No se encontró el backslash, puede ser solo un nombre de clase
            return [null, $fullClassName];
        }
    }

    public function set_custom_constants($custom_constants) {
        $this->custom_constants = $custom_constants;
        return true;
    }
    public function setup_execution($config) {
        $this->echo("Setup execution", 'Y');

        $this->set_php_ini($config['php_ini']);
        //$this->set_replace_path($config['path_replace']);
        $this->set_init_script($config['init_script']);
        
        return;
    }



    public $colors = [
        'R' => "\033[31m", // red level 0
        'G' => "\033[32m", // green level 2
        'Y' => "\033[33m", // yellow level 1
        'B' => "\033[34m", // blue level 3
        'M' => "\033[35m", // magenta level 1
        'C' => "\033[36m", // cyan level 2
        'W' => "\033[37m", // white no level
        'E' => "\033[90m", // grey level 4
        'S' => "\033[0m", // reset
    ];

    // Only prints white, other colors are for debug purposes
    public function echo($text, $color) {
        //echo "RAW: $text [$color]\n";
        // log levels by color, White is allways visible, the rest needs to have $this->isDebug = true
        if ($color != 'W' && $this->isDebug === false) {
            return false;
        }

        if (!isset($this->colors[$color])) {
            die("NO COLOR DEFINED!");
        }
        if($this->isDebug === 0 && !in_array($color, ['R', 'W'])) {
            return false;
        }
        if($this->isDebug === 1 && !in_array($color, ['R', 'W','Y', 'M'])) {
            return false;
        }
        if($this->isDebug === 2 && !in_array($color, ['R', 'W','Y', 'M', 'G', 'C'])) {
            return false;
        }
        if($this->isDebug === 3 && !in_array($color, ['R', 'W','Y', 'M', 'G', 'C', 'B'])) {
            return false;
        }
        
        if($color == 'W'){
            $text = '$ ' . $text;
        }else{
            $text .= PHP_EOL;
        }

        echo $this->colors[$color] . $text . $this->colors['S'];
    }
}
