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
        //PhpParser\Node\Stmt\Switch_
        public static function Switch_($executor, $element, $context = null) {
            $executor->echo("Switch", 'B');
            $condition = $executor->execute_ast_element($element->cond, $context);
            $execute_next = false;
            $cases = $element->cases;
            foreach($cases as $case) {
                if(is_null($case->cond)) {
                    // this is the default case
                    $result = $executor->execute_ast($case->stmts, $context);
                    break;
                }
                $case_condition = $executor->execute_ast_element($case->cond, $context);
                if($condition == $case_condition || $execute_next === true) {
                    $result = $executor->execute_ast($case->stmts, $context);
                    //if result is break, we need to break the switch
                    if(is_array($result) && array_key_exists('break', $result)) {
                        break;
                    }else{
                        $execute_next = true;
                    }
                }
            }
            /*
            PhpParser\Node\Stmt\Switch_::__set_state(array(
            'cond' => 
            PhpParser\Node\Expr\Variable::__set_state(array(
                'name' => 'a',
                'attributes' => 
                array (
                'startLine' => 3,
                'startTokenPos' => 8,
                'startFilePos' => 19,
                'endLine' => 3,
                'endTokenPos' => 8,
                'endFilePos' => 20,
                ),
            )),
            'cases' => 
            array (
                0 => 
                PhpParser\Node\Stmt\Case_::__set_state(array(
                'cond' => 
                PhpParser\Node\Scalar\Int_::__set_state(array(
                    'value' => 0,
                    'attributes' => 
                    array (
                    'startLine' => 4,
                    'startTokenPos' => 15,
                    'startFilePos' => 34,
                    'endLine' => 4,
                    'endTokenPos' => 15,
                    'endFilePos' => 34,
                    'rawValue' => '0',
                    'kind' => 10,
                    ),
                )),
                'stmts' => 
                array (
                    0 => 
                    PhpParser\Node\Stmt\Echo_::__set_state(array(
                    'exprs' => 
                    array (
                        0 => 
                        PhpParser\Node\Scalar\String_::__set_state(array(
                        'value' => 'bad',
                        'attributes' => 
                        array (
                            'startLine' => 5,
                            'startTokenPos' => 20,
                            'startFilePos' => 50,
                            'endLine' => 5,
                            'endTokenPos' => 20,
                            'endFilePos' => 54,
                            'kind' => 2,
                            'rawValue' => '"bad"',
                        ),
                        )),
                    ),
                    'attributes' => 
                    array (
                        'startLine' => 5,
                        'startTokenPos' => 18,
                        'startFilePos' => 45,
                        'endLine' => 5,
                        'endTokenPos' => 21,
                        'endFilePos' => 55,
                    ),
                    )),
                    1 => 
                    PhpParser\Node\Stmt\Break_::__set_state(array(
                    'num' => NULL,
                    'attributes' => 
                    array (
                        'startLine' => 6,
                        'startTokenPos' => 23,
                        'startFilePos' => 65,
                        'endLine' => 6,
                        'endTokenPos' => 24,
                        'endFilePos' => 70,
                    ),
                    )),
                ),
                'attributes' => 
                array (
                    'startLine' => 4,
                    'startTokenPos' => 13,
                    'startFilePos' => 29,
                    'endLine' => 6,
                    'endTokenPos' => 24,
                    'endFilePos' => 70,
                ),
                )),
                1 => 
                PhpParser\Node\Stmt\Case_::__set_state(array(
                'cond' => 
                PhpParser\Node\Scalar\Int_::__set_state(array(
                    'value' => 1,
                    'attributes' => 
                    array (
                    'startLine' => 7,
                    'startTokenPos' => 28,
                    'startFilePos' => 81,
                    'endLine' => 7,
                    'endTokenPos' => 28,
                    'endFilePos' => 81,
                    'rawValue' => '1',
                    'kind' => 10,
                    ),
                )),
                'stmts' => 
                array (
                    0 => 
                    PhpParser\Node\Stmt\Echo_::__set_state(array(
                    'exprs' => 
                    array (
                        0 => 
                        PhpParser\Node\Scalar\String_::__set_state(array(
                        'value' => 'good',
                        'attributes' => 
                        array (
                            'startLine' => 8,
                            'startTokenPos' => 33,
                            'startFilePos' => 97,
                            'endLine' => 8,
                            'endTokenPos' => 33,
                            'endFilePos' => 102,
                            'kind' => 2,
                            'rawValue' => '"good"',
                        ),
                        )),
                    ),
                    'attributes' => 
                    array (
                        'startLine' => 8,
                        'startTokenPos' => 31,
                        'startFilePos' => 92,
                        'endLine' => 8,
                        'endTokenPos' => 34,
                        'endFilePos' => 103,
                    ),
                    )),
                    1 => 
                    PhpParser\Node\Stmt\Break_::__set_state(array(
                    'num' => NULL,
                    'attributes' => 
                    array (
                        'startLine' => 9,
                        'startTokenPos' => 36,
                        'startFilePos' => 113,
                        'endLine' => 9,
                        'endTokenPos' => 37,
                        'endFilePos' => 118,
                    ),
                    )),
                ),
                )),
                2 => 
                PhpParser\Node\Stmt\Case_::__set_state(array(
                'cond' => NULL,
                'stmts' => 
                array (
                    0 => 
                    PhpParser\Node\Stmt\Echo_::__set_state(array(
                    'exprs' => 
                    array (
                        0 => 
                        PhpParser\Node\Scalar\String_::__set_state(array(
                        'value' => 'bad',
                        'attributes' => 
                        array (
                            'startLine' => 11,
                            'startTokenPos' => 44,
                            'startFilePos' => 146,
                            'endLine' => 11,
                            'endTokenPos' => 44,
                            'endFilePos' => 150,
                            'kind' => 2,
                            'rawValue' => '"bad"',
                        ),
                        )),
                    ),
                    )),
                    1 => 
                    PhpParser\Node\Stmt\Break_::__set_state(array(
                    'num' => NULL,
                    'attributes' => 
                    array (
                        'startLine' => 12,
                        'startTokenPos' => 47,
                        'startFilePos' => 161,
                        'endLine' => 12,
                        'endTokenPos' => 48,
                        'endFilePos' => 166,
                    ),
                    )),
                ),
                'attributes' => 
                array (
                    'startLine' => 10,
                    'startTokenPos' => 39,
                    'startFilePos' => 124,
                    'endLine' => 12,
                    'endTokenPos' => 48,
                    'endFilePos' => 166,
                ),
                )),
            ),
            )
            */

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
        //PhpParser\Node\Stmt\TryCatch
        public static function TryCatch($executor, $element, $context = null) {
            $executor->echo("TryCatch", 'B');
            try {
                $executor->execute_ast($element->stmts, $context);
            } catch (\Exception $e) {
                //saves the Exception into the context of execution of $executor
                $executor->set_variable_value('e', $e, $context);

                $catches = $element->catches;
                foreach($catches as $catch) {
                    $executor->echo("Catch", 'B');
                    $executor->execute_ast($catch->stmts, $context);
                }
            }
            //$executor->execute_ast($element->stmts, $context);
            return true;
        }

}