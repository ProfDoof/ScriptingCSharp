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
    return run_and_check("0\n","Yes, zero was provided as input.","")
       &&  run_and_check("68\n","","Yes, zero was provided as input.")
       &&  run_and_check("99\n","","Yes, zero was provided as input.");
}

/** @test
    @prereq standard1
    @score  1.0 */
function points()
{
    return _count_case(true);
}
