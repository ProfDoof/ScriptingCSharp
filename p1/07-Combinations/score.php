<?php

/** @test
    @score  0 */
function compiles() {
    global $files;
    global $cflags;
    $source = file_get_contents($files[0]);
    return compile("g++ $cflags -I. $files[0] -o prog")
        &&  check_blacklist($source) && source_excludes_loops() && source_contains_recursive_function("Combinations");
}
/*----------------------------------------------------------------------------------*/
// BASE CASES
/** @test
    @score  0.04 
    @prereq compiles */
function standard01() { return run_and_check("0\n0\n","Combinations(0,0) = 1","Combinations(1,0)"); }

/** @test
    @score  0.04 
    @prereq compiles */
function standard02() { return run_and_check("1\n0\n","Combinations(1,0) = 1","Combinations(1,1)"); }

/** @test
    @score  0.04 
    @prereq compiles */
function standard03() { return run_and_check("1\n1\n","Combinations(1,1) = 1","Combinations(2,2)"); }

/** @test
    @score  0.04 
    @prereq compiles */
function standard04() { return run_and_check("2\n2\n","Combinations(2,2) = 1","Combinations(0,0)"); }
/*----------------------------------------------------------------------------------*/
// SIMPLE AND SAMPLE CASES
/** @test
    @score  0.04 
    @prereq standard01 standard02 standard03 standard04 */
function standard05() { return run_and_check("2\n1\n","Combinations(2,1) = 2",""); }

/** @test
    @score  0.04 
    @prereq standard01 standard02 standard03 standard04 */
function standard06() { return run_and_check("3\n2\n","Combinations(3,2) = 3",""); }

/** @test
    @score  0.04 
    @prereq standard01 standard02 standard03 standard04 */
function standard07() { return run_and_check("6\n2\n","Combinations(6,2) = 15",""); }

/** @test
    @score  0.04 
    @prereq standard01 standard02 standard03 standard04 */
function standard08() { return run_and_check("8\n4\n","Combinations(8,4) = 70",""); }
/*----------------------------------------------------------------------------------*/
/** @test
    @score  0.04 
    @prereq standard05 standard06 standard07 standard08 */
function standard09() { return run_and_check("5\n3\n","Combinations(5,3) = 10",""); }

/** @test
    @score  0.04 
    @prereq standard05 standard06 standard07 standard08 */
function standard10() { return run_and_check("5\n2\n","Combinations(5,2) = 10",""); }

/** @test
    @score  0.04 
    @prereq standard05 standard06 standard07 standard08 */
function standard11() { return run_and_check("6\n3\n","Combinations(6,3) = 20",""); }

/** @test
    @score  0.04 
    @prereq standard05 standard06 standard07 standard08 */
function standard12() { return run_and_check("6\n4\n","Combinations(6,4) = 15",""); }
/*----------------------------------------------------------------------------------*/
/** @test
    @score  0.04 
    @prereq standard09 standard10 standard11 standard12 */
function standard13() { return run_and_check("7\n2\n","Combinations(7,2) = 21",""); }

/** @test
    @score  0.04 
    @prereq standard09 standard10 standard11 standard12 */
function standard14() { return run_and_check("7\n3\n","Combinations(7,3) = 35",""); }

/** @test
    @score  0.04 
    @prereq standard09 standard10 standard11 standard12 */
function standard15() { return run_and_check("8\n3\n","Combinations(8,3) = 56",""); }

/** @test
    @score  0.04 
    @prereq standard09 standard10 standard11 standard12 */
function standard16() { return run_and_check("8\n4\n","Combinations(8,4) = 70",""); }
/*----------------------------------------------------------------------------------*/
/** @test
    @score  0.04 
    @prereq standard13 standard14 standard15 standard16 */
function standard17() { return run_and_check("9\n5\n","Combinations(9,5) = 126",""); }

/** @test
    @score  0.04 
    @prereq standard13 standard14 standard15 standard16 */
function standard18() { return run_and_check("9\n7\n","Combinations(9,7) = 36",""); }

/** @test
    @score  0.04 
    @prereq standard13 standard14 standard15 standard16 */
function standard19() { return run_and_check("10\n3\n","Combinations(10,3) = 120",""); }

/** @test
    @score  0.04 
    @prereq standard13 standard14 standard15 standard16 */
function standard20() { return run_and_check("10\n5\n","Combinations(10,5) = 252",""); }
/*----------------------------------------------------------------------------------*/
/** @test
    @score  0.04 
    @prereq standard17 standard18 standard19 standard20 */
function standard21() { return run_and_check("11\n3\n","Combinations(11,3) = 165",""); }

/** @test
    @score  0.04 
    @prereq standard17 standard18 standard19 standard20 */
function standard22() { return run_and_check("11\n6\n","Combinations(11,6) = 462",""); }

/** @test
    @score  0.04 
    @prereq standard17 standard18 standard19 standard20 */
function standard23() { return run_and_check("12\n4\n","Combinations(12,4) = 495",""); }

/** @test
    @score  0.04 
    @prereq standard17 standard18 standard19 standard20 */
function standard24() { return run_and_check("13\n5\n","Combinations(13,5) = 1287",""); }

/** @test
    @score  0.04 
    @prereq standard17 standard18 standard19 standard20 */
function standard25() { return run_and_check("15\n11\n","Combinations(15,11) = 1365",""); }

include 'scoring_functions.php';
include 'auto_score.php';

