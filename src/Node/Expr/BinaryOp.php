<?php

namespace PhpExecutor\Node\Expr;

Class BinaryOp {
//BinaryOp
    //PhpParser\Node\Expr\BinaryOp\Pow
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\Pow) {
    public static function Pow($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        $result = $left ** $right;

        $executor->echo("Pow: " . $left . " ** " . $right . " = " . $result, 'B');
        return $result;
    }
    //PhpParser\Node\Expr\BinaryOp\Coalesce
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\Coalesce) {
    public static function Coalesce($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        if($executor->debug) echo "Coalesce: " . var_export($left, true) . " ?? " . var_export($right, true) . " = " . var_export($left ?? $right, true) . "\n";
        return $left ?? $right;
    }
    //PhpParser\Node\Expr\BinaryOp\BooleanOr
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
    public static function BooleanOr($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        if($executor->debug) echo "BooleanOr: " . var_export($left, true) . " || " . var_export($right, true) . " = " . var_export($left || $right, true) . "\n";
        return $left || $right;
    }
    //PhpParser\Node\Expr\BinaryOp\BooleanAnd
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd) {
    public static function BooleanAnd($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        if($executor->debug) echo "BooleanAnd: " . var_export($left, true) . " && " . var_export($right, true) . " = " . var_export($left && $right, true) . "\n";
        return $left && $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Equal
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\Equal) {
    public static function Equal($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        if($executor->debug) echo "Equal: " . var_export($left, true) . " == " . var_export($right, true) . " = " . var_export($left == $right, true) . "\n";
        return $left == $right;
    }
    //PhpParser\Node\Expr\BinaryOp\NotEqual
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\NotEqual) {
    public static function NotEqual($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        if($executor->debug) echo "NotEqual: " . var_export($left, true) . " != " . var_export($right, true) . " = " . var_export($left != $right, true) . "\n";
        return $left != $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Identical
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
    public static function Identical($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        if($executor->debug) echo "Identical: " . var_export($left, true) . " === " . var_export($right, true) . " = " . var_export($left === $right, true) . "\n";
        return $left === $right;
    }
    //PhpParser\Node\Expr\BinaryOp\NotIdentical
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\NotIdentical) {
    public static function NotIdentical($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left);
        $right = $executor->execute_ast_element($element->right);
        if($executor->debug) echo "NotIdentical: " . var_export($left, true) . " !== " . var_export($right, true) . " = " . var_export($left !== $right, true) . "\n";
        return $left !== $right;
    }
    //PhpParser\Node\Expr\BinaryOp\SmallerOrEqual
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\SmallerOrEqual) {
    public static function SmallerOrEqual($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        if($executor->debug) echo "SmallerOrEqual: " . $left . " <= " . $right . " = " . var_export($left <= $right, true) . "\n";
        return $left <= $right;
    }
    //PhpParser\Node\Expr\BinaryOp\GreaterOrEqual
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\GreaterOrEqual) {
    public static function GreaterOrEqual($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        if($executor->debug) echo "GreaterOrEqual: " . $left . " >= " . $right . " = " . var_export($left >= $right, true) . "\n";
        return $left >= $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Spaceship
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\Spaceship) {
    public static function Spaceship($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        if($executor->debug) echo "Spaceship: " . $left . " <=> " . $right . " = " . var_export($left <=> $right, true) . "\n";
        return $left <=> $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Smaller
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\Smaller) {
    public static function Smaller($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        if($executor->debug) echo "Smaller: " . $left . " < " . $right . " = " . var_export($left < $right, true) . "\n";
        return $left < $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Concat
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
    public static function Concat($executor, $element, $context = null) {
        
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("Concat: [" . $left . "] . [" . $right . "] = " . $left . $right, 'B');
        return $left . $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Plus
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\Plus) {
    public static function Plus($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        if($executor->debug) echo "Sum: " . $left . " + " . $right . " = " . ($left + $right) . "\n";
        return $left + $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Minus
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\Minus) {
    public static function Minus($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        if($executor->debug) echo "Substract: " . $left . " - " . $right . " = " . ($left - $right) . "\n";
        return $left - $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Mul
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\Mul) {
    public static function Mul($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        if($executor->debug) echo "Multiply: " . $left . " * " . $right . " = " . ($left * $right) . "\n";
        return $left * $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Div
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\Div) {
    public static function Div($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        if($executor->debug) echo "Divide: " . $left . " / " . $right . " = " . ($left / $right) . "\n";
        return $left / $right;
    }
    //PhpParser\Node\Expr\BinaryOp\ShiftLeft
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\ShiftLeft) {
    public static function ShiftLeft($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        if($executor->debug) echo "ShiftLeft: " . decbin($left) . " << " . decbin($right) . " = " . decbin($left << $right) . "\n";
        return $left << $right;
    }
    //PhpParser\Node\Expr\BinaryOp\ShiftRight
    //if($element instanceof \PhpParser\Node\Expr\BinaryOp\ShiftRight) {
    public static function ShiftRight($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        if($executor->debug) echo "ShiftRight: " . decbin($left) . " >> " . decbin($right) . " = " . decbin($left >> $right) . "\n";
        return $left >> $right;
    }
}