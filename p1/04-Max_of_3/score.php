<?php
include 'scoring_functions.php';
include 'auto_score.php';


function max_of_three($a,$b,$c){
	$output = "The largest number is " + strval(max($a,$b,$c)) + ".";
	return $output;
}

/** @test
    @score  0 */
function compiles() {
    global $files;
    global $cflags;
    $source = file_get_contents($files[0]);
    return compile("g++ $cflags -I. $files[0] -o prog")
       &&  check_blacklist($source);
}

//------------------------------------------------------------------------------
// For A < B < C, check 6 permutations: ABC, ACB, BAC, BCA, CAB, CBA

/** @test
    @score  0 
    @prereq compiles */
function perm1() {
	$a = rand(1,10);
	$b = $a * rand(1,4) + rand(1,10);
	$c = $b * rand(1,4) + rand(1,10);
	return run_and_check("$a\n$b\n$c\n","The largest number is $c.","The largest number is $a.");
}

/** @test
    @score  0 
    @prereq compiles */
function perm2() {
	$a = rand(1,10);
	$b = $a * rand(1,4) + rand(1,10);
	$c = $b * rand(1,4) + rand(1,10);
	return run_and_check("$a\n$c\n$b\n","The largest number is $c.","The largest number is $a.");
}

/** @test
    @score  0 
    @prereq compiles */
function perm3() {
	$a = rand(1,10);
	$b = $a * rand(1,4) + rand(1,10);
	$c = $b * rand(1,4) + rand(1,10);
	return run_and_check("$b\n$a\n$c\n","The largest number is $c.","The largest number is $a.");
}

/** @test
    @score  0 
    @prereq compiles */
function perm4() {
	$a = rand(1,10);
	$b = $a * rand(1,4) + rand(1,10);
	$c = $b * rand(1,4) + rand(1,10);
	return run_and_check("$b\n$c\n$a\n","The largest number is $c.","The largest number is $a.");	
}

/** @test
    @score  0 
    @prereq compiles */
function perm5() {
	$a = rand(1,10);
	$b = $a * rand(1,4) + rand(1,10);
	$c = $b * rand(1,4) + rand(1,10);
	return run_and_check("$c\n$a\n$b\n","The largest number is $c.","The largest number is $a.");	
}

/** @test
    @score  0 
    @prereq compiles */
function perm6() {
	$a = rand(1,10);
	$b = $a * rand(1,4) + rand(1,10);
	$c = $b * rand(1,4) + rand(1,10);
	return run_and_check("$c\n$b\n$a\n","The largest number is $c.","The largest number is $a.");	
}

/** @test
    @score  0
    @prereq perm1 perm2 perm3 perm4 perm5 perm6 */
function distinct_values() {
	// If prereqs met, passed all permutations of distinct values
	return true;
}

//------------------------------------------------------------------------------
// Now check all permutations with two or more equal: 
//	A = B < C -> ABC, ACB, CAB
//	A < B = C -> ABC, BAC, BCA

/** @test
    @score  0 
    @prereq distinct_values */
function equal1() {
	$a = rand(1,10);
	$b = $a;
	$c = $b * rand(1,4) + rand(1,10);
	return run_and_check("$a\n$b\n$c\n","The largest number is $c.","The largest number is $a.");	
}

/** @test
    @score  0 
    @prereq distinct_values */
function equal2() {
	$a = rand(1,10);
	$b = $a;
	$c = $b * rand(1,4) + rand(1,10);
	return run_and_check("$a\n$c\n$b\n","The largest number is $c.","The largest number is $a.");	
}

/** @test
    @score  0 
    @prereq distinct_values */
function equal3() {
	$a = rand(1,10);
	$b = $a;
	$c = $b * rand(1,4) + rand(1,10);
	return run_and_check("$c\n$a\n$b\n","The largest number is $c.","The largest number is $a.");	
}

/** @test
    @score  0 
    @prereq distinct_values */
function equal4() {
	$a = rand(1,10);
	$b = $a * rand(1,4) + rand(1,10);
	$c = $b;
	return run_and_check("$a\n$b\n$c\n","The largest number is $c.","The largest number is $a.");	
}

/** @test
    @score  0 
    @prereq distinct_values */
function equal5() {
	$a = rand(1,10);
	$b = $a * rand(1,4) + rand(1,10);
	$c = $b;
	return run_and_check("$b\n$a\n$c\n","The largest number is $c.","The largest number is $a.");	
}

/** @test
    @score  0 
    @prereq distinct_values */
function equal6() {
	$a = rand(1,10);
	$b = $a * rand(1,4) + rand(1,10);
	$c = $b;
	return run_and_check("$b\n$c\n$a\n","The largest number is $c.","The largest number is $a.");	
}

/** @test
    @score  0
    @prereq equal1 equal2 equal3 equal4 equal5 equal6 */
function two_equal_values() {
	// If prereqs met, passed all permutations of with two equal values
	return true;
}

//------------------------------------------------------------------------------
// Now check unusual input values: all equal, all negative, all zero

/** @test
    @score  0 
    @prereq two_equal_values */
function all_equal() {
	$a = rand(25,75);
	$b = $a;
	$c = $a;
	return run_and_check("$a\n$b\n$c\n","The largest number is $a.","");	
}

/** @test
    @score  0 
    @prereq two_equal_values */
function all_zero() {
	$a = 0;
	$b = 0;
	$c = 0;
	return run_and_check("$a\n$b\n$c\n","The largest number is $a.","");	
}


/** @test
    @score  0 
    @prereq two_equal_values */
function all_negative() {
	$a = -1 * rand(45,75);
	$b = -1 * rand(20,40);
	$c = $a + $b + rand(1,15);
	return run_and_check("$a\n$b\n$c\n","The largest number is $b.","The largest number is $c.");	
}

/** @test
    @prereq all_equal all_zero all_negative
    @score  1.0 */
function points()
{
    return _count_case(true);
}

