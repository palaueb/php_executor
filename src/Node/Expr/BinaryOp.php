<?php

namespace PhpExecutor\Node\Expr;

Class BinaryOp {
//BinaryOp
    //PhpParser\Node\Expr\BinaryOp\Pow
    public static function Pow($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);
        $result = $left ** $right;

        $executor->echo("Pow: " . $left . " ** " . $right . " = " . $result, 'B');
        return $result;
    }
    //PhpParser\Node\Expr\BinaryOp\Coalesce
    public static function Coalesce($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("Coalesce: " . var_export($left, true) . " ?? " . var_export($right, true) . " = " . var_export($left ?? $right, true), 'B');
        return $left ?? $right;
    }
    //PhpParser\Node\Expr\BinaryOp\BooleanOr
    public static function BooleanOr($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("BooleanOr: " . var_export($left, true) . " || " . var_export($right, true) . " = " . var_export($left || $right, true), 'B');
        return $left || $right;
    }
    //PhpParser\Node\Expr\BinaryOp\BooleanAnd
    public static function BooleanAnd($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("BooleanAnd: " . var_export($left, true) . " && " . var_export($right, true) . " = " . var_export($left && $right, true), 'B');
        return $left && $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Equal
    public static function Equal($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("Equal: " . var_export($left, true) . " == " . var_export($right, true) . " = " . var_export($left == $right, true), 'B');
        return $left == $right;
    }
    //PhpParser\Node\Expr\BinaryOp\NotEqual
    public static function NotEqual($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("NotEqual: " . var_export($left, true) . " != " . var_export($right, true) . " = " . var_export($left != $right, true), 'B');
        return $left != $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Identical
    public static function Identical($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("Identical: " . var_export($left, true) . " === " . var_export($right, true) . " = " . var_export($left === $right, true), 'B');
        return $left === $right;
    }
    //PhpParser\Node\Expr\BinaryOp\NotIdentical
    public static function NotIdentical($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("NotIdentical: " . var_export($left, true) . " !== " . var_export($right, true) . " = " . var_export($left !== $right, true), 'B');
        return $left !== $right;
    }
    //PhpParser\Node\Expr\BinaryOp\SmallerOrEqual
    public static function SmallerOrEqual($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("SmallerOrEqual: " . $left . " <= " . $right . " = " . var_export($left <= $right, true), 'B');
        return $left <= $right;
    }
    //PhpParser\Node\Expr\BinaryOp\GreaterOrEqual
    public static function GreaterOrEqual($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("GreaterOrEqual: " . $left . " >= " . $right . " = " . $left >= $right, 'B');
        return $left >= $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Spaceship
    public static function Spaceship($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("Spaceship: " . $left . " <=> " . $right . " = " . ($left <=> $right), 'B');
        return $left <=> $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Smaller
        public static function Smaller($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("Smaller: " . $left . " < " . $right . " = " . var_export($left < $right, true), 'B');
        return $left < $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Greater
    public static function Greater($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("Greater: " . $left . " > " . $right . " = " . var_export($left > $right, true), 'B');
        return $left > $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Concat
        public static function Concat($executor, $element, $context = null) {
        
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("Concat: [" . $left . "] . [" . $right . "] = " . $left . $right, 'B');
        return $left . $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Plus
        public static function Plus($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("Plus: " . $left . " + " . $right . " = " . ($left + $right), 'B');
        return $left + $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Minus
        public static function Minus($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("Minus: " . $left . " - " . $right . " = " . ($left - $right), 'B');
        return $left - $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Mul
        public static function Mul($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("Multiply: " . $left . " * " . $right . " = " . ($left * $right), 'B');
        return $left * $right;
    }
    //PhpParser\Node\Expr\BinaryOp\Div
        public static function Div($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("Divide: " . $left . " / " . $right . " = " . ($left / $right), 'B');
        return $left / $right;
    }
    //PhpParser\Node\Expr\BinaryOp\ShiftLeft
        public static function ShiftLeft($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("ShiftLeft: " . decbin($left) . " << " . decbin($right) . " = " . decbin($left << $right), 'B');
        return $left << $right;
    }
    //PhpParser\Node\Expr\BinaryOp\ShiftRight
        public static function ShiftRight($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("ShiftRight: " . decbin($left) . " >> " . decbin($right) . " = " . decbin($left >> $right), 'B');
        return $left >> $right;
    }
    //PhpParser\Node\Expr\BinaryOp\BitwiseOr
    public static function BitwiseOr($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("BitwiseOr: " . decbin($left) . " | " . decbin($right) . " = " . decbin($left | $right), 'B');
        return $left | $right;
    }
    //PhpParser\Node\Expr\BinaryOp\BitwiseAnd
    public static function BitwiseAnd($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("BitwiseAnd: " . decbin($left) . " & " . decbin($right) . " = " . decbin($left & $right), 'B');
        return $left & $right;
    }
    //PhpParser\Node\Expr\BinaryOp\LogicalOr
    public static function LogicalOr($executor, $element, $context = null) {
        $left = $executor->execute_ast_element($element->left, $context);
        $right = $executor->execute_ast_element($element->right, $context);

        $executor->echo("LogicalOr: " . var_export($left, true) . " or " . var_export($right, true) . " = " . var_export($left or $right, true), 'B');
        return $left or $right;
    }
}