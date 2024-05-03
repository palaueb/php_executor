<?php
// YES I KNOW: Go and use xdebug, this is my headache.

// TODO: Different levels of debug, and different outputs
// all class, namespace, function, etc. declarations and execution, levels of execution.

namespace Palaueb\PhpExecutor;

class PhpExecutor
{
    public $debug = true;

    private $path_replace = [];
    private $init_script = '';
    private $php_ini = [];

    private $virtual_path_table = [];
    private $file_content_table = [];

    private $execution_stack = [];
    private $current_environment = [];

    private $custom_constants = [];

    public function __construct()
    {
        //inits the object core functions

        //set up for internal memory objects

        //set up the basic PHP constants
        return;
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
        $virtual_path = $this->normalize_name($this->init_script);
        $this->virtual_path_table[$virtual_path] = $this->init_script;
        $current_ast = $this->load_ast($script_content);

        $this->file_content_table[$virtual_path] = $current_ast;
        
        return $current_ast;
    }
    private function load_ast(string $string_php_code){
        if($this->debug) echo "Load AST\n";

        $parser = (new \PhpParser\ParserFactory())->createForNewestSupportedVersion();
        try {
            $ast = $parser->parse($string_php_code);
        } catch (\Error $error) {
            echo "Parse error: {$error->getMessage()}\n";
            return;
        }

        return $ast;
    }
    
    private function execute_ast($ast_code){
        //this function executes the ast code and returns the result
        if($this->debug) echo "Execute AST\n";

        foreach($ast_code as $element){
            $this->execute_ast_element($element);
        }

    }

