<?php

// cm = ((ft*12 + in)*2.54)

function verify_output($in,$out) {
    return run("prog",$in,$output)
    && output_contains_lines($output,"The length is $out cm."); 
}

function test_function($in,$args,$out) {
    run("mod",$in,$output);
    if (strpos($output,$out) === false) {
        _append_log("<b>Function returned incorrect value.</b></br>Given arguments $args, the answer should be $out</br>");
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
    //$expr = "/float\s+convert\s*\(\s*float\s+feet,\s*float\s+inches\s*\)/";
    return compile("g++ $cflags -I. $files[0] -o prog") && source_contains_function("convert","float",array("float","float"));
    //&&  source_contains_regex($source,$expr,"function declaration: <b>float convert(float feet, float inches)"); 
}

/** @test
    @score 0.15 */
function check_original() {
    return verify_output("2\n2\n",66.04)
       &&  verify_output("1\n11\n",58.42)
       &&  verify_output("9\n9\n",297.18);
}

/** @test
    @score  0
    @prereq check_original */
// Recompile with extra code to test function directly
function compiles2() { 
    global $files;
    global $cflags;
    $source = file_get_contents($files[0]);
    file_put_contents("modified.cpp","#include<cstdlib>\n".$source.<<<EOF

int function_test()
{
    float ft, in;
    cin >> ft >> in;
    cout << convert(ft,in);
    exit(0);
}

static int __init=function_test();
EOF
); 
    //print(file_get_contents("modified.cpp")); print("\n\n");
    return compile("g++ $cflags -I. modified.cpp -o mod");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function convert1() {
    return test_function("1\n0\n","(1,0)","30.48");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function convert2() {
    return test_function("0\n1\n","(0,1)","2.54");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function convert3() {
    return test_function("2\n3\n","(2,3)","68.58");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function convert4() {
    return test_function("3\n1\n","(3,1)","93.98");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function convert5() {
    return test_function("8\n4\n","(8,4)","254");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function convert6() {
    return test_function("6.3\n2.1\n","(6.3,2.1)","197.358");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function convert7() {
    return test_function("0.1\n0\n","(0.1,0)","3.048");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function convert8() {
    return test_function("2\n1.4\n","(2,1.4)","64.516");
}

include 'scoring_functions.php';
include 'auto_score.php';
