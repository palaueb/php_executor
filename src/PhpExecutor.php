<?php
// YES I KNOW: Go and use xdebug, this is my headache.

// TODO: Different levels of debug, and different outputs
// all class, namespace, function, etc. declarations and execution, levels of execution.


/*
 things to know:
 - The use of "," (comma) inside loops, if statements, etc. is to separate expressions, and they are executed in order, but only the last one is returned. For example: for($i=0;$i<10,$i<20;$i++){} $i will be 20 at the end of the loop. It means that is not the same echo("Hello world"), than echo "Hello"," ","world"; 
*/
namespace Palaueb\PhpExecutor;

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

    public function __construct()
    {
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
    private function load_ast(string $string_php_code) 
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
    
    private function execute_ast($ast_code)
    {
        //this function executes an ast code list, usually a stmt and returns the result
        if($this->debug) echo "Execute AST\n";

        foreach($ast_code as $element){
            $last = $this->execute_ast_element($element);
        }
        return $last;
    }

    //this function executes the ast element and returns the result
    private function execute_ast_element($element)
    {
        //TODO: refactor to encapsulate each nodetype into a custom class that being called dinamically
        //      and that must be extendable by ouside user configuration to inject and modify the PHP flow
        
        if($this->debug) echo "Execute AST element: (" .get_class($element). ")\n";
        
        //stmts
        if($element instanceof \PhpParser\Node\Stmt\Expression){
            if($this->debug) echo "Expression\n";
            return $this->execute_ast_element($element->expr);
        }
        //\PhpParser\Node\Stmt\Echo_
        if($element instanceof \PhpParser\Node\Stmt\Echo_){
            if($this->debug) echo "Echo\n";
            //exprs is an array of expressions that needs to be concatenaded to be printed
            $result = '';
            foreach($element->exprs as $expr){
                $result .= $this->execute_ast_element($expr);
            }

            if($this->debug) echo "echo: ";
            echo $result;
            if($this->debug) echo "\n";
            return true;
        }
        //PhpParser\Node\Stmt\Function_
        if($element instanceof \PhpParser\Node\Stmt\Function_){
            if($this->debug) echo "Function: " . $element->name->name . PHP_EOL;
            $this->create_function($element);
            //$this->current_environment['functions'][$name] = $element;
            return true;
        }
        //PhpParser\Node\Stmt\If_
        if($element instanceof \PhpParser\Node\Stmt\If_){
            if($this->debug) echo "If\n";
            $cond = $this->execute_ast_element($element->cond);
            if($cond){
                $this->execute_ast($element->stmts);
            }else{
                if(isset($element->elseifs)){
                    foreach($element->elseifs as $elseif){
                        $cond = $this->execute_ast_element($elseif->cond);
                        if($cond){
                            $this->execute_ast($elseif->stmts);
                            return true;
                        }
                    }
                }
                if(isset($element->else)){
                    $this->execute_ast($element->else->stmts);
                }
            }
            return true;
        }
        //PhpParser\Node\Stmt\For_
        if($element instanceof \PhpParser\Node\Stmt\For_){
            if($this->debug) echo "Processing For loop\n";

            $this->execute_for($element);

            return true;
        }
        //PhpParser\Node\Stmt\Break_
        if($element instanceof \PhpParser\Node\Stmt\Break_){
            if($this->debug) echo "Break\n";
            $break_num = $element->num;
            return ['break' => $break_num];
        }
        //PhpParser\Node\Stmt\Nop
        if($element instanceof \PhpParser\Node\Stmt\Nop){
            if($this->debug) echo "Nop\n";
            //throw new \Exception("Nop detected on code execution!");
            return true;
        }


        
        //PhpParser\Node\Expr\Assign
        if($element instanceof \PhpParser\Node\Expr\Assign){
            if($this->debug) echo "Assign\n";

            $name = $this->get_variable_name($element->var); //ha de ser el nom de la variable
            $expr = $this->execute_ast_element($element->expr);

            $this->set_variable_value($name, $expr);
            return true;
        }
        //PhpParser\Node\Expr\BooleanNot
        if($element instanceof \PhpParser\Node\Expr\BooleanNot){
            $expr = $this->execute_ast_element($element->expr);
            if($this->debug) echo "BooleanNot: ".var_export(!$expr, true)."\n";
            return !$expr;
        }
        //PhpParser\Node\Expr\Array_
        if($element instanceof \PhpParser\Node\Expr\Array_){
            if($this->debug) echo "Array\n";
            $result = [];
            foreach($element->items as $item){
                $value = $this->execute_ast_element($item->value);
                if(is_null($item->key)){
                    $result[] = $value;
                    continue;
                }
                $key = $this->execute_ast_element($item->key);
                $result[$key] = $value;
            }
            return $result;
        }
        //PhpParser\Node\Expr\Exit_
        if($element instanceof \PhpParser\Node\Expr\Exit_){
            if($this->debug) echo "Exit\n";
            
            $value = $this->execute_ast_element($element->expr);
            echo $value . "\n";
            
            $this->halt_execution();
        }
        //PhpParser\Node\Expr\FuncCall
        if($element instanceof \PhpParser\Node\Expr\FuncCall){b
            $name = $this->execute_ast_element($element->name);
            if($this->debug) echo "FuncCall [$name]\n";
            $args = [];
            foreach($element->args as $arg){
                $args[] = $this->execute_ast_element($arg);
            }
            //TODO: we need to have our own functions proxy to call native functions or user defined functions
            $result = call_user_func_array($name, $args);
            return $result;
        }
        //PhpParser\Node\Arg
        if($element instanceof \PhpParser\Node\Arg){
            if($this->debug) echo "Arg\n";
            return $this->execute_ast_element($element->value);
        }

        //AssignOp
        //PhpParser\Node\Expr\AssignOp\Pow
        if($element instanceof \PhpParser\Node\Expr\AssignOp\Pow){
            $name = $this->get_variable_name($element->var); //ha de ser el nom de la variable
            $expr = $this->execute_ast_element($element->expr);

            $left = $this->get_variable_value($name);
            $right = $expr;
            $result = $left ** $right;

            if($this->debug) echo "AssignOp Pow: " . $left . " ** " . $right . " = " . $result . "\n";

            $this->set_variable_value($name, $result);
            return true;
        }
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
        //PhpParser\Node\Expr\Cast\Array_
        if($element instanceof \PhpParser\Node\Expr\Cast\Array_){
            $value = $this->execute_ast_element($element->expr);
            $result = (array)$value;

            if($this->debug) echo "Cast Array: " . var_export($value, true) . " = " . var_export($result, true) . "\n";
            
            return $result;
        }
        /*
        //PhpParser\Node\Expr\Cast\Unset_
        if($element instanceof \PhpParser\Node\Expr\Cast\Unset_){
            $value = $this->execute_ast_element($element->expr);
            $result = (unset)$value;

            if($this->debug) echo "Cast Unset: " . var_export($value, true) . " = " . var_export($result, true) . "\n";
            
            return $result;
        }
        */


        


        //BinaryOp
        //PhpParser\Node\Expr\BinaryOp\Pow
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\Pow){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            $result = $left ** $right;

            if($this->debug) echo "Pow: " . $left . " ** " . $right . " = " . $result . "\n";
            return $result;
        }
        //PhpParser\Node\Expr\BinaryOp\Coalesce
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\Coalesce){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "Coalesce: " . var_export($left, true) . " ?? " . var_export($right, true) . " = " . var_export($left ?? $right, true) . "\n";
            return $left ?? $right;
        }
        //PhpParser\Node\Expr\BinaryOp\BooleanOr
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "BooleanOr: " . var_export($left, true) . " || " . var_export($right, true) . " = " . var_export($left || $right, true) . "\n";
            return $left || $right;
        }
        //PhpParser\Node\Expr\BinaryOp\BooleanAnd
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "BooleanAnd: " . var_export($left, true) . " && " . var_export($right, true) . " = " . var_export($left && $right, true) . "\n";
            return $left && $right;
        }
        //PhpParser\Node\Expr\BinaryOp\Equal
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\Equal){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "Equal: " . var_export($left, true) . " == " . var_export($right, true) . " = " . var_export($left == $right, true) . "\n";
            return $left == $right;
        }
        //PhpParser\Node\Expr\BinaryOp\NotEqual
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\NotEqual){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "NotEqual: " . var_export($left, true) . " != " . var_export($right, true) . " = " . var_export($left != $right, true) . "\n";
            return $left != $right;
        }
        //PhpParser\Node\Expr\BinaryOp\Identical
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\Identical){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "Identical: " . var_export($left, true) . " === " . var_export($right, true) . " = " . var_export($left === $right, true) . "\n";
            return $left === $right;
        }
        //PhpParser\Node\Expr\BinaryOp\NotIdentical
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\NotIdentical){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "NotIdentical: " . var_export($left, true) . " !== " . var_export($right, true) . " = " . var_export($left !== $right, true) . "\n";
            return $left !== $right;
        }
        //PhpParser\Node\Expr\BinaryOp\SmallerOrEqual
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\SmallerOrEqual){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "SmallerOrEqual: " . $left . " <= " . $right . " = " . var_export($left <= $right, true) . "\n";
            return $left <= $right;
        }
        //PhpParser\Node\Expr\BinaryOp\GreaterOrEqual
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\GreaterOrEqual){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "GreaterOrEqual: " . $left . " >= " . $right . " = " . var_export($left >= $right, true) . "\n";
            return $left >= $right;
        }
        //PhpParser\Node\Expr\BinaryOp\Spaceship
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\Spaceship){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "Spaceship: " . $left . " <=> " . $right . " = " . var_export($left <=> $right, true) . "\n";
            return $left <=> $right;
        }
        //PhpParser\Node\Expr\BinaryOp\Smaller
        if($element instanceof \PhpParser\Node\Expr\BinaryOp\Smaller){
            $left = $this->execute_ast_element($element->left);
            $right = $this->execute_ast_element($element->right);
            if($this->debug) echo "Smaller: " . $left . " < " . $right . " = " . var_export($left < $right, true) . "\n";
            return $left < $right;
        }
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
            $constant_name = $this->execute_ast_element($element->name);
            if($this->debug) echo "ConstFetch: [".$constant_name."]\n";
            return $this->get_constant($constant_name);
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
        //PhpParser\Node\ArrayItem
        if($element instanceof \PhpParser\Node\ArrayItem){
            if($this->debug) echo "ArrayItem\n";
            $key = null;
            $value = $this->execute_ast_element($element->value);
            if(!is_null($element->key)){
                $key = $this->execute_ast_element($element->key);
                return [$key => $value];
            }
            return [$value];
            
        }
        //PhpParser\Node\Name
        if($element instanceof \PhpParser\Node\Name){
            if(!is_string($element->name)) { throw new \Exception("Name is not a string: " . var_export($element->name, true)); }
            if($this->debug) echo "Name (" . $element->name . ")\n";

            return $element->name;
        }
        //PhpParser\Node\InterpolatedStringPart
        if($element instanceof \PhpParser\Node\Scalar\EncapsedStringPart){
            if($this->debug) echo "InterpolatedStringPart\n";
            return $element->value;
        }
        
        if(is_null($element)) {
            echo "NULL DETECTED ON ELEMENT!!!!!\n";
            die();
        }
        echo "ELEMENT NOT IN LIST:" . get_class($element) . "\n";
        
        throw new \Exception("Element not recognized:" . get_class($element) . "\n". var_export($element, true) . "\n");
        die();

    }

    private function execute_for($element)
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

    private function create_function($element)
    {
        //TODO Needed to implement the right environment for functions/classes/namespaces/objects/etc...
        $name = $this->execute_ast_element($element->name);
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
    private function get_variable_name($var)
    {
        //TODO code this to the right namespace / class object / function environment
        $name=$var->name;
        if(!is_string($name)) $name = $this->execute_ast_element($name); 
        return $name;
    }
    private function exists_variable_name($var)
    {
        //TODO code this to the right namespace / class object / function environment
        $name=$var->name;
        if(!is_string($name)) $name = $this->execute_ast_element($name); 
        return isset($this->execution_stack[$name]);
    }
    private function get_variable_value($name)
    {
        //TODO code this to the right namespace / class object / function environment
        if(!is_string($name)) $name = $this->execute_ast_element($name);
        return $this->execution_stack[$name];
    }
    private function set_variable_value($var, $expr)
    {
        //TODO code this to the right namespace / class object / function environment
        if($this->debug) echo "Set variable: " . var_export($var, true) . " = " . var_export($expr, true) . "\n";
        $this->execution_stack[$var] = $expr;
    }

    private function get_constant($name)
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
    public function set_custom_constants($custom_constants)
    {
        $this->custom_constants = $custom_constants;
        return true;
    }

    private function normalize_path_name($path)
    {
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


    private function halt_execution()
    {
        if($this->debug) echo "Halt execution\n";

        //TODO: implement an extendable way to process data after the end of the execution.

        die();
    }
}
