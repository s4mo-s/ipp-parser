<?php

/**
 * VUT FIT IPP project 1
 * Return codes
 * @author Samuel Šulo
 */

# Return codes
class Errors
{
    public const
        WRONG_PARAMETER = 10,
        INPUT_FILE_ERROR = 11,
        OUTPUT_FILE_ERROR = 12,
        HEADER_ERROR = 21,
        OPCODE_ERROR = 22,
        LEX_SYNTAX_ERROR = 23,
        INTERNAL_ERROR = 99;
}