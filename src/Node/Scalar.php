<?php
namespace PhpExecutor\Node;

Class Scalar {
    //PhpParser\Node\Scalar\String_
    //if($element instanceof \PhpParser\Node\Scalar\String_) {
    public static function String_($executor, $element, $context = null) {
        $executor->echo("String: [" . $element->value ."]", 'B');
        if($element->value == "END_EXECUTOR") { $executor->halt_execution("END_EXECUTOR"); }

        return $element->value;
    }
    //PhpParser\Node\Scalar\Int_
    //if($element instanceof \PhpParser\Node\Scalar\Int_) {
    public static function Int_($executor, $element, $context = null) {
        $executor->echo("Integer: [" . $element->value . "]", 'B');
        return $element->value;
    }
    //PhpParser\Node\Scalar\Float_
    //if($element instanceof \PhpParser\Node\Scalar\Float_) {
    public static function Float_($executor, $element, $context = null) {
        $executor->echo("Float: [" . $element->value . "]", 'B');
        return $element->value;
    }
    //PhpParser\Node\Scalar\InterpolatedString
    //if($element instanceof \PhpParser\Node\Scalar\InterpolatedString) {
    public static function InterpolatedString($executor, $element, $context = null) {

        $result = '';
        foreach($element->parts as $part) {
            $result .= $executor->execute_ast_element($part, $context);
        }
        $executor->echo("InterpolatedString: [" . $result . "]", 'B');
        return $result;
    }
    //PhpParser\Node\Scalar\EncapsedStringPart
    //if($element instanceof \PhpParser\Node\Scalar\EncapsedStringPart) {
    public static function EncapsedStringPart($executor, $element, $context = null) {
        $executor->echo("EncapsedStringPart: [" . $element->value . "]", 'B');
        return $element->value;
    }
}