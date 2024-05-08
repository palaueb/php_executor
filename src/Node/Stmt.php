<?php

namespace PhpExecutor\Node;

Class Stmt {
        //stmts
        //\PhpParser\Node\Stmt\Expression
        public static function Expression($executor, $element, $context = null) {
            $executor->echo("Expression type: " . get_class($element->expr), 'B');
            return $executor->execute_ast_element($element->expr, $context);
        }

        //\PhpParser\Node\Stmt\Echo_
        public static function Echo_($executor, $element, $context = null) {
            //exprs is an array of expressions that needs to be concatenaded to be printed
            $result = '';
            foreach($element->exprs as $expr) {
                $result .= $executor->execute_ast_element($expr, $context);
            }

            $executor->echo("Echo (".$element->getLine().") $result", 'B');

            $executor->echo($result, 'W');

            return true;
        }
        //PhpParser\Node\Stmt\Function_
        public static function Function_($executor, $element, $context = null) {
            $executor->echo("Function: " . $element->name->name . " in context [$context]", 'B');

            $executor->create_function($element, $context);
            //$executor->current_environment['functions'][$name] = $element;
            return true;
        }
        //PhpParser\Node\Stmt\Class_
        public static function Class_($executor, $element, $context = null) {
            $executor->echo("Class: " . $element->name->name . " in context [$context]", 'B');

            $executor->create_class($element, $context);
            return true;
        }
        //PhpParser\Node\Stmt\ClassMethod
        public static function ClassMethod($executor, $element, $context = null) {
            $executor->echo("ClassMethod: " . $element->name->name . " in context [$context]", 'R');
            die();
            //$executor->create_class_method($element, $context);
            return true;
        }
        //PhpParser\Node\Stmt\Namespace_
        public static function Namespace_($executor, $element, $context = null) {
            $executor->echo("Namespace: " . $element->name->name . " in context [$context]", 'B');

            $executor->create_namespace($element, $context);
            return true;
        }
        //PhpParser\Node\Stmt\If_
        public static function If_($executor, $element, $context = null) {
            $executor->echo("If", 'B');

            $cond = $executor->execute_ast_element($element->cond, $context);
            if($cond) {
                $executor->execute_ast($element->stmts, $context);
            }else{
                if(isset($element->elseifs)) {
                    foreach($element->elseifs as $elseif) {
                        $executor->echo("ElseIf", 'B');
                        $cond = $executor->execute_ast_element($elseif->cond, $context);
                        if($cond) {
                            $executor->execute_ast($elseif->stmts, $context);
                            return true;
                        }
                    }
                }
                if(isset($element->else)) {
                    $executor->echo("Else", 'B');
                    $executor->execute_ast($element->else->stmts, $context);
                }
            }
            return true;
        }
        //PhpParser\Node\Stmt\For_
        public static function For_($executor, $element, $context = null) {
            $executor->echo("For", 'B');
            $executor->execute_for($element, $context);
            return true;
        }
        
        //PhpParser\Node\Stmt\Break_
        public static function Break_($executor, $element, $context = null) {
            $executor->echo("Break", 'B');
            $break_num = $element->num;
            return ['break' => $break_num];
        }
        //PhpParser\Node\Stmt\Nop
        public static function Nop($executor, $element, $context = null) {
            $executor->echo("Nop", 'B');
            //throw new \Exception("Nop detected on code execution!");
            return true;
        }

}