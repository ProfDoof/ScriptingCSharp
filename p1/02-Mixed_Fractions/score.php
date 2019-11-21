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
function case01() {
    // Sample input
    $in  = "14\n5\n";
    $out = "2 4/5";

    return run_and_check($in,$out,"");
}
    
/** @test
 @score  0
 @prereq compiles */
function case02() {
    // Sample input
    $in  = "15\n2\n";
    $out = "7 1/2";

    return run_and_check($in,$out,"");
}

/** @test
    @score  0
    @prereq case01 case02 */
function case03() {
    $in  = "26\n4\n";
    $out = "6 2/4";

    return run_and_check($in,$out,"");
}
    
/** @test
 @score  0
 @prereq case01 case02 */
function case04() {
    $in  = "15\n4\n";
    $out = "3 3/4";

    return run_and_check($in,$out,"");
}

/** @test
    @score  0
    @prereq case03 case04 */
function case05() {
    $in  = "31\n11\n";
    $out = "2 9/11";

    return run_and_check($in,$out,"");
}
    
/** @test
 @score  0
 @prereq case03 case04 */
function case06() {
    $in  = "90\n16\n";
    $out = "5 10/16";

    return run_and_check($in,$out,"");
}

/** @test
    @score  0
    @prereq case05 case06 */
function case07() {
    // Sample input
    $in  = "12\n4\n";
    $out = "3 0/4";

    return run_and_check($in,$out,"");
}
    
/** @test
 @score  0
 @prereq case05 case06 */
function case08() {
    $in  = "30\n6\n";
    $out = "5 0/6";

    return run_and_check($in,$out,"");
}

/** @test
    @score  0
    @prereq case05 case06 */
function case09() {
    // Sample input
    $in  = "2\n3\n";
    $out = "0 2/3";

    return run_and_check($in,$out,"");
}
    
/** @test
 @score  0
 @prereq case05 case06 */
function case10() {
    $in  = "5\n12\n";
    $out = "0 5/12";

    return run_and_check($in,$out,"");
}

/** @test
    @prereq case07 case08 case09 case10
    @score  1.0 */
function points()
{
    return _count_case(true);
}


include 'scoring_functions.php';
include 'auto_score.php';

