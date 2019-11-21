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
    @score  0.05 */
// Compile program exactly as submitted by student, check for function declaration
function compiles1() { 
    global $files;
    global $cflags;
    $source = file_get_contents($files[0]);
    //$expr = "/float\s+update_balance\s*\(\s*float\s+balance,\s*float\s+rate\s*\)/";
    return compile("g++ $cflags -I. $files[0] -o prog") && source_contains_function("update_balance","float",array("float","float"));
    //&&  source_contains_regex($source,$expr,"function declaration: float update_balance(float balance, float rate)"); 
}

/** @test
    @score  0.05
    @prereq compiles1 */
function format1() { 
    return run("prog","5000\n3\n",$output)
    && output_contains_lines($output,<<<END
Starting balance? <span class=input>5000</span>
Interest rate? <span class=input>3</span>

Balance after one year: $5150.00
Balance after two years: $5304.50
Balance after three years: $5463.63
END
); 
}

/** @test
    @score  0.05
    @prereq compiles1 */
function format2() { 
    return run("prog","9500\n1.25\n",$output)
    && output_contains_lines($output,<<<END
Starting balance? <span class=input>9500</span>
Interest rate? <span class=input>1.25</span>

Balance after one year: $9618.75
Balance after two years: $9738.98
Balance after three years: $9860.72
END
); 
}

/** @test
    @score  0.05
    @prereq compiles1 */
function format3() { 
    return run("prog","7200\n0.9\n",$output)
    && output_contains_lines($output,<<<END
Starting balance? <span class=input>7200</span>
Interest rate? <span class=input>0.9</span>

Balance after one year: $7264.80
Balance after two years: $7330.18
Balance after three years: $7396.15
END
); 
}

/** @test
    @score  0
    @prereq format1 format2 format3 */
// Recompile with extra code to test function directly
function compiles2() { 
    global $files;
    global $cflags;
    $source = "#include <iomanip>\n".file_get_contents($files[0]);
    file_put_contents("modified.cpp",$source.<<<EOF

int function_test()
{
    float balance, rate;
    cin >> balance >> rate;
    cout << fixed << setprecision(2);
    cout << update_balance(balance,rate);
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
function check1() {
    return test_function("7330.18\n0.9\n","(7330.18,0.9)","7396.15");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check2() {
    return test_function("5304.20\n3\n","(5304.20,3)","5463.33");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check3() {
    return test_function("10306\n2.7\n","(10305,2.7)","10584.26");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check4() {
    return test_function("11006.35\n1.8\n","(11006.35,1.8)","11204.46");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check5() {
    return test_function("21300\n2.125\n","(21300,2.125)","21752.62");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check6() {
    return test_function("84.56\n1.1\n","(84.56,1.1)","85.49");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check7() {
    return test_function("999999.99\n1\n","(999999.99,1)","1010000.00");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check8() {
    return test_function("67200\n2.2\n","(67200,2.2)","68678.40");
}

include 'scoring_functions.php';
include 'auto_score.php';
