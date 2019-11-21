<?php

//TODO
//add the test input as 33, to pick up on an edge case when the person may be doing integer division
	
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
	$expr = "/int\s+FahrenheitToCelsius\s*\(\s*int\s+fahrenheit\s*\)/";
	return compile("g++ $cflags -I. $files[0] -o prog")
	&&  source_contains_regex($source,$expr,"function declaration: <b>int FahrenheitToCelsius( int fahrenheit )"); 
}
	
/** @test
 @score  0.05
 @prereq compiles1 */
function format1() { 
	return run("prog","212 32\n",$output)
	&& output_contains_lines($output,<<<END
Enter high and low temperatures (Fahrenheit): <span class=input>212</span> <span class=input>32</span>

High (Celsius): 100
Low (Celsius): 0
END
);
}

/** @test
 @score  0.05
 @prereq compiles1 */
function format2() { 
	return run("prog","80 60\n",$output)
	&& output_contains_lines($output,<<<END
Enter high and low temperatures (Fahrenheit): <span class=input>80</span> <span class=input>60</span>

High (Celsius): 26
Low (Celsius): 15
END
);
}

/** @test
 @score  0.05
 @prereq compiles1 */
function format3() { 
	return run("prog","100 0\n",$output)
	&& output_contains_lines($output,<<<END
Enter high and low temperatures (Fahrenheit): <span class=input>100</span> <span class=input>0</span>

High (Celsius): 37
Low (Celsius): -17
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
    $source = file_get_contents($files[0]);
    file_put_contents("modified.cpp","#include<cstdlib>\n".$source.<<<EOF

int function_test()
{
    int F;
    cin >> F;
    cout << FahrenheitToCelsius(F) << endl;
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
    return test_function("100\n","(100)","37");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check2() {
    return test_function("94\n","(94)","34");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check3() {
    return test_function("13\n","(13)","-10");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check4() {
    return test_function("77\n","(77)","25");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check5() {
    return test_function("45\n","(45)","7");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check6() {
    return test_function("105\n","(105)","40");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check7() {
    return test_function("60\n","(60)","15");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check8() {
    return test_function("83\n","(83)","28");
}
	
include 'auto_score.php';
include 'scoring_functions.php';