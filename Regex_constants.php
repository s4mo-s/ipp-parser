<?php

/**
 * VUT FIT IPP project 1
 * Regular expressions
 * @author Samuel Šulo
 */

# Regular expressions
class Regex_constants
{
    public const
        HEADER = '\.IPPcode22',
        ID = '(?:[A-Za-z_&%!\-\$\*\?]+[A-Za-z0-9_&%!\-\$\*\?]*)',        ## etc. _x2, x_2
        LABEL = '('. self::ID .')',
        VAR = '((?:GF|LF|TF)@' . self::ID .')',                         ## etc. GL@x_2
        CONST = '(?:(int)@((?:\+|\-)?\d+)' .'|'.                        ## etc. int@+55 or int@-55
                '(?:(string)@((?:[^\\\\\s#]|(?:\\\\\d{3}))*))' .'|'.    ## exception of '\', whitespace and '#', then follows \ and 3 digits, etc. \055
                '(?:(bool)@(true|false)))' .'|'.                        ## etc. bool@true or bool@false
                '(?:(nil)@(nil))',
        SYMB = '('. self::VAR .'|'. self::CONST .')',
        TYPE = '(int|string|bool)',
        WS_COMMENT = '\s*(#.*)?$',                                      ## whitespaces(0..n), ('#' then any character(0..n)) 0 or 1 times
        EMPTY_LINE = '<^\s*$>u',
        COMMENT_LINE = '<^' . self::WS_COMMENT . '>u',
        HEADER_LINE = '<^\s*' . self::HEADER . self::WS_COMMENT . '>ui';
}