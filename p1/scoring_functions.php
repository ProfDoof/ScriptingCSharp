<?php

function no_string_member_functions($source) {
   return  source_does_not_contain_regex($source,"/\.append/","built-in string functions (like .append)")
       &&  source_does_not_contain_regex($source,"/\.assign/","built-in string functions (like .assign)")
       &&  source_does_not_contain_regex($source,"/\.at/","built-in string functions (like .at)")
       &&  source_does_not_contain_regex($source,"/\.begin/","built-in string functions (like .begin)")
       &&  source_does_not_contain_regex($source,"/\.c_str/","built-in string functions (like .c_str)")
       &&  source_does_not_contain_regex($source,"/\.capacity/","built-in string functions (like .capacity)")
       &&  source_does_not_contain_regex($source,"/\.clear/","built-in string functions (like .clear)")
       &&  source_does_not_contain_regex($source,"/\.compare/","built-in string functions (like .compare)")
       &&  source_does_not_contain_regex($source,"/\.copy/","built-in string functions (like .copy)")
       &&  source_does_not_contain_regex($source,"/\.data/","built-in string functions (like .data)")
       &&  source_does_not_contain_regex($source,"/\.empty/","built-in string functions (like .empty)")
       &&  source_does_not_contain_regex($source,"/\.end/","built-in string functions (like .end)")
       &&  source_does_not_contain_regex($source,"/\.erase/","built-in string functions (like .erase)")
       &&  source_does_not_contain_regex($source,"/\.find/","built-in string functions (like .find)")
       &&  source_does_not_contain_regex($source,"/\.find_first_of/","built-in string functions (like .find_first_of)")
       &&  source_does_not_contain_regex($source,"/\.find_last_of/","built-in string functions (like .find_last_of)")
       &&  source_does_not_contain_regex($source,"/\.find_first_not_of/","built-in string functions (like .find_first_not_of)")
       &&  source_does_not_contain_regex($source,"/\.find_last_not_of/","built-in string functions (like .find_last_not_of)")
       &&  source_does_not_contain_regex($source,"/\.insert/","built-in string functions (like .insert)")
       &&  source_does_not_contain_regex($source,"/\.length/","built-in length functions (like .length)")
       &&  source_does_not_contain_regex($source,"/\.max_size/","built-in string functions (like .max_size)")
       &&  source_does_not_contain_regex($source,"/\.push_back/","built-in string functions (like .push_back)")
       &&  source_does_not_contain_regex($source,"/\.rbegin/","built-in string functions (like .rbegin)")
       &&  source_does_not_contain_regex($source,"/\.rend/","built-in string functions (like .rend)")
       &&  source_does_not_contain_regex($source,"/\.replace/","built-in string functions (like .replace)")
       &&  source_does_not_contain_regex($source,"/\.reserve/","built-in string functions (like .reserve)")
       &&  source_does_not_contain_regex($source,"/\.resize/","built-in string functions (like .resize)")
       &&  source_does_not_contain_regex($source,"/\.rfind/","built-in string functions (like .rfind)")
       &&  source_does_not_contain_regex($source,"/\.size/","built-in string functions (like .size)")
       &&  source_does_not_contain_regex($source,"/\.substr/","built-in string functions (like .substr)")
       &&  source_does_not_contain_regex($source,"/\.swap/","built-in string functions (like .swap)");
}

function only_allowed_libraries($source) {
   return  source_does_not_contain_regex($source,"/\<algorithm\>/","libraries with functions you should write yourself (like <algorithm>)")
       &&  source_does_not_contain_regex($source,"/\<cmath\>/","libraries with functions you should write yourself (like <cmath>)")
       &&  source_does_not_contain_regex($source,"/\"cmath\"/","libraries with functions you should write yourself (like \"cmath\")");
}

function check_blacklist($source) {
   return  no_string_member_functions($source)
       &&  only_allowed_libraries($source);
}

function no_while_loops($source) {
   return  source_does_not_contain_regex($source,"/while\s*\(/","any while loops");
}

function no_for_loops($source) {
   return  source_does_not_contain_regex($source,"/for\s*\(/","any for loops");
}

function no_loops($source) {
   return  no_while_loops($source) 
       &&  no_for_loops($source);
}


