<?php

include 'scoring_functions.php';
include 'auto_score.php';

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
	$in  = array("5\n2\n", "7\n8\n", "14\n14\n");
	$out = array("5 is greater than 2", "7 is less than 8", "14 is equal to 14");

	return run_and_check($in[0],$out[0],"") 
		&& run_and_check($in[1],$out[1],"")	
		&& run_and_check($in[2],$out[2],"");	
}
/** @test
    @score  0
    @prereq standard1 */
function format1() {
	return run_and_check("5\n2\n","This program determines the relationship between two input numbers.\nEnter the first integer: <span class=input>5</span>\nEnter the second integer: <span class=input>2</span>\n5 is greater than 2","");
}	

/** @test
    @score  0
    @prereq compiles standard1 format1*/
function random1() { 
	$a = rand(1,10);
	$b = rand(1,10); 
	$c = rand(1,10);
	$d = rand(1,10);
	function greaterorless($one,$two){
		if($one > $two){
			return "$one is greater than $two"; 
		}
		else if ($one == $two) {
			return "$one is equal to $two";
		}{
			return "$one is less than $two";	
		}
	}
	$ab = greaterorless($a,$b);
	$cd = greaterorless($c,$d);
	
	return 	run_and_check("$a\n$b\n",$ab,"") &&
			run_and_check("$c\n$d\n",$cd,"");
}

/** @test
    @prereq random1
    @score  1.0 */
function points()
{
    return _count_case(true);
}