<?php

function test_function($in,$args,$out) {
    run("mod",$in,$output);
    if (strpos($output,$out) === false) {
        _append_log("<b>Function returned incorrect value.</b></br>Given arguments $args, the return value should be $out</br>");
        return false;
    }
    return true;
}

/** @test
    @score  0 */
// Compile program exactly as submitted by student, check for function declaration
function compiles1() { 
    global $files;
    global $cflags;
    $source = file_get_contents($files[0]);
    //$expr = "/int\s+round\s*\(\s*int\s+number\s*,\s*int\s+unit\s*\)/";
    return compile("g++ $cflags -I. $files[0] -o prog") && source_contains_function("round","int",array("int","int"));
    //&&  source_contains_regex($source,$expr,"function declaration: int round(int number, int unit)"); 
}

/** @test
    @score  0
    @prereq compiles1 */
function format1() { 
    return run("prog","5127\n",$output)
    && output_contains_lines($output,<<<END
Enter an integer: <span class=input>5127</span>

Round to ten: 5120
Round to hundred: 5100
Round to thousand: 5000
END
); 
}

/** @test
    @score  0
    @prereq compiles1 */
function format2() { 
    return run("prog","893\n",$output)
    && output_contains_lines($output,<<<END
Enter an integer: <span class=input>893</span>

Round to ten: 890
Round to hundred: 800
Round to thousand: 0
END
); 
}

/** @test
    @score  0
    @prereq format1 format2 */
// Recompile with extra code to test function directly
function compiles2() { 
    global $files;
    global $cflags;
    //$source = "#include <iomanip>\n".file_get_contents($files[0]);
    $source = file_get_contents($files[0]);
    file_put_contents("modified.cpp","#include<cstdlib>\n".$source.<<<EOF

int function_test()
{
    int n, u;
    cin >> n >> u;
    cout << '[' << round(n,u) << ']' << endl;
    exit(0);
}

static int __init=function_test();

EOF

); 
    //print(file_get_contents("modified.cpp")); print("\n\n");
    return compile("g++ $cflags -I. modified.cpp -o mod");
}

/** @test
    @score  0
    @prereq compiles2 */
function check1() {
    return test_function("5127\n10\n","(5127,10)","5120");
}

/** @test
    @score  0
    @prereq compiles2 */
function check2() {
    return test_function("5127\n100\n","(5127,100)","5100");
}

/** @test
    @score  0
    @prereq compiles2 */
function check3() {
    return test_function("5127\n1000\n","(5127,1000)","5000");
}

/** @test
    @score  0
    @prereq check1 check2 check3 */
function check4() {
    return test_function("893\n10\n","(893,10)","890");
}

/** @test
    @score  0
    @prereq check1 check2 check3 */
function check5() {
    return test_function("893\n100\n","(893,100)","800");
}

/** @test
    @score  0
    @prereq check1 check2 check3 */
function check6() {
    return test_function("893\n1000\n","(893,1000)","0");
}

/** @test
    @score  0
    @prereq check4 check5 check6 */
function check7() {
    return test_function("55\n4\n","(55,4)","52");
}

/** @test
    @score  0
    @prereq check4 check5 check6 */
function check8() {
    return test_function("99\n3\n","(99,3)","99");
}

/** @test
    @score  0
    @prereq check4 check5 check6 */
function check9() {
    return test_function("100\n7\n","(100,7)","98");
}

/** @test
    @score  0
    @prereq check7 check8 check9 */
function check10() {
    return test_function("7\n85\n","(7,85)","0");
}

/** @test
    @score  0
    @prereq check7 check8 check9 */
function check11() {
    return test_function("10\n4\n","(10,4)","8");
}

/** @test
    @score  0
    @prereq check7 check8 check9 */
function check12() {
    return test_function("38\n37\n","(38,37)","37");
}

/** @test
    @score  0
    @prereq check10 check11 check12 */
function check13() {
    return test_function("55\n7\n","(55,7)","49");
}

/** @test
    @score  0
    @prereq check10 check11 check12 */
function check14() {
    return test_function("50\n1\n","(50,1)","50");
}

/** @test
    @score  0
    @prereq check10 check11 check12 */
function check15() {
    return test_function("1800\n101\n","(1800,101)","1717");
}

/** @test
    @score  0
    @prereq check13 check14 check15 */
function check16() {
    return test_function("0\n20\n","(0,20)","0");
}

/** @test
    @score  0
    @prereq check13 check14 check15 */
function check17() {
    return test_function("61324\n30\n","(61324,30)","61320");
}

/** @test
    @score  0
    @prereq check13 check14 check15 */
function check18() {
    return test_function("1423\n31\n","(1423,31)","1395");
}

/** @test
    @score 1.0
    @prereq check16 check17 check18 */
function points() {
    return _count_case(true);
}

include 'scoring_functions.php';
include 'auto_score.php';
