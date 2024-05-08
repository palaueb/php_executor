<?php

namespace PhpExecutor;

Class Node {
    //PhpParser\Node\Arg
    //if($element instanceof \PhpParser\Node\Arg) {
    public static function Arg($executor, $element, $context = null) {
        $executor->echo("Arg", 'B');
        return $executor->execute_ast_element($element->value);
    }
    //PhpParser\Node\Identifier
    //if($element instanceof \PhpParser\Node\Identifier) {
    public static function Identifier($executor, $element, $context = null) {
        $executor->echo("Identifier: [" . $element->name . "]", 'B');

        return $element->name;
    }
    //PhpParser\Node\ArrayItem
    //if($element instanceof \PhpParser\Node\ArrayItem) {
    public static function ArrayItem($executor, $element, $context = null) {
        $executor->echo("ArrayItem", 'B');

        $key = null;
        $value = $executor->execute_ast_element($element->value);
        if(!is_null($element->key)) {
            $key = $executor->execute_ast_element($element->key);
            return [$key => $value];
        }
        return [$value];
        
    }
    //PhpParser\Node\Name
    //if($element instanceof \PhpParser\Node\Name) {
    public static function Name($executor, $element, $context = null) {
        if(!is_string($element->name)) { throw new \Exception("Name is not a string: " . var_export($element->name, true)); }
        $executor->echo("Name: [" . $element->name . "]", 'B');

        return $element->name;
    }
    //PhpParser\Node\InterpolatedStringPart
    //if($element instanceof \PhpParser\Node\InterpolatedStringPart) {
    public static function InterpolatedStringPart($executor, $element, $context = null) {
        $executor->echo("InterpolatedStringPart: [" . $element->value . "]", 'B');
        return $element->value;
    }
}