<?php

/** @test
    @score  0 */
function compiles() { 
    global $files;
    global $cflags;
    return compile("g++ $cflags -I. $files[0] -o prog"); 
}

/** @test
    @score 0
    @prereq compiles */
function case01() {
    return run_and_check( "1a\nb0\n", "1a + b0 = ca","70");
}

/** @test
    @score 0
    @prereq compiles */
function case02() {
    return run_and_check( "4b\n25\n", "4b + 25 = 70","ca");
}

/** @test
    @score 0
    @prereq compiles */
function case03() {
    return run_and_check( "10\n20\n", "10 + 20 = 30","70");
}

/** @test
    @score 0
    @prereq compiles */
function case04() {
    return run_and_check( "a0\n22\n", "a0 + 22 = c2","30");
}

/** @test
    @score 0
    @prereq compiles */
function case05() {
    return run_and_check( "18\n19\n", "18 + 19 = 31","c2");
}

//************************************************************

/** @test
    @score 0
    @prereq case01 case02 case03 case04 case05 */
function case06() {
    return run_and_check( "33\ncc\n", "33 + cc = ff","");
}

/** @test
    @score 0
    @prereq case01 case02 case03 case04 case05 */
function case07() {
    return run_and_check( "12\nab\n", "12 + ab = bd","ff");
}

/** @test
    @score 0
    @prereq case01 case02 case03 case04 case05 */
function case08() {
    return run_and_check( "35\n37\n", "35 + 37 = 6c","bd");
}

/** @test
    @score 0
    @prereq case01 case02 case03 case04 case05 */
function case09() {
    return run_and_check( "53\n73\n", "53 + 73 = c6","6c");
}

/** @test
    @score 0
    @prereq case01 case02 case03 case04 case05 */
function case10() {
    return run_and_check( "66\n74\n", "66 + 74 = da","c6");
}

/** @test
    @score 0
    @prereq case06 case07 case08 case09 case10 */
function case11() {
    return run_and_check( "14\n2a\n", "14 + 2a = 3e","c6");
}

/** @test
    @score 0
    @prereq case06 case07 case08 case09 case10 */
function case12() {
    return run_and_check( "17\nb9\n", "17 + b9 = d0","3e");
}

/** @test
    @score 0
    @prereq case06 case07 case08 case09 case10 */
function case13() {
    return run_and_check( "81\n6e\n", "81 + 6e = ef","d0");
}

/** @test
    @score 0
    @prereq case06 case07 case08 case09 case10 */
function case14() {
    return run_and_check( "6e\n27\n", "6e + 27 = 95","ef");
}

/** @test
    @score 0
    @prereq case06 case07 case08 case09 case10 */
function case15() {
    return run_and_check( "95\n27\n", "95 + 27 = bc","ef");
}

//********************************************************
// Following tests will produce 3-digit answers
//********************************************************

/** @test
    @score 0
    @prereq case11 case12 case13 case14 case15 */
function case16() {
    return run_and_check( "aa\n72\n", "aa + 72 = 11c", "1f3");
}

/** @test
    @score 0
    @prereq case11 case12 case13 case14 case15 */
function case17() {
    return run_and_check( "ff\nff\n", "ff + ff = 1fe","11c");
}

/** @test
    @score 0
    @prereq case11 case12 case13 case14 case15 */
function case18() {
    return run_and_check( "bb\ncc\n", "bb + cc = 187","1fe");
}

/** @test
    @score 0
    @prereq case11 case12 case13 case14 case15 */
function case19() {
    return run_and_check( "90\n90\n", "90 + 90 = 120","187");
}

/** @test
    @score 0
    @prereq case11 case12 case13 case14 case15 */
function case20() {
    return run_and_check( "8a\na8\n", "8a + a8 = 132","187");
}

/** @test
    @prereq case16 case17 case18 case19 case20
    @score  1.0 */
function points()
{
    return _count_case(true);
}

include 'scoring_functions.php';
include 'auto_score.php';


