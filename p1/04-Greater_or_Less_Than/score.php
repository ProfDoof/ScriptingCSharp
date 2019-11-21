<?php

include 'scoring_functions.php';
include 'auto_score.php';

function verify_output($in,$out) {
    return run("prog",$in,$output)
    && output_contains_lines($output,"$out"); 
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
	return 	verify_output("6\n8\n","6 is less than 8","8 is less than 6") &&
			verify_output("5\n2\n","5 is greater than 2","2 is greater than 5");
}

/** @test
    @score  0 
    @prereq compiles standard1 */
function standard2() { 
	$a = rand(1,10);
	$b = rand(1,10); if( $b == $a ) $b = $b - 1;
	$c = rand(1,10);
	$d = rand(1,10); if( $d == $c ) $d = $d + 1;
	function greaterorless($one,$two){
		if($one > $two){
			return "$one is greater than $two"; 
		}
		else{
			return "$one is less than $two";	
		}
	}
	$ab = greaterorless($a,$b);
	$cd = greaterorless($c,$d);
	
	return 	verify_output("$a\n$b\n",$ab,"") &&
			verify_output("$c\n$d\n",$cd,"");
}

/** @test
    @prereq standard2
    @score  1.0 */
function points()
{
    return _count_case(true);
}