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
    public $debug = true;

    private $path_replace = [];
    private $init_script = '';
    private $php_ini = [];

    private $virtual_path_table = [];
    private $file_content_table = [];

    //here we save the current variables and their values
    //in the execution stack we mimic the architecture of the functions and objects instantiated, also the namespaces.
    private $execution_stack = [];
    //here we saves the code that will be or not executed futurely, also we need to have the current environment stack defined
    private $code_stack = [];

    // provably this will come as a key/key/key on an array
    private $current_environment = [];

    private $custom_constants = [];
    private $current_ns = null;

    public function __construct()
    {
        list($namespace, $node) = $this->split_nameclass(get_class($this));
        $this->current_ns = '\\'.$namespace.'\\';

        return true;
    }

    public function init_execution()
    {
        if($this->debug) echo "Init execution\n";

        $initial_ast = $this->prepare_ast($this->init_script);
        $this->execute_ast($initial_ast);

        var_export($this->execution_stack);
        return;
    }

    //this function get content of PHP file and saves his ast into the file_content_table array
    private function prepare_ast(string $raw_path_filename) 
    {
        if($this->debug) echo "Prepare AST\n";

        $script_content = file_get_contents($raw_path_filename);
        $virtual_path = $this->normalize_path_name($this->init_script);
        $this->virtual_path_table[$virtual_path] = $this->init_script; // we need to know the processed files to avoid loops

        $current_ast = $this->load_ast($script_content);

        $this->file_content_table[$virtual_path] = $current_ast;
        
        return $current_ast;
    }
    public function load_ast(string $string_php_code) 
    {
        if($this->debug) echo "Load AST\n";

        $parser = (new \PhpParser\ParserFactory())->createForNewestSupportedVersion();
        try {
            $ast = $parser->parse($string_php_code);
        } catch (\Error $error) {
            throw new \Exception("Parse error: {$error->getMessage()}\n");
        }

        return $ast;
    }
    
    public function execute_ast($ast_code)
    {
        //this function executes an ast code list, usually a stmt and returns the result
        if($this->debug) echo "Execute AST\n";

        foreach($ast_code as $element){
            $last = $this->execute_ast_element($element);
        }
        return $last;
    }

    //this function executes the ast element and returns the result
    public function execute_ast_element($element, $context = null)
    {
        //TODO: refactor to encapsulate each nodetype into a custom class that being called dinamically
        //      and that must be extendable by ouside user configuration to inject and modify the PHP flow
        
        $class_name = get_class($element);
        if($this->debug) echo "Execute AST element: (" .$class_name. ")\n";

        $context = null;

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
            echo "NULL DETECTED ON ELEMENT!!!!!\n";
            die();
        }
        echo "ELEMENT NOT IN LIST:" . get_class($element) . "\n";
        
        throw new \Exception("Element not recognized:" . get_class($element) . "\n". var_export($element, true) . "\n");
        die();
    }

    public function execute_for($element)
    {
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

            $return = $this->execute_ast($element->stmts);
            var_export($element->stmts);die();
            
            //check if the element is a break, if it is, then break the loop
            
            foreach ($element->loop as $loop) {
                $this->execute_ast_element($loop);
            }
        }
        //if the return value is a break with a count, we need to populate that count up to the upper loops
    }

    public function create_function($element)
    {
        //TODO Needed to implement the right environment for functions/classes/namespaces/objects/etc...
        var_export($element);die("NYESZT");
        $name = $this->execute_ast_element($element->name);
        $this->save_code_in_environment($name, $element);
        var_export($element);die("NYESZT");
        /*
        $params = [];
        foreach($element->params as $param){
            $params[] = $this->execute_ast_element($param);
        }
        $stmts = $element->stmts;
        $this->functions[$name] = function() use ($params, $stmts){
            $this->execution_stack = [];
            foreach($params as $i => $param){
                $this->execution_stack[$param] = func_get_arg($i);
            }
            $this->execute_ast($stmts);
        };
        */
    }
    public function save_code_in_environment($name, $element){
        // TODO: implement the right environment for functions/classes/namespaces/objects/etc...
        //$this->context[$name] = $element;
    }
    public function get_variable_name($var)
    {
        //TODO code this to the right namespace / class object / function environment
        $name=$var->name;
        if(!is_string($name)) $name = $this->execute_ast_element($name); 
        return $name;
    }
    public function exists_variable_name($var)
    {
        //TODO code this to the right namespace / class object / function environment
        $name=$var->name;
        if(!is_string($name)) $name = $this->execute_ast_element($name); 
        return isset($this->execution_stack[$name]);
    }
    public function get_variable_value($name)
    {
        //TODO code this to the right namespace / class object / function environment
        if(!is_string($name)) $name = $this->execute_ast_element($name);
        return $this->execution_stack[$name];
    }
    public function set_variable_value($var, $expr)
    {
        //TODO code this to the right namespace / class object / function environment
        if($this->debug) echo "Set variable: " . var_export($var, true) . " = " . var_export($expr, true) . "\n";
        $this->execution_stack[$var] = $expr;
    }

    public function get_constant($name)
    {
        if($this->debug) echo "Get constant: $name" . PHP_EOL;
        var_export($name);
        if(!isset($this->custom_constants[$name])){
            if(defined($name)){
                return constant($name);
            }
            return null;
        }
        return $this->custom_constants[$name];
    }
    public function halt_execution()
    {
        if($this->debug) echo "Halt execution\n";

        //TODO: implement an extendable way to process data after the end of the execution.

        die();
    }

    private function normalize_path_name($path)
    {
        $path = str_replace(array_keys($this->path_replace), array_values($this->path_replace), $path);
        return $path;
    }
    private function set_replace_path($path_replace)
    {
        $this->path_replace = $path_replace;
        return true;
    }
    private function set_init_script($init_script)
    {
        $this->init_script = $init_script;
        return true;
    }
    private function set_php_ini($php_ini)
    {
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

    public function set_custom_constants($custom_constants)
    {
        $this->custom_constants = $custom_constants;
        return true;
    }
    public function setup_execution($config)
    {
        if($this->debug) echo "Setup execution\n";

        $this->set_php_ini($config['php_ini']);
        $this->set_replace_path($config['path_replace']);
        $this->set_init_script($config['init_script']);
        
        return;
    }
}
