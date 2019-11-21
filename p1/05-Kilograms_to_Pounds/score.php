<?php
	
function test_function($in,$arg,$out) {
	run("mod",$in,$output);
	if (strpos($output,$out) === false) {
		_append_log("<b>Function returned incorrect value.</b></br>For $arg kilograms, the results should be $out</br>");
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
	//$expr = "/void\s+convert\s*\(\s*int\s+kilograms,\s*\s*int\s*&\s*pounds,\s*\s*float\s*&\s*ounces\s*\)/";
	return compile("g++ $cflags -I. $files[0] -o prog") && source_contains_function("convert","void",array("int","int&","float&"));
    //&&  source_contains_regex($source,$expr,"function declaration: <b>void convert(int kilograms, int& pounds, float& ounces)"); 
}

/** @test
 @score  0.05
 @prereq compiles1 */
function format1() { 
	return run("prog","1\n",$output)
	&& output_contains_lines($output,<<<END
Kilograms: <span class=input>1</span>

1 kilograms is 2 pounds and 3.2 ounces.
END
);
}

/** @test
 @score  0.05
 @prereq compiles1 */
function format2() { 
	return run("prog","8\n",$output)
	&& output_contains_lines($output,<<<END
Kilograms: <span class=input>8</span>

8 kilograms is 17 pounds and 9.6 ounces.
END
);
}

/** @test
 @score  0.05
 @prereq compiles1 */
function format3() { 
	return run("prog","5\n",$output)
	&& output_contains_lines($output,<<<END
Kilograms: <span class=input>5</span>

5 kilograms is 11 pounds and 0.0 ounces.
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
    int kg, lb;
    float oz;
    cin >> kg;
    convert(kg,lb,oz);
    cout << fixed << setprecision(1);
    cout << kg << " kilograms is " << lb << " pounds and " << oz << " ounces." << endl;
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
    return test_function("31\n","31","68 pounds and 3.2 ounces");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check2() {
    return test_function("2\n","2","4 pounds and 6.4 ounces");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check3() {
    return test_function("19\n","19","41 pounds and 12.8 ounces");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check4() {
    return test_function("41\n","41","90 pounds and 3.2 ounces");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check5() {
    return test_function("19\n","19","41 pounds and 12.8 ounces");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check6() {
    return test_function("17\n","17","37 pounds and 6.4 ounces");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check7() {
    return test_function("1001\n","1001","2202 pounds and 3.2 ounces");
}

/** @test
    @score  0.1
    @prereq compiles2 */
function check8() {
    return test_function("999\n","999","2197 pounds and 12.8 ounces");
}

include 'scoring_functions.php';	
include 'auto_score.php';
