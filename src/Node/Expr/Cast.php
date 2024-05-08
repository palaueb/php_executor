<?php

namespace PhpExecutor\Node\Expr;

Class Cast {
        //PhpParser\Node\Expr\Cast\Int_
        //if($element instanceof \PhpParser\Node\Expr\Cast\Int_) {
        public static function Int_($executor, $element, $context = null) {
            $value = $executor->execute_ast_element($element->expr, $context);
            $result = (int)$value;

            $executor->echo("Cast Int: " . var_export($value, true) . " = " . var_export($result, true), 'B');
            
            return $result;
        }
        //PhpParser\Node\Expr\Cast\Double
        //if($element instanceof \PhpParser\Node\Expr\Cast\Double) {
        public static function Double($executor, $element, $context = null) {
            $value = $executor->execute_ast_element($element->expr, $context);
            $result = (float)$value;

            $executor->echo("Cast Double: " . var_export($value, true) . " = " . var_export($result, true), 'B');
            
            return $result;
        }
        //PhpParser\Node\Expr\Cast\String_
        //if($element instanceof \PhpParser\Node\Expr\Cast\String_) {
        public static function String_($executor, $element, $context = null) {
            $value = $executor->execute_ast_element($element->expr, $context);
            $result = (string)$value;

            $executor->echo("Cast String: " . var_export($value, true) . " = " . var_export($result, true), 'B');
            
            return $result;
        }
        //PhpParser\Node\Expr\Cast\Bool_
        //if($element instanceof \PhpParser\Node\Expr\Cast\Bool_) {
        public static function Bool_($executor, $element, $context = null) {
            $value = $executor->execute_ast_element($element->expr, $context);
            $result = (bool)$value;

            $executor->echo("Cast Bool: " . var_export($value, true) . " = " . var_export($result, true), 'B');
            
            return $result;
        }
        //PhpParser\Node\Expr\Cast\Array_
        //if($element instanceof \PhpParser\Node\Expr\Cast\Array_) {
        public static function Array_($executor, $element, $context = null) {
            $value = $executor->execute_ast_element($element->expr, $context);
            $result = (array)$value;

            $executor->echo("Cast Array: " . var_export($value, true) . " = " . var_export($result, true), 'B');
            
            return $result;
        }
        /*
        //PhpParser\Node\Expr\Cast\Unset_
        //if($element instanceof \PhpParser\Node\Expr\Cast\Unset_) {
        public static function Unset_($executor, $element, $context = null) {
            $value = $executor->execute_ast_element($element->expr);
            $result = (unset)$value;

            $executor->echo("Cast Unset: " . var_export($value, true) . " = " . var_export($result, true), 'B');
            
            return $result;
        }
        */
}