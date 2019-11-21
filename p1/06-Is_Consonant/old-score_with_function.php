<?php

function test_function($in,$out) {
    run("mod",$in,$output);
    $in = trim($in); // remove endline character
    if (strpos($output,$out) === false) {
        _append_log("<b>Function returned incorrect value for input '$in'.</b></br>");
        return false;
    }
    return true;
}

/** @test
    @score  0.05 */
// Compile program exactly as submitted by student, check for function declaration
function compiles1() { 
    global $files;
    global $cflags;
    $source = file_get_contents($files[0]);
    //$expr = "/bool\s+IsConsonant\s*\(\s*char\s+ch\s*\)/";
    return compile("g++ $cflags -I. $files[0] -o prog") && source_contains_function("IsConsonant","bool",array("char"));
    //&&  source_contains_regex($source,$expr,"function declaration: <b>bool IsConsonant(char ch)</b>"); 
}

/** @test
    @score  0.05
    @prereq compiles1 */
function main() { 
    return true;
/*
    return ( run("prog","J",$output) && output_contains_lines($output,"J is a consonant.")  )
       &&  ( run("prog","o",$output) && output_contains_lines($output,"o is not a consonant.") ); 
*/
}

/** @test
    @score  0
    @prereq main */
// Recompile with extra code to test function directly
function compiles2() { 
    global $files;
    global $cflags;
    $source = file_get_contents($files[0]);
    file_put_contents("modified.cpp",$source.<<<EOF

int function_test()
{
    char letter;
    cin >> letter;
    cout << ( IsConsonant(letter) ? "true" : "false" );
    exit(0);
}

static int __init=function_test();
EOF
); 
    //print(file_get_contents("modified.cpp")); print("\n\n");
    return compile("g++ $cflags -I. modified.cpp -o mod");
}

/** @test
    @score  0.20
    @prereq compiles2 */
function uppercase_consonants() {
    return test_function("B\n","true")
       &&  test_function("C\n","true")
       &&  test_function("D\n","true")
       &&  test_function("F\n","true")
       &&  test_function("G\n","true")
       &&  test_function("H\n","true")
       &&  test_function("J\n","true")
       &&  test_function("K\n","true")
       &&  test_function("L\n","true")
       &&  test_function("M\n","true")
       &&  test_function("N\n","true")
       &&  test_function("P\n","true")
       &&  test_function("Q\n","true")
       &&  test_function("R\n","true")
       &&  test_function("S\n","true")
       &&  test_function("T\n","true")
       &&  test_function("V\n","true")
       &&  test_function("W\n","true")
       &&  test_function("X\n","true")
       &&  test_function("Y\n","true")
       &&  test_function("Z\n","true");
}

/** @test
    @score  0.20
    @prereq compiles2 */
function uppercase_vowels() {
    return test_function("A\n","false")
       &&  test_function("E\n","false")
       &&  test_function("I\n","false")
       &&  test_function("O\n","false")
       &&  test_function("U\n","false");
}

/** @test
    @score  0.20
    @prereq uppercase_consonants uppercase_vowels */
function lowercase_consonants() {
    return test_function("B\n","true")
       &&  test_function("C\n","true")
       &&  test_function("D\n","true")
       &&  test_function("F\n","true")
       &&  test_function("G\n","true")
       &&  test_function("H\n","true")
       &&  test_function("J\n","true")
       &&  test_function("K\n","true")
       &&  test_function("L\n","true")
       &&  test_function("M\n","true")
       &&  test_function("N\n","true")
       &&  test_function("P\n","true")
       &&  test_function("Q\n","true")
       &&  test_function("R\n","true")
       &&  test_function("S\n","true")
       &&  test_function("T\n","true")
       &&  test_function("V\n","true")
       &&  test_function("W\n","true")
       &&  test_function("X\n","true")
       &&  test_function("Y\n","true")
       &&  test_function("Z\n","true");
}

/** @test
    @score  0.20
    @prereq uppercase_consonants uppercase_vowels */
function lowercase_vowels() {
    return test_function("a\n","false")
       &&  test_function("e\n","false")
       &&  test_function("i\n","false")
       &&  test_function("o\n","false")
       &&  test_function("u\n","false");
}

/** @test
    @score  0.10
    @prereq lowercase_consonants lowercase_vowels */
function non_letter() {
    return test_function("7\n","false")
       &&  test_function("$\n","false")
       &&  test_function("!\n","false")
       &&  test_function("2\n","false")
       &&  test_function(">\n","false");
}

include 'scoring_functions.php';
include 'auto_score.php';
