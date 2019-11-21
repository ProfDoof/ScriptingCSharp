<?php

/** @test
    @score  0 */
function compiles() { 
    global $files;
    global $cflags;
    return compile("g++ $cflags -I. $files[0] -o prog"); 
}

// ************************************************************************
//  block1: 
//   All digits 0..9
// ************************************************************************


/** @test
    @score 0
    @prereq compiles */
function check01() {
    return run_and_check( "2\n5\n", "2 + 5 = 7","");
}

/** @test
    @score 0
    @prereq compiles */
function check02() {
    return run_and_check( "3\n9\n", "3 + 9 = 12","2 + 5 = 7");
}

/** @test
    @score 0
    @prereq compiles */
function check03() {
    return run_and_check( "6\n6\n", "6 + 6 = 12","3 + 9 = 12");
}

/** @test
    @score 0
    @prereq compiles */
function check04() {
    return run_and_check( "7\n3\n", "7 + 3 = 10","6 + 6 = 12");
}

/** @test
    @score 0
    @prereq compiles */
function check05() {
    return run_and_check( "1\n0\n", "1 + 0 = 1","7 + 3 = 10");
}

/** @test
    @score 0
    @prereq check01 check02 check03 check04 check05 */
function block1() {
    return true;
}

// ************************************************************************
//  block2: 
//   One or both digits a..f
// ************************************************************************

/** @test
    @score 0
    @prereq block1 */
function check06() {
    return run_and_check( "3\nc\n", "3 + c = 15","");
}

/** @test
    @score 0
    @prereq block1 */
function check07() {
    return run_and_check( "1\na\n", "1 + a = 11","3 + c = 15");
}

/** @test
    @score 0
    @prereq block1 */
function check08() {
    return run_and_check( "f\ne\n", "f + e = 29","1 + a = 11");
}

/** @test
    @score 0
    @prereq block1 */
function check09() {
    return run_and_check( "d\n9\n", "d + 9 = 22","f + e = 29");
}

/** @test
    @score 0
    @prereq block1 */
function check10() {
    return run_and_check( "a\na\n", "a + a = 20","d + 9 = 22");
}

/** @test
    @score 0
    @prereq block1 */
function check11() {
    return run_and_check( "0\ne\n", "0 + e = 14","a + a = 20");
}

/** @test
    @score 0
    @prereq block1 */
function check12() {
    return run_and_check( "d\nb\n", "d + b = 24","0 + e = 14");
}

/** @test
    @score 0
    @prereq block1 */
function check13() {
    return run_and_check( "b\n2\n", "b + 2 = 13","d + b = 24");
}

/** @test
    @score 0
    @prereq block1 */
function check14() {
    return run_and_check( "a\nf\n", "a + f = 25","b + 2 = 13");
}

/** @test
    @score 0
    @prereq block1 */
function check15() {
    return run_and_check( "1\nf\n", "1 + f = 16","a + f = 25");
}

/** @test
    @score 0
    @prereq block1 */
function check16() {
    return run_and_check( "d\n5\n", "d + 5 = 18","1 + f = 16");
}

/** @test
    @score 0
    @prereq block1 */
function check17() {
    return run_and_check( "e\nc\n", "e + c = 26","d + 5 = 18");
}

/** @test
    @score 0
    @prereq block1 */
function check18() {
    return run_and_check( "3\nb\n", "3 + b = 14","e + c = 26");
}

/** @test
    @score 0
    @prereq block1 */
function check19() {
    return run_and_check( "e\n5\n", "e + 5 = 19","3 + b = 14");
}

/** @test
    @score 0
    @prereq block1 */
function check20() {
    return run_and_check( "9\nd\n", "9 + d = 22","e + 5 = 19");
}

/** @test
    @prereq check06 check07 check08 check09 check10 check11 check12 check13 check14 check15 check16 check17 check18 check19 check20
    @score  1.0 */
function points()
{
    return _count_case(true);
}

include 'scoring_functions.php';
include 'auto_score.php';


