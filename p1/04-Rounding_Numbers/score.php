<?php

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
function given() {
    return run_and_check("3.2\n","The rounded number is 3","The rounded number is 4")
       &&  run_and_check("3.7\n","The rounded number is 4","The rounded number is 3")
       &&  run_and_check("-3.2\n","The rounded number is -3","The rounded number is -4")
       &&  run_and_check("-3.7\n","The rounded number is -4","The rounded number is -3");
}

/** @test
    @score  0 
    @prereq given */
function standard() { 
    return run_and_check("17.3\n","The rounded number is 17","The rounded number is 18") 
       &&  run_and_check("17.8\n","The rounded number is 18","The rounded number is 17")
       &&  run_and_check(" 9.6\n","The rounded number is 10","The rounded number is 9" )
       &&  run_and_check("23.5\n","The rounded number is 24","The rounded number is 23")
       &&  run_and_check(" 2.9\n","The rounded number is 3", "The rounded number is 2" )
       &&  run_and_check(" 4.0\n","The rounded number is 4", "The rounded number is 3" )
       &&  run_and_check("44.4\n","The rounded number is 44","The rounded number is 45")
       &&  run_and_check("77.7\n","The rounded number is 78","The rounded number is 77")
       &&  run_and_check("11.2\n","The rounded number is 11","The rounded number is 12");
}

/** @test
    @score  0
    @prereq standard */
function random() {
    $continue = true;
    for( $i = 0; $i < 50 && $continue; $i++ ) {
        $num = rand(-100,100) + rand(0,500)/rand(200,800);
        $ans = round($num); $nans = $ans+1;
        $continue = run_and_check("$num\n","The rounded number is $ans","The rounded number is $nans");
    }
    return $continue;
}

/** @test
    @prereq random
    @score  1.0 */
function points()
{
    return _count_case(true);
}

include 'scoring_functions.php';
include 'auto_score.php';