function run_and_check($in,$out,$notout) {
    if($notout == ""){
        return run("prog",$in,$output)
        && output_contains_lines($output,$out);  
    }
    else{
        return run("prog",$in,$output)
            && output_contains_lines($output,$out)
        && output_does_not_contain_lines($output,$notout);        
    }
}

function hint($msg) {
    global $log;
    $log .= $msg;
}

// C++ source code checking library: this library requires a global PHP array called
// 'sources' that contains objects of type 'SourceCode' (defined in source_parser.php);
// auto_score.php provides this array (just like it provides other faculties used here)

function check_sources($var) {
    if (!is_array($var) || count($var)==0 || get_class($var[0])!='SourceCode')
        throw new Exception("scoring_functions.php: require global variable 'sources' with elements of type 'SourceCode'");
}

/* returns 'true' if the source has defined a function named '$name'; the optional arguments validate
   the function's signiture as well if they are specified; '$parameterTypes' is an array of type names */
function source_contains_function($name,$type = "",array $parameterTypes = null) {
    global $sources; check_sources($sources);
    $root = $sources[0]->get_parse_tree_root();

    // see if function is defined with specified name
    $results = $root->match_elements("CPPFunctionDefinition[$name]");
    if ( is_null($results) ) {
        hint("<b>Source should contain function named '$name'.</b>");
        return false;
    }
    // check the function return type
    else if ($type != "") {
        // the first CPPDeclSpecSeq in the function-def is the return type; we expect the type name
        // to be a single keyword token like 'int' or 'bool'
        $declSpec = $results[0]->match_elements("CPPDeclSpecSeq:KeywordToken[$type]");
        if ( is_null($declSpec) ) {
            hint("<b>Function '$name' must have return type '$type'.</b>");
            return false;
        }

        // check function parameter types
        if ( !is_null($parameterTypes) ) {
            $i = 0;
            $name = $results[0]->find_first('CPPParameterDeclarationClause')->find_next('CPPParameterDeclaration');
            while (!is_null($name) && current($parameterTypes)!==false) {
                // parse parameter type name
                $r = preg_match("/([_[:alnum:]]+)([\*&])?/",current($parameterTypes),$matches);
                if ($r == 0)
                    throw new Exception("scoring_functions.php: type name '"
                                        . current($parameterTypes) . "' is not supported in source_contains_function()");
                else if ($r === false)
                    throw new Exception("scoring_functions.php: bad regex in source_contains_function()");

                // compile search patterns for the program's parse tree; one is for the type name and the other
                // is for the declarator modifiers ('*' and '&' are supported)
                $expr = array("CPPDeclSpecSeq:KeywordToken[" . $matches[1] . "]", "CPPDeclarator:OperatorPunctuatorToken");
                if ( isset($matches[2]) ) {
                    // symbol names need to be mapped; the '*' and '&' modifiers live in the CPPDeclarator construct
                    $v = CPPElement::map_search_name($matches[2]);
                    $expr[1] .= "[$v]";
                }

                // see if the expected parameter type matches what's in the parse tree; (note: if !isset($matches[2]) then we want
                // match_elements to not find any declarator modifiers)
                $a = is_null($name->match_elements($expr[0]));
                $b = is_null($name->match_elements($expr[1]));
                $c = isset($matches[2]); // does the type have declarator modifiers?
                if ($a || ($b && $c) || (!$b && !$c))
                    break;

                // advance to next parameter type
                $name = $name->find_next('CPPParameterDeclaration');
                next($parameterTypes);
                ++$i;
            }

            if ($i < count($parameterTypes)) {
                ++$i;
                hint("<b>Function parameter types are incorrect; expected '" . current($parameterTypes) . "' for parameter $i.</b>");
                return false;
            }
        }
    }
    return true;
}

/* returns 'true' if the source has a recursive function; if $name is not empty, then
   the recursive function must be named "$name"; otherwise it can have any name */
function source_contains_recursive_function($name = "") {
    global $sources; check_sources($sources);

    $root = $sources[0]->get_parse_tree_root();
    $fn = $id = $name;
    if ($fn == "") {
        $fn = "fname~"; // create symbol with function name
        $id = "~fname"; // match identifier against created symbol
    }

    /* recursive call is identified by the following:
        - function call at some level within function body; this is a CPPPostfixExpression that contains an identifier (the function name)
          followed by an OperatorPunctuatorToken with the value '('
        - identifier used in function call is same as function definition name */
    $results = $root->match_elements("CPPFunctionDefinition[$fn]-CPPPostfixExpression:(IdentifierToken[$id] OperatorPunctuatorToken[~oparen])");
    if ( is_null($results) ) {
        if ($name != "")
            hint("<b>Source should contain recursive function named '$name'.</b>");
        else
            hint("<b>Source should contain recursive function.</b>");
        return false;
    }
    return true;
}

