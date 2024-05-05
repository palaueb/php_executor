<?php

namespace PhpExecutor\Node;

Class Stmt {
        public static function exist(){ return true; }
        //stmts
        //\PhpParser\Node\Stmt\Expression
        public static function Expression($executor, $element, $context = null){
            if($executor->debug) echo "Expression\n";
            return $executor->execute_ast_element($element->expr, $context);
        }

        //\PhpParser\Node\Stmt\Echo_
        public static function Echo_($executor, $element, $context = null){
            if($executor->debug) echo "Echo\n";
            //exprs is an array of expressions that needs to be concatenaded to be printed
            $result = '';
            foreach($element->exprs as $expr){
                $result .= $executor->execute_ast_element($expr, $context);
            }

            if($executor->debug) echo "echo: ";
            echo $result;
            if($executor->debug) echo "\n";
            return true;
        }
        //PhpParser\Node\Stmt\Function_
        public static function Function_($executor, $element, $context = null){
            if($executor->debug) echo "Function: " . $element->name->name . PHP_EOL;
            $executor->create_function($element, $context);
            //$executor->current_environment['functions'][$name] = $element;
            return true;
        }

        //PhpParser\Node\Stmt\If_
        public static function If_($executor, $element, $context = null){
            if($executor->debug) echo "If\n";
            $cond = $executor->execute_ast_element($element->cond, $context);
            if($cond){
                $executor->execute_ast($element->stmts, $context);
            }else{
                if(isset($element->elseifs)){
                    foreach($element->elseifs as $elseif){
                        $cond = $executor->execute_ast_element($elseif->cond, $context);
                        if($cond){
                            $executor->execute_ast($elseif->stmts, $context);
                            return true;
                        }
                    }
                }
                if(isset($element->else)){
                    $executor->execute_ast($element->else->stmts, $context);
                }
            }
            return true;
        }
        //PhpParser\Node\Stmt\For_
        public static function For_($executor, $element, $context = null){
            if($executor->debug) echo "For\n";
            $executor->execute_for($element, $context);
            return true;
        }
        
        //PhpParser\Node\Stmt\Break_
        public static function Break_($executor, $element, $context = null){
            if($executor->debug) echo "Break\n";
            $break_num = $element->num;
            return ['break' => $break_num];
        }
        //PhpParser\Node\Stmt\Nop
        public static function Nop($executor, $element, $context = null){
            if($executor->debug) echo "Nop\n";
            //throw new \Exception("Nop detected on code execution!");
            return true;
        }

}