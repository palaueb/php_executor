<?php

namespace PhpExecutor\Node\Expr;

Class AssignOp {
        //PhpParser\Node\Expr\AssignOp\Pow
        //if($element instanceof \PhpParser\Node\Expr\AssignOp\Pow) {
        public static function Pow($executor, $element, $context = null) {
            $name = $executor->get_variable_name($element->var, $context); //ha de ser el nom de la variable
            $expr = $executor->execute_ast_element($element->expr, $context);

            $left = $executor->get_variable_value($name, $context);
            $right = $expr;
            $result = $left ** $right;

            $executor->echo("AssignOp Pow: " . $left . " ** " . $right . " = " . $result, 'B');

            $executor->set_variable_value($name, $result, $context);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\Concat
        //if($element instanceof \PhpParser\Node\Expr\AssignOp\Concat) {
        public static function Concat($executor, $element, $context = null) {
            $name = $executor->get_variable_name($element->var, $context); //ha de ser el nom de la variable
            $expr = $executor->execute_ast_element($element->expr, $context);

            $left = $executor->get_variable_value($name, $context);
            $right = $expr;
            $result = $left . $right;

            $executor->echo("AssignOp Concat: " . $left . " . " . $right . " = " . $result, 'B');

            $executor->set_variable_value($name, $result);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\Plus
        //if($element instanceof \PhpParser\Node\Expr\AssignOp\Plus) {
        public static function Plus($executor, $element, $context = null) {
            $name = $executor->get_variable_name($element->var, $context); //ha de ser el nom de la variable
            $expr = $executor->execute_ast_element($element->expr, $context);

            $left = $executor->get_variable_value($name, $context);
            $right = $expr;
            $result = $left + $right;

            $executor->echo("AssignOp Plus: " . $left . " + " . $right . " = " . $result, 'B');

            $executor->set_variable_value($name, $result, $context);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\Minus
        //if($element instanceof \PhpParser\Node\Expr\AssignOp\Minus) {
        public static function Minus($executor, $element, $context = null) {
            $name = $executor->get_variable_name($element->var, $context); //ha de ser el nom de la variable
            $expr = $executor->execute_ast_element($element->expr, $context);

            $left = $executor->get_variable_value($name, $context);
            $right = $expr;
            $result = $left - $right;

            $executor->echo("AssignOp Minus: " . $left . " - " . $right . " = " . $result, 'B');

            $executor->set_variable_value($name, $result, $context);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\Mul
        //if($element instanceof \PhpParser\Node\Expr\AssignOp\Mul) {
        public static function Mul($executor, $element, $context = null) {
            $name = $executor->get_variable_name($element->var, $context); //ha de ser el nom de la variable
            $expr = $executor->execute_ast_element($element->expr, $context);

            $left = $executor->get_variable_value($name, $context);
            $right = $expr;
            $result = $left * $right;

            $executor->echo("AssignOp Mul: " . $left . " * " . $right . " = " . $result, 'B');

            $executor->set_variable_value($name, $result, $context);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\Div
        //if($element instanceof \PhpParser\Node\Expr\AssignOp\Div) {
        public static function Div($executor, $element, $context = null) {
            $name = $executor->get_variable_name($element->var, $context); //ha de ser el nom de la variable
            $expr = $executor->execute_ast_element($element->expr, $context);

            $left = $executor->get_variable_value($name, $context);
            $right = $expr;
            $result = $left / $right;

            $executor->echo("AssignOp Div: " . $left . " / " . $right . " = " . $result, 'B');

            $executor->set_variable_value($name, $result, $context);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\Mod
        //if($element instanceof \PhpParser\Node\Expr\AssignOp\Mod) {
        public static function Mod($executor, $element, $context = null) {
            $name = $executor->get_variable_name($element->var, $context); //ha de ser el nom de la variable
            $expr = $executor->execute_ast_element($element->expr, $context);

            $left = $executor->get_variable_value($name, $context);
            $right = $expr;
            $result = $left % $right;

            $executor->echo("AssignOp Mod: " . $left . " % " . $right . " = " . $result, 'B');

            $executor->set_variable_value($name, $result, $context);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\BitwiseAnd
        //if($element instanceof \PhpParser\Node\Expr\AssignOp\BitwiseAnd) {
        public static function BitwiseAnd($executor, $element, $context = null) {
            $name = $executor->get_variable_name($element->var, $context); //ha de ser el nom de la variable
            $expr = $executor->execute_ast_element($element->expr, $context);

            $left = $executor->get_variable_value($name, $context);
            $right = $expr;
            $result = $left & $right;

            $executor->echo("AssignOp BitwiseAnd: " . $left . " & " . $right . " = " . $result, 'B');
            //show results as binary representations
            $executor->echo("AssignOp BitwiseAnd: " . decbin($left) . " & " . decbin($right) . " = " . decbin($result), 'B');


            $executor->set_variable_value($name, $result, $context);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\BitwiseOr
        //if($element instanceof \PhpParser\Node\Expr\AssignOp\BitwiseOr) {
        public static function BitwiseOr($executor, $element, $context = null) {
            $name = $executor->get_variable_name($element->var, $context); //ha de ser el nom de la variable
            $expr = $executor->execute_ast_element($element->expr, $context);

            $left = $executor->get_variable_value($name, $context, $context);
            $right = $expr;
            $result = $left | $right;

            $executor->echo("AssignOp BitwiseOr: " . $left . " | " . $right . " = " . $result, 'B');
            //show results as binary representations
            $executor->echo("AssignOp BitwiseOr: " . decbin($left) . " | " . decbin($right) . " = " . decbin($result), 'B');

            $executor->set_variable_value($name, $result, $context);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\BitwiseXor
        //if($element instanceof \PhpParser\Node\Expr\AssignOp\BitwiseXor) {
        public static function BitwiseXor($executor, $element, $context = null) {
            $name = $executor->get_variable_name($element->var, $context); //ha de ser el nom de la variable
            $expr = $executor->execute_ast_element($element->expr, $context);

            $left = $executor->get_variable_value($name, $context);
            $right = $expr;
            $result = $left ^ $right;

            $executor->echo("AssignOp BitwiseXor: " . $left . " ^ " . $right . " = " . $result, 'B');
            //show results as binary representations
            $executor->echo("AssignOp BitwiseXor: " . decbin($left) . " ^ " . decbin($right) . " = " . decbin($result), 'B');

            $executor->set_variable_value($name, $result, $context);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\ShiftLeft
        //if($element instanceof \PhpParser\Node\Expr\AssignOp\ShiftLeft) {
        public static function ShiftLeft($executor, $element, $context = null) {
            $name = $executor->get_variable_name($element->var, $context); //ha de ser el nom de la variable
            $expr = $executor->execute_ast_element($element->expr, $context);

            $left = $executor->get_variable_value($name, $context);
            $right = $expr;
            $result = $left << $right;

            $executor->echo("AssignOp ShiftLeft: " . $left . " << " . $right . " = " . $result, 'B');
            //show results as binary representations
            $executor->echo("AssignOp ShiftLeft: " . decbin($left) . " << " . decbin($right) . " = " . decbin($result), 'B');

            $executor->set_variable_value($name, $result, $context);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\ShiftRight
        //if($element instanceof \PhpParser\Node\Expr\AssignOp\ShiftRight) {
        public static function ShiftRight($executor, $element, $context = null) {
            $name = $executor->get_variable_name($element->var, $context); //ha de ser el nom de la variable
            $expr = $executor->execute_ast_element($element->expr, $context);

            $left = $executor->get_variable_value($name, $context);
            $right = $expr;
            $result = $left >> $right;

            $executor->echo("AssignOp ShiftRight: " . $left . " >> " . $right . " = " . $result, 'B');
            //show results as binary representations
            $executor->echo("AssignOp ShiftRight: " . decbin($left) . " >> " . decbin($right) . " = " . decbin($result), 'B');

            $executor->set_variable_value($name, $result, $context);
            return true;
        }
        //PhpParser\Node\Expr\AssignOp\Coalesce
        //if($element instanceof \PhpParser\Node\Expr\AssignOp\Coalesce) {
        public static function Coalesce($executor, $element, $context = null) {
            $executor->echo("AssignOp Coalesce", 'B');

            $name = $executor->get_variable_name($element->var, $context);
            $expr = $executor->execute_ast_element($element->expr, $context);
            $left = null;
            if(!$executor->exists_variable_name($element->var, $context)) {   
                $executor->echo("Variable is null, then we set to:" . $expr, 'B');
                $executor->set_variable_value($name, $expr, $context);
            }else{
                $left = $executor->get_variable_value($name, $context);
            }

            $right = $expr;
            $left ??= $right;

            $executor->echo("AssignOp Coalesce: " . $left . " ??= " . $right . " = " . $left, 'B');

            return true;
        }
}