<?php

/**
 * VUT FIT IPP project 1
 * IPPcode22 parser
 * @author Samuel Šulo
 */

include "Regex_constants.php";
include "Errors.php";

const INSTRUCTIONS =
[
    'MOVE' => ['var', 'symb'],
    'CREATEFRAME' => [],
    'PUSHFRAME' => [],
    'POPFRAME' => [],
    'DEFVAR' => ['var'],
    'CALL' => ['label'],
    'RETURN' => [],
    'PUSHS' => ['symb'],
    'POPS' => ['var'],
    'ADD' => ['var', 'symb', 'symb'],
    'SUB' => ['var', 'symb', 'symb'],
    'MUL' => ['var', 'symb', 'symb'],
    'IDIV' => ['var', 'symb', 'symb'],
    'LT' => ['var', 'symb', 'symb'],
    'GT' => ['var', 'symb', 'symb'],
    'EQ' => ['var', 'symb', 'symb'],
    'AND' => ['var', 'symb', 'symb'],
    'OR' => ['var', 'symb', 'symb'],
    'NOT' => ['var', 'symb'],
    'INT2CHAR' => ['var', 'symb'],
    'STRI2INT' => ['var', 'symb', 'symb'],
    'READ' => ['var', 'type'],
    'WRITE' => ['symb'],
    'CONCAT' => ['var', 'symb', 'symb'],
    'STRLEN' => ['var', 'symb'],
    'GETCHAR' => ['var', 'symb', 'symb'],
    'SETCHAR' => ['var', 'symb', 'symb'],
    'TYPE' => ['var', 'symb'],
    'LABEL' => ['label'],
    'JUMPIFEQ' => ['label', 'symb', 'symb'],
    'JUMPIFNEQ' => ['label', 'symb', 'symb'],
    'JUMP' => ['label'],
    'EXIT' => ['symb'],
    'DPRINT' => ['symb'],
    'BREAK' => []];

if (($argc == 2 && ($argv[1] != '--help')) || $argc > 2)
{
    fwrite(STDERR, "Chybajuci parameter skriptu (ak je treba) alebo pouzitie zakazanej kombinacie parametrov.\n");
    exit(Errors::WRONG_PARAMETER);
}

if ($argc == 2 && $argv[1] == '--help')
{
    echo "Skript typu filter(parse.php) nacita zo standardneho vstupu zdrojovy kod v IPP-code22.
Skontroluje lexikalnu a syntakticku spravnost kodu a vypise na standardny vystup XML reprezentaciu programu.";
    exit(0);
}

$xml_header = 0;
$header = 0;
$instruction_order = 1;

while (($line = fgets(STDIN)) !== false)
{
    # Empty line
    if (preg_match(Regex_constants::EMPTY_LINE, $line)) {
        continue;
    }

    # Comment line
    if (preg_match(Regex_constants::COMMENT_LINE, $line)) {
        continue;
    }

    if ($header == 0) {
        # Header line
        if (preg_match(Regex_constants::HEADER_LINE, $line)) {
            ob_start();
            if ($xml_header == 0) {
                echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
                echo "<program language=\"IPPcode22\">\n";
                $xml_header = 1;
            }
            $header = 1;
            continue;
        }
        else {
            fwrite(STDERR, "Chybna alebo chybajuca hlavicka.\n");
            exit(Errors::HEADER_ERROR);
        }
    }

    $analyse_error = 1;
    $wrong_instruction = 1;
    foreach (INSTRUCTIONS as $INSTRUCTION => $args)
    {
        # If instruction should have arguments then
        if ($args){
            # check if the instruction is followed by some arguments
            if (preg_match('<^\s*' . $INSTRUCTION . '\s*' . '$>ui', $line, $matches)){
            }

            # check if an argument is connected to the instruction without whitespaces, if it's true then exit with error
            elseif (preg_match('<^\s*' . $INSTRUCTION . '([^\s#]+)'. '\s*' . '$>ui', $line, $matches)){
                ob_end_clean();
                fwrite(STDERR, "Neznamy alebo chybny operacny kod.\n");
                exit(Errors::OPCODE_ERROR);
            }
        }

        # <^\s*	  delimeter, start line, whitespace(0..n)
        # ([^#]*) negated set (everything except # (0..n times)) => args
        # >ui     end line, utf-8, case insensitive
        if (preg_match('<^\s*' . $INSTRUCTION . '([^#]*)' . Regex_constants::WS_COMMENT . '>ui', $line, $matches)) {
            echo "    <instruction order=\"" . $instruction_order++ . "\" opcode=\"" . $INSTRUCTION . "\">\n";

            $wrong_instruction = 0;
            $arg_order = 1;
            $arr = array();
            $arrValue = 1;

            # Creates regular expression from arguments matched on line
            # <^   start string
            $processArg = '<^';
            foreach ($args as $arg) {
                # \s+  whitespace (1..n)
                if ($arg == 'symb') {
                    $processArg .= '\s+' . Regex_constants::SYMB;
                } elseif ($arg == 'var') {
                    $processArg .= '\s+' . Regex_constants::VAR;
                } elseif ($arg == 'type') {
                    $processArg .= '\s+' . Regex_constants::TYPE;
                } elseif ($arg == 'label') {
                    $processArg .= '\s+' . Regex_constants::LABEL;
                }
            }
            # \s*$>u'  whitespace (0..n), end string, utf-8
            $processArg .= '\s*$>u';

            # Compares regular expression with arguments and store possibly matches
            # If there are no matches then break
            $found = preg_match($processArg, $matches[1], $matchedOperands);
            if ($found == null){
                break;
            }

            $analyse_error = 0;

            # Finds non-empty strings and stores them in array
            foreach ($matchedOperands as $key => $value)
            {
                if ($value != '') {
                    $arr[] .= $value;
                }
                elseif (isset($value)) {
                    if ($value === 'string') {
                        $arr[] .= $value;
                    }
                }
            }

            foreach ($args as $arg)
            {
                # Replaces problematic characters
                $arr = preg_replace('/&/', '&amp;', $arr);
                $arr = preg_replace('/</', '&lt;', $arr);
                $arr = preg_replace('/>/', '&gt;', $arr);
                if ($arg == 'symb') {
                    # 'symb' is variable
                    if (preg_match('<^' . Regex_constants::VAR . '$>u', $arr[$arrValue++])) {
                        echo "        <arg" . $arg_order . " type=\"var\">" . ($arr[$arrValue++] ?? "") . "</arg" . $arg_order++ . ">\n";
                    }
                    # 'symb' is constant
                    else {
                        echo "        <arg" . $arg_order . " type=\"" . $arr[$arrValue++] . "\">" . ($arr[$arrValue++] ?? "") . "</arg" . $arg_order++ . ">\n";
                    }
                }
                else {
                    echo "        <arg" . $arg_order . " type=\"" . $arg . "\">" . ($arr[$arrValue++] ?? ""). "</arg" . $arg_order++ . ">\n";
                }
            }
            echo "    </instruction>\n";
            break;
        }
    }
    if ($wrong_instruction == 1) {
        ob_end_clean();
        fwrite(STDERR, "Neznamy alebo chybny operacny kod.\n");
        exit(Errors::OPCODE_ERROR);
    }
    elseif ($analyse_error == 1){
        ob_end_clean();
        fwrite(STDERR, "Lexikalna alebo syntakticka chyba.\n");
        exit(Errors::LEX_SYNTAX_ERROR);
    }
}
echo "</program>" . "\n";
exit(0);
