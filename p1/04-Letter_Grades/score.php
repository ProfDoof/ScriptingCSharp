<?php

include 'scoring_functions.php';
include 'auto_score.php';


function letter_grade($a){
	if($a >= 90){
		return "Grade: A";
	} elseif($a >= 80){
		return "Grade: B";
	} elseif($a >= 70){
		return "Grade: C";
	} elseif($a >= 60){
		return "Grade: D";
	} else {
		return "Grade: F";
	}
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
/** @test
    @score  0
    @prereq compiles */
function standard1() {
	$x = 82;
	$y = 53;
	return run_and_check("$x\n",letter_grade($x),"")
		&& run_and_check("$y\n",letter_grade($y),"");
}

/** @test
    @score  0
    @prereq compiles */
function standard2() {
	$x = 100;
	$y = 0;
	return run_and_check("$x\n",letter_grade($x),"")
		&& run_and_check("$y\n",letter_grade($y),"");
}

/** @test
    @score  0
    @prereq compiles */
function standard3() {
	$x = 90;
	$y = 80;
	return run_and_check("$x\n",letter_grade($x),"")
		&& run_and_check("$y\n",letter_grade($y),"");
}

/** @test
    @score  0
    @prereq compiles */
function random1() {
	$x = rand(70,100);
	$y = rand(70,100);
	return run_and_check("$x\n",letter_grade($x),"")
		&& run_and_check("$y\n",letter_grade($y),"");
}

/** @test
    @score  0
    @prereq compiles */
function random2() {
	$x = rand(50,100);
	$y = rand(50,100);
	return run_and_check("$x\n",letter_grade($x),"")
		&& run_and_check("$y\n",letter_grade($y),"");
}

/** @test
    @prereq standard1 standard2 random1 random2
    @score  1.0 */
function points()
{
    return _count_case(true);
}