/* returns 'true' if the source does not have a recursive function */
function source_excludes_recursive_function($name = "") {
    global $sources; check_sources($sources);
    $root = $sources[0]->get_parse_tree_root();

    /* recursive call is identified by the following:
        - function call at some level within function body; this is a CPPPostfixExpression that contains an identifier (the function name)
          followed by an OperatorPunctuatorToken with the value '('
        - identifier used in function call is same as function definition name */
    $results = 
        $root->match_elements("CPPFunctionDefinition[func~]-CPPPostfixExpression:(IdentifierToken[~func] OperatorPunctuatorToken[~oparen])");
    if ( !is_null($results) ) {
        hint("<b>Source should not contain recursive function.</b>");
        return false;
    }
    return true;
}

/* returns true if the source contains at least one while loop */
function source_contains_while_loop() {
    global $sources; check_sources($sources);

    $root = $sources[0]->get_parse_tree_root();

    /* while loop is identified by the following:
        - KeywordToken with value "while" */
    if ( is_null($root->match_elements("KeywordToken[while]",'#')) ) {
        hint("<b>Source should contain while loop.</b>");
        return false;
    }
    return true;
}

/* returns true if the source is free of while loops */
function source_excludes_while_loop() {
    global $sources; check_sources($sources);

    $root = $sources[0]->get_parse_tree_root();

    /* while loop is identified by the following:
        - KeywordToken with value "while" */
    if ( !is_null($root->match_elements("KeywordToken[while]",'#')) ) {
        hint("<b>Source should not contain while loops.</b>");
        return false;
    }
    return true;
}

/* returns true if the source contains at least one for loop */
function source_contains_for_loop() {
    global $sources; check_sources($sources);

    $root = $sources[0]->get_parse_tree_root();

    /* for loop is identified by the following:
        - KeywordToken with value "for" */
    if ( is_null($root->match_elements("KeywordToken[for]",'#')) ) {
        hint("<b>Source should contain for loop.</b>");
        return false;
    }
    return true;
}

/* returns true if the source is free of for loops */
function source_excludes_for_loop() {
    global $sources; check_sources($sources);

    $root = $sources[0]->get_parse_tree_root();

    /* for loop is identified by the following:
        - KeywordToken with value "for" */
    if ( !is_null($root->match_elements("KeywordToken[for]",'#')) ) {
        hint("<b>Source should not contain for loops.</b>");
        return false;
    }
    return true;
}

/* returns true if the source contains at least one of either for or while loops */
function source_contains_loops() {
    global $sources; check_sources($sources);

    $root = $sources[0]->get_parse_tree_root();

    /* for loop is identified by the following:
        - KeywordToken with value "for" */
    if (is_null($root->match_elements("KeywordToken[for]",'#'))
        || is_null($root->match_elements("KeywordToken[while]",'#'))) {
        hint("<b>Source should contain iterative loop.</b>");
        return false;
    }
    return true;
}

/* returns true if the source is free of loops */
function source_excludes_loops() {
    return source_excludes_while_loop() && source_excludes_for_loop();
}

/* returns true if the source is free of the specified header file includes; a variable
   number of parameters may be passed to this function */
function source_excludes_libraries() {
    global $sources; check_sources($sources);

    $prepr = $sources[0]->get_preprocessor();
    foreach (func_get_args() as $header) {
        if ( $prepr->did_include_file($header) ) {
            hint("<b>Source should not include header '$header'.</b>");
            return false;
        }
    }
    return true;
}

/* like 'source_excludes_libraries' but uses C++ standard libraries 
   that should not be used by CS120 students */
function source_excludes_standard_libraries() {
    return source_excludes_libraries("algorithm","array","bitset","deque","forward_list","list","map",
                                     "queue","set","stack","unordered_map","unordered_set","vector",
                                     "mutex","thread","complex","functional","iterator","numeric",
                                     "utility");
}
