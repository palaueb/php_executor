<?php

namespace PhpExecutor\Node;

Class Expr {
    //PhpParser\Node\Expr\Assign
    public static function Assign($executor, $element, $context = null) {
        $name = $executor->get_variable_name($element->var, $context); //ha de ser el nom de la variable
        $expr = $executor->execute_ast_element($element->expr, $context);

        $executor->echo("Assign: [$name] = [$expr]", 'B');

        $executor->set_variable_value($name, $expr, $context);
        return true;
    }
    //PhpParser\Node\Expr\BooleanNot
    public static function BooleanNot($executor, $element, $context = null) {
        $expr = $executor->execute_ast_element($element->expr, $context);
        $executor->echo("BooleanNot: ".var_export(!$expr, true), 'B');
        return !$expr;
    }
    //PhpParser\Node\Expr\Array_
    public static function Array_($executor, $element, $context = null) {
        $executor->echo("Array", 'B');
        $result = [];
        foreach($element->items as $item) {
            $value = $executor->execute_ast_element($item->value, $context);
            if(is_null($item->key)) {
                $result[] = $value;
                continue;
            }
            $key = $executor->execute_ast_element($item->key, $context);
            $result[$key] = $value;
        }
        //$executor->echo("Array: ".var_export($result, true), 'B');
        return $result;
    }
    //PhpParser\Node\Expr\Exit_
    public static function Exit_($executor, $element, $context = null) {
        $value = "PHPEXECUTOR: NULL VALUE";
        if(!is_null($element->expr)){
            $value = $executor->execute_ast_element($element->expr, $context);
            echo $value . "\n";
        }
        $executor->echo("Exit [$value]", 'B');
        return $executor->halt_execution($value);
    }
    //PhpParser\Node\Expr\FuncCall
    public static function FuncCall($executor, $element, $context = null) {
        $name = $executor->execute_ast_element($element->name);
        $executor->echo("FuncCall [$name]", 'B');

        $result = $executor->execute_function($element, $context);
        
        return $result;
    }
    //PhpParser\Node\Expr\AssignRef
    public static function AssignRef($executor, $element, $context = null) {
        $executor->echo("AssignRef", 'B');
        $executor->echo("TODO AssignRef", 'R');
        //TODO
        die("TODO AssignRef");
        return $executor->execute_ast_element($element->expr, $context);
    }
    //PhpParser\Node\Expr\Variable
    public static function Variable($executor, $element, $context = null) {
        $executor->echo("Variable " , 'B');
        return $executor->get_variable_value($element->name, $context);
    }
    //PhpParser\Node\Expr\ConstFetch
    public static function ConstFetch($executor, $element, $context = null) {
        $constant_name = $executor->execute_ast_element($element->name, $context);
        $executor->echo("ConstFetch: [".$constant_name."]", 'B');
        return $executor->get_constant($constant_name, $context);
    }
    //PhpParser\Node\Expr\PostInc
    public static function PostInc($executor, $element, $context = null) {
        $executor->echo("PostInc (var++)", 'B');

        $var = $executor->get_variable_name($element->var, $context);
        $value = $executor->get_variable_value($var, $context);
        $executor->set_variable_value($var, $value + 1, $context);
        return $value;
    }
    //PhpParser\Node\Expr\PostDec
    public static function PostDec($executor, $element, $context = null) {
        $executor->echo("PostDec (var--)", 'B');

        $var = $executor->get_variable_name($element->var, $context);
        $value = $executor->get_variable_value($var, $context);
        $executor->set_variable_value($var, $value - 1, $context);
        return $value;
    }
    //PhpParser\Node\Expr\Include_
    //if($element instanceof \PhpParser\Node\Expr\Include_) {
    public static function Include_($executor, $element, $context = null) {
        $type = $element->type; //1: include, 2: include_once, 3: require, 4: require_once, 
        $file = $executor->execute_ast_element($element->expr, $context);

        $executor->echo("Include: [$file]($type)", 'B');
        return $executor->include_file($file, $type, $context);
    }
    //PhpParser\Node\Expr\Isset_
    //if($element instanceof \PhpParser\Node\Expr\Isset_) {
    public static function Isset_($executor, $element, $context = null) {
        $result = true;
        foreach($element->vars as $var) {
            //$name = $executor->get_variable_name($var, $context);
            $current = $executor->exists_variable_name($var, $context);
            if($current === false) {
                return false;
            }
        }
        $executor->echo("Isset [$result]", 'B');
        return true;
    }
}