    //this function executes the ast element and returns the result
    private function execute_ast_element($element)
    {
        //TODO: refactor to encapsulate each nodetype into a custom class that being called dinamically
        //      and that must be extendable by ouside user configuration to inject and modify the PHP flow
        
        if($this->debug) echo "Execute AST element: (" .get_class($element). ")\n";
        if($element instanceof \PhpParser\Node\Stmt\Expression){
            if($this->debug) echo "Expression\n";
            return $this->execute_ast_element($element->expr);
        }
        
        //PhpParser\Node\Expr\Assign
        if($element instanceof \PhpParser\Node\Expr\Assign){
            if($this->debug) echo "Assign\n";

            $name = $this->get_variable_name($element->var); //ha de ser el nom de la variable
            $expr = $this->execute_ast_element($element->expr);

            $this->set_variable_value($name, $expr);
            return true;
        }

        //AssignOp
        //PhpParser\Node\Expr\AssignOp\Concat
        if($element instanceof \PhpParser\Node\Expr\AssignOp\Concat){
            $name = $this->get_variable_name($element->var); //ha de ser el nom de la variable
            $expr = $this->execute_ast_element($element->expr);

            $left = $this->get_variable_value($name);
            $right = $expr;
            $result = $left . $right;

            if($this->debug) echo "AssignOp Concat: " . $left . " . " . $right . " = " . $result . "\n";

            $this->set_variable_value($name, $result);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\Plus
        if($element instanceof \PhpParser\Node\Expr\AssignOp\Plus){
            $name = $this->get_variable_name($element->var); //ha de ser el nom de la variable
            $expr = $this->execute_ast_element($element->expr);

            $left = $this->get_variable_value($name);
            $right = $expr;
            $result = $left + $right;

            if($this->debug) echo "AssignOp Plus: " . $left . " + " . $right . " = " . $result . "\n";

            $this->set_variable_value($name, $result);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\Minus
        if($element instanceof \PhpParser\Node\Expr\AssignOp\Minus){
            $name = $this->get_variable_name($element->var); //ha de ser el nom de la variable
            $expr = $this->execute_ast_element($element->expr);

            $left = $this->get_variable_value($name);
            $right = $expr;
            $result = $left - $right;

            if($this->debug) echo "AssignOp Minus: " . $left . " - " . $right . " = " . $result . "\n";

            $this->set_variable_value($name, $result);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\Mul
        if($element instanceof \PhpParser\Node\Expr\AssignOp\Mul){
            $name = $this->get_variable_name($element->var); //ha de ser el nom de la variable
            $expr = $this->execute_ast_element($element->expr);

            $left = $this->get_variable_value($name);
            $right = $expr;
            $result = $left * $right;

            if($this->debug) echo "AssignOp Mul: " . $left . " * " . $right . " = " . $result . "\n";

            $this->set_variable_value($name, $result);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\Div
        if($element instanceof \PhpParser\Node\Expr\AssignOp\Div){
            $name = $this->get_variable_name($element->var); //ha de ser el nom de la variable
            $expr = $this->execute_ast_element($element->expr);

            $left = $this->get_variable_value($name);
            $right = $expr;
            $result = $left / $right;

            if($this->debug) echo "AssignOp Div: " . $left . " / " . $right . " = " . $result . "\n";

            $this->set_variable_value($name, $result);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\Mod
        if($element instanceof \PhpParser\Node\Expr\AssignOp\Mod){
            $name = $this->get_variable_name($element->var); //ha de ser el nom de la variable
            $expr = $this->execute_ast_element($element->expr);

            $left = $this->get_variable_value($name);
            $right = $expr;
            $result = $left % $right;

            if($this->debug) echo "AssignOp Mod: " . $left . " % " . $right . " = " . $result . "\n";

            $this->set_variable_value($name, $result);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\BitwiseAnd
        if($element instanceof \PhpParser\Node\Expr\AssignOp\BitwiseAnd){
            $name = $this->get_variable_name($element->var); //ha de ser el nom de la variable
            $expr = $this->execute_ast_element($element->expr);

            $left = $this->get_variable_value($name);
            $right = $expr;
            $result = $left & $right;

            if($this->debug) echo "AssignOp BitwiseAnd: " . $left . " & " . $right . " = " . $result . "\n";
            //show results as binary representations
            if($this->debug) echo "AssignOp BitwiseAnd: " . decbin($left) . " & " . decbin($right) . " = " . decbin($result) . "\n";


            $this->set_variable_value($name, $result);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\BitwiseOr
        if($element instanceof \PhpParser\Node\Expr\AssignOp\BitwiseOr){
            $name = $this->get_variable_name($element->var); //ha de ser el nom de la variable
            $expr = $this->execute_ast_element($element->expr);

            $left = $this->get_variable_value($name);
            $right = $expr;
            $result = $left | $right;

            if($this->debug) echo "AssignOp BitwiseOr: " . $left . " | " . $right . " = " . $result . "\n";
            //show results as binary representations
            if($this->debug) echo "AssignOp BitwiseOr: " . decbin($left) . " | " . decbin($right) . " = " . decbin($result) . "\n";

            $this->set_variable_value($name, $result);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\BitwiseXor
        if($element instanceof \PhpParser\Node\Expr\AssignOp\BitwiseXor){
            $name = $this->get_variable_name($element->var); //ha de ser el nom de la variable
            $expr = $this->execute_ast_element($element->expr);

            $left = $this->get_variable_value($name);
            $right = $expr;
            $result = $left ^ $right;

            if($this->debug) echo "AssignOp BitwiseXor: " . $left . " ^ " . $right . " = " . $result . "\n";
            //show results as binary representations
            if($this->debug) echo "AssignOp BitwiseXor: " . decbin($left) . " ^ " . decbin($right) . " = " . decbin($result) . "\n";

            $this->set_variable_value($name, $result);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\ShiftLeft
        if($element instanceof \PhpParser\Node\Expr\AssignOp\ShiftLeft){
            $name = $this->get_variable_name($element->var); //ha de ser el nom de la variable
            $expr = $this->execute_ast_element($element->expr);

            $left = $this->get_variable_value($name);
            $right = $expr;
            $result = $left << $right;

            if($this->debug) echo "AssignOp ShiftLeft: " . $left . " << " . $right . " = " . $result . "\n";
            //show results as binary representations
            if($this->debug) echo "AssignOp ShiftLeft: " . decbin($left) . " << " . decbin($right) . " = " . decbin($result) . "\n";

            $this->set_variable_value($name, $result);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\ShiftRight
        if($element instanceof \PhpParser\Node\Expr\AssignOp\ShiftRight){
            $name = $this->get_variable_name($element->var); //ha de ser el nom de la variable
            $expr = $this->execute_ast_element($element->expr);

            $left = $this->get_variable_value($name);
            $right = $expr;
            $result = $left >> $right;

            if($this->debug) echo "AssignOp ShiftRight: " . $left . " >> " . $right . " = " . $result . "\n";
            //show results as binary representations
            if($this->debug) echo "AssignOp ShiftRight: " . decbin($left) . " >> " . decbin($right) . " = " . decbin($result) . "\n";

            $this->set_variable_value($name, $result);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\Coalesce
        if($element instanceof \PhpParser\Node\Expr\AssignOp\Coalesce){
            if($this->debug) echo "AssignOp Coalesce:\n";
            $name = $this->get_variable_name($element->var);
            $expr = $this->execute_ast_element($element->expr);
            $left = null;
            if(!$this->exists_variable_name($element->var)){   
                if($this->debug) echo "Variable is null, then we set to:" . $expr . "\n";             
                $this->set_variable_value($name, $expr);
            }else{
                $left = $this->get_variable_value($name);
            }

            $right = $expr;
            $left ??= $right;

            if($this->debug) echo "AssignOp Coalesce: " . $name . " ??= " . $right . "\n";

            return true;
        }

        //Scalar types
        //PhpParser\Node\Scalar\String_
        if($element instanceof \PhpParser\Node\Scalar\String_){
            if($this->debug) echo "String\n";
            if($element->value == "END_EXECUTOR"){ die("END_EXECUTOR"); }

            return $element->value;
        }
        //PhpParser\Node\Scalar\Int_
        if($element instanceof \PhpParser\Node\Scalar\Int_){
            if($this->debug) echo "Integer\n";
            return $element->value;
        }
        //PhpParser\Node\Scalar\Float_
        if($element instanceof \PhpParser\Node\Scalar\Float_){
            if($this->debug) echo "Float\n";
            return $element->value;
        }
        //PhpParser\Node\Scalar\InterpolatedString
        if($element instanceof \PhpParser\Node\Scalar\InterpolatedString){
            if($this->debug) echo "InterpolatedString\n";
            $result = '';
            foreach($element->parts as $part){
                $result .= $this->execute_ast_element($part);
            }
            return $result;
        }
        //PhpParser\Node\Scalar\EncapsedStringPart
        

        //PhpParser\Node\Expr\Cast\Int_
        if($element instanceof \PhpParser\Node\Expr\Cast\Int_){
            $value = $this->execute_ast_element($element->expr);
            $result = (int)$value;

            if($this->debug) echo "Cast Int: " . var_export($value, true) . " = " . var_export($result, true) . "\n";
            
            return $result;
        }
        //PhpParser\Node\Expr\Cast\Double
        if($element instanceof \PhpParser\Node\Expr\Cast\Double){
            $value = $this->execute_ast_element($element->expr);
            $result = (float)$value;

            if($this->debug) echo "Cast Double: " . var_export($value, true) . " = " . var_export($result, true) . "\n";
            
            return $result;
        }
        //PhpParser\Node\Expr\Cast\String_
        if($element instanceof \PhpParser\Node\Expr\Cast\String_){
            $value = $this->execute_ast_element($element->expr);
            $result = (string)$value;

            if($this->debug) echo "Cast String: " . var_export($value, true) . " = " . var_export($result, true) . "\n";
            
            return $result;
        }
        //PhpParser\Node\Expr\Cast\Bool_
        if($element instanceof \PhpParser\Node\Expr\Cast\Bool_){
            $value = $this->execute_ast_element($element->expr);
            $result = (bool)$value;

            if($this->debug) echo "Cast Bool: " . var_export($value, true) . " = " . var_export($result, true) . "\n";
            
            return $result;
        }



        //\PhpParser\Node\Stmt\Echo_
        if($element instanceof \PhpParser\Node\Stmt\Echo_){
            if($this->debug) echo "Echo\n";

            $exprs = $element->exprs[0]; //can it contain more than one expression?
            $result = $this->execute_ast_element($exprs);
            if($this->debug) echo "echo: ";
            echo $result;
            if($this->debug) echo "\n";
            return;
        }
        //PhpParser\Node\Stmt\Nop
        if($element instanceof \PhpParser\Node\Stmt\Nop){
            if($this->debug) echo "Nop\n";
            throw new \Exception("Nop detected on code execution!");
            return;
        }


        //BinaryOp
        //PhpParser\Node\Expr\BinaryOp\Concat
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\Concat){
            if($this->debug) echo "Concat\n";
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            return $left . $right;
        }
        //PhpParser\Node\Expr\BinaryOp\Plus
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\Plus){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "Sum: " . $left . " + " . $right . " = " . ($left + $right) . "\n";
            return $left + $right;
        }
        //PhpParser\Node\Expr\BinaryOp\Minus
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\Minus){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "Substract: " . $left . " - " . $right . " = " . ($left - $right) . "\n";
            return $left - $right;
        }
        //PhpParser\Node\Expr\BinaryOp\Mul
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\Mul){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "Multiply: " . $left . " * " . $right . " = " . ($left * $right) . "\n";
            return $left * $right;
        }
        //PhpParser\Node\Expr\BinaryOp\Div
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\Div){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "Divide: " . $left . " / " . $right . " = " . ($left / $right) . "\n";
            return $left / $right;
        }
        //PhpParser\Node\Expr\BinaryOp\ShiftLeft
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\ShiftLeft){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "ShiftLeft: " . decbin($left) . " << " . decbin($right) . " = " . decbin($left << $right) . "\n";
            return $left << $right;
        }
        //PhpParser\Node\Expr\BinaryOp\ShiftRight
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\ShiftRight){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "ShiftRight: " . decbin($left) . " >> " . decbin($right) . " = " . decbin($left >> $right) . "\n";
            return $left >> $right;
        }


        //PhpParser\Node\Expr\AssignRef
        if($element instanceof \PhpParser\Node\Expr\AssignRef){
            if($this->debug) echo "AssignRef\n";
            //TODO
            die("TODO AssignRef");
            return $this->execute_ast_element($element->expr);
        }
        //PhpParser\Node\Expr\Variable
        if($element instanceof \PhpParser\Node\Expr\Variable){
            if($this->debug) echo "Variable\n";
            return $this->get_variable_value($element->name);
        }
        //PhpParser\Node\Expr\ConstFetch
        if($element instanceof \PhpParser\Node\Expr\ConstFetch){
            if($this->debug) echo "ConstFetch\n";
            return $this->execute_ast_element($element->name);
        }
        //PhpParser\Node\Expr\PostInc
        if($element instanceof \PhpParser\Node\Expr\PostInc){
            if($this->debug) echo "PostInc (var++)\n";
            $var = $this->get_variable_name($element->var);
            $value = $this->get_variable_value($var);
            $this->set_variable_value($var, $value + 1);
            return $value;
        }
        //PhpParser\Node\Expr\PostDec
        if($element instanceof \PhpParser\Node\Expr\PostDec){
            if($this->debug) echo "PostDec (var--)\n";
            $var = $this->get_variable_name($element->var);
            $value = $this->get_variable_value($var);
            $this->set_variable_value($var, $value - 1);
            return $value;
        }
        //PhpParser\Node\Expr\FuncCall
        if($element instanceof \PhpParser\Node\Expr\FuncCall){
            if($this->debug) echo "FuncCall\n";
            //TODO
            var_export($element);
            die("TODO FuncCall");
            return;
        }

        //PhpParser\Node\Name
        if($element instanceof \PhpParser\Node\Name){
            if($this->debug) echo "Name (constant)\n";
            return $this->get_constant($element->name);
        }
        //PhpParser\Node\InterpolatedStringPart
        if($element instanceof \PhpParser\Node\Scalar\EncapsedStringPart){
            if($this->debug) echo "InterpolatedStringPart\n";
            return $element->value;
        }




        throw new \Exception("Element not recognized:" . get_class($element) . "\n". var_export($element, true) . "\n");
        die();

    }
    private function get_variable_name($var){
        //TODO code this to the right namespace / class object / function environment
        $name=$var->name;
        if(!is_string($name)) $name = $this->execute_ast_element($name); 
        return $name;
    }
    private function exists_variable_name($var){
        //TODO code this to the right namespace / class object / function environment
        $name=$var->name;
        if(!is_string($name)) $name = $this->execute_ast_element($name); 
        return isset($this->execution_stack[$name]);
    }
    private function get_variable_value($name){
        //TODO code this to the right namespace / class object / function environment
        if(!is_string($name)) $name = $this->execute_ast_element($name);
        return $this->execution_stack[$name];
    }
    private function set_variable_value($var, $expr){
        //TODO code this to the right namespace / class object / function environment
        if($this->debug) echo "Set variable: " . var_export($var, true) . " = " . var_export($expr, true) . "\n";
        $this->execution_stack[$var] = $expr;
    }

    private function get_constant($name){
        if(!isset($this->custom_constants[$name])){
            if(defined($name)){
                return constant($name);
            }
            return null;
        }
        return $this->custom_constants[$name];
    }
    public function set_custom_constants($custom_constants){
        $this->custom_constants = $custom_constants;
        return true;
    }

    private function normalize_name($path){
        $path = str_replace(array_keys($this->path_replace), array_values($this->path_replace), $path);
        return $path;
    }
    public function setup_execution($config)
    {
        if($this->debug) echo "Setup execution\n";

        $this->set_php_ini($config['php_ini']);
        $this->set_replace_path($config['path_replace']);
        $this->set_init_script($config['init_script']);
        
        return;
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
}
