<?php
namespace PhpExecutor\Node;

Class Scalar {
    //PhpParser\Node\Scalar\String_
    //if($element instanceof \PhpParser\Node\Scalar\String_){
    public static function String_($executor, $element, $context = null){
        if($executor->debug) echo "String\n";
        if($element->value == "END_EXECUTOR"){ die("END_EXECUTOR"); }

        return $element->value;
    }
    //PhpParser\Node\Scalar\Int_
    //if($element instanceof \PhpParser\Node\Scalar\Int_){
    public static function Int_($executor, $element, $context = null){
        if($executor->debug) echo "Integer\n";
        return $element->value;
    }
    //PhpParser\Node\Scalar\Float_
    //if($element instanceof \PhpParser\Node\Scalar\Float_){
    public static function Float_($executor, $element, $context = null){
        if($executor->debug) echo "Float\n";
        return $element->value;
    }
    //PhpParser\Node\Scalar\InterpolatedString
    //if($element instanceof \PhpParser\Node\Scalar\InterpolatedString){
    public static function InterpolatedString($executor, $element, $context = null){
        if($executor->debug) echo "InterpolatedString\n";
        $result = '';
        foreach($element->parts as $part){
            $result .= $executor->execute_ast_element($part, $context);
        }
        return $result;
    }
    //PhpParser\Node\Scalar\EncapsedStringPart
    //if($element instanceof \PhpParser\Node\Scalar\EncapsedStringPart){
    public static function EncapsedStringPart($executor, $element, $context = null){
        if($executor->debug) echo "EncapsedStringPart\n";
        return $element->value;
    }
}