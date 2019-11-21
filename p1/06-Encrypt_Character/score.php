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
//   Lowercase letters, no wrap
// ************************************************************************


/** @test
    @score 0
    @prereq compiles */
function check01() {
    return run_and_check( "a\n1\n", "Result: B","Result: J");
}

/** @test
    @score 0
    @prereq compiles */
function check02() {
    return run_and_check( "f\n4\n", "Result: J","Result: B");
}

/** @test
    @score 0
    @prereq compiles */
function check03() {
    return run_and_check( "b\n18\n", "Result: T","Result: J");
}

/** @test
    @score 0
    @prereq compiles */
function check04() {
    return run_and_check( "p\n-4\n", "Result: L","Result: T");
}

/** @test
    @score 0
    @prereq compiles */
function check05() {
    return run_and_check( "r\n5\n", "Result: W","Result: J");
}

/** @test
    @score 0
    @prereq compiles */
function check06() {
    return run_and_check( "s\n2\n", "Result: U","Result: W");
}

/** @test
    @score 0
    @prereq compiles */
function check07() {
    return run_and_check( "e\n10\n", "Result: O","Result: U");
}

/** @test
    @score 0
    @prereq compiles */
function check08() {
    return run_and_check( "b\n20\n", "Result: V","Result: O");
}

/** @test
    @score 0
    @prereq compiles */
function check09() {
    return run_and_check( "w\n-15\n", "Result: H","Result: V");
}

/** @test
    @score 0
    @prereq compiles */
function check10() {
    return run_and_check( "n\n0\n", "Result: N","Result: H");
}

/** @test
    @score 0
    @prereq check01 check02 check03 check04 check05 check06 check07 check08 check09 check10 */
function block1() {
    return true;
}

// ************************************************************************
//  block2: 
//   Uppercase letters, no wrap
// ************************************************************************


/** @test
    @score 0
    @prereq block1 */
function check11() {
    return run_and_check( "W\n-18\n", "Result: E","Result: W");
}

/** @test
    @score 0
    @prereq block1 */
function check12() {
    return run_and_check( "E\n18\n", "Result: W","Result: E");
}

/** @test
    @score 0
    @prereq block1 */
function check13() {
    return run_and_check( "L\n7\n", "Result: S","Result: W");
}

/** @test
    @score 0
    @prereq block1 */
function check14() {
    return run_and_check( "R\n-17\n", "Result: A","Result: S");
}

/** @test
    @score 0
    @prereq check11 check12 check13 check14 */
function block2() {
    return true;
}

// ************************************************************************
//  block3: 
//   Force wrap around
// ************************************************************************

/** @test
    @score 0
    @prereq block2 */
function check15() {
    return run_and_check( "a\n-1\n", "Result: Z", "Result: T");
}

/** @test
    @score 0
    @prereq block2 */
function check16() {
    return run_and_check( "y\n3\n", "Result: B", "Result: J");
}

/** @test
    @score 0
    @prereq block2 */
function check17() {
    return run_and_check( "h\n25\n", "Result: G", "Result: Z");
}

/** @test
    @score 0
    @prereq block2 */
function check18() {
    return run_and_check( "m\n-17\n", "Result: V", "Result: G");
}

/** @test
    @score 0
    @prereq block2 */
function check19() {
    return run_and_check( "C\n-9\n", "Result: T", "Result: V");
}

/** @test
    @score 0
    @prereq block2 */
function check20() {
    return run_and_check( "Q\n13\n", "Result: D", "Result: T");
}

/** @test
    @prereq check16 check17 check18 check19 check20
    @score  1.0 */
function points()
{
    return _count_case(true);
}

include 'scoring_functions.php';
include 'auto_score.php';


