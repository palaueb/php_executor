<?php

namespace PhpExecutor\Node;

Class Expr {
    //PhpParser\Node\Expr\Assign
    public static function Assign($executor, $element, $context = null) {
        if($executor->debug) echo "Assign\n";

        $name = $executor->get_variable_name($element->var, $context); //ha de ser el nom de la variable
        $expr = $executor->execute_ast_element($element->expr, $context);

        $executor->set_variable_value($name, $expr, $context);
        return true;
    }
    //PhpParser\Node\Expr\BooleanNot
    public static function BooleanNot($executor, $element, $context = null) {
        $expr = $executor->execute_ast_element($element->expr, $context);
        if($executor->debug) echo "BooleanNot: ".var_export(!$expr, true)."\n";
        return !$expr;
    }
    //PhpParser\Node\Expr\Array_
    public static function Array_($executor, $element, $context = null) {
        if($executor->debug) echo "Array\n";
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
        return $result;
    }
    //PhpParser\Node\Expr\Exit_
    public static function Exit_($executor, $element, $context = null) {
        $value = "PHPEXECUTOR: NULL VALUE";
        if(!is_null($element->expr)){
            $value = $executor->execute_ast_element($element->expr, $context);
            echo $value . "\n";
        }
        if($executor->debug) echo "Exit [$value]\n";
        return $executor->halt_execution($value);
    }
    //PhpParser\Node\Expr\FuncCall
    public static function FuncCall($executor, $element, $context = null) {
        $name = $executor->execute_ast_element($element->name);
        if($executor->debug) echo "FuncCall [$name]\n";
        $args = [];
        foreach($element->args as $arg) {
            $args[] = $executor->execute_ast_element($arg, $context);
        }
        //TODO: we need to have our own functions proxy to call native functions or user defined functions
        $result = call_user_func_array($name, $args);
        return $result;
    }
    //PhpParser\Node\Expr\AssignRef
    //if($element instanceof \PhpParser\Node\Expr\AssignRef) {
    public static function AssignRef($executor, $element, $context = null) {
        if($executor->debug) echo "AssignRef\n";
        //TODO
        die("TODO AssignRef");
        return $executor->execute_ast_element($element->expr, $context);
    }
    //PhpParser\Node\Expr\Variable
    //if($element instanceof \PhpParser\Node\Expr\Variable) {
    public static function Variable($executor, $element, $context = null) {
        if($executor->debug) echo "Variable\n";
        return $executor->get_variable_value($element->name, $context);
    }
    //PhpParser\Node\Expr\ConstFetch
    //if($element instanceof \PhpParser\Node\Expr\ConstFetch) {
    public static function ConstFetch($executor, $element, $context = null) {
        $constant_name = $executor->execute_ast_element($element->name, $context);
        if($executor->debug) echo "ConstFetch: [".$constant_name."]\n";
        return $executor->get_constant($constant_name, $context);
    }
    //PhpParser\Node\Expr\PostInc
    //if($element instanceof \PhpParser\Node\Expr\PostInc) {
    public static function PostInc($executor, $element, $context = null) {
        if($executor->debug) echo "PostInc (var++)\n";
        $var = $executor->get_variable_name($element->var, $context);
        $value = $executor->get_variable_value($var, $context);
        $executor->set_variable_value($var, $value + 1, $context);
        return $value;
    }
    //PhpParser\Node\Expr\PostDec
    //if($element instanceof \PhpParser\Node\Expr\PostDec) {
    public static function PostDec($executor, $element, $context = null) {
        if($executor->debug) echo "PostDec (var--)\n";
        $var = $executor->get_variable_name($element->var, $context);
        $value = $executor->get_variable_value($var, $context);
        $executor->set_variable_value($var, $value - 1, $context);
        return $value;
    }
    //PhpParser\Node\Expr\Include_
    //if($element instanceof \PhpParser\Node\Expr\Include_) {
    public static function Include_($executor, $element, $context = null) {
        if($executor->debug) echo "Include\n";
        $type = $element->type; //1: include, 2: include_once, 3: require, 4: require_once, 
        $file = $executor->execute_ast_element($element->expr, $context);
        return $executor->include_file($file, $type, $context);
    }
}