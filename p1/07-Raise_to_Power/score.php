<?php


/** @test
    @score  0.05 */
function compiles() {
    global $files;
    global $cflags;
    $source = file_get_contents($files[0]);
    return compile("g++ $cflags -I. $files[0] -o prog")
        &&  check_blacklist($source) && source_excludes_loops() && source_contains_recursive_function("pow");
}

/** @test
    @score  0.05 
    @prereq compiles */
function standard01() { return run_and_check("3\n2\n","3^2 = 9",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard02() { return run_and_check("2\n3\n","2^3 = 8",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard03() { return run_and_check("87\n0\n","87^0 = 1",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard04() { return run_and_check("7\n4\n","7^4 = 2401",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard05() { return run_and_check("5\n5\n","5^5 = 3125",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard06() { return run_and_check("1\n25\n","1^25 = 1",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard07() { return run_and_check("2\n12\n","2^12 = 4096",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard08() { return run_and_check("3\n6\n","3^6 = 729",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard09() { return run_and_check("9\n3\n","9^3 = 729",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard10() { return run_and_check("513\n1\n","513^1 = 513",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard11() { return run_and_check("3204\n2\n","3204^2 = 10265616",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard12() { return run_and_check("11\n6\n","11^6 = 1771561",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard13() { return run_and_check("5\n3\n","5^3 = 125",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard14() { return run_and_check("2\n5\n","2^5 = 32",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard15() { return run_and_check("3\n2\n","3^2 = 9",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard16() { return run_and_check("3\n2\n","3^2 = 9",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard17() { return run_and_check("6\n7\n","6^7 = 279936",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard18() { return run_and_check("601\n3\n","601^3 = 217081801",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard19() { return run_and_check("3\n15\n","3^15 = 14348907",""); }




include 'scoring_functions.php';
include 'auto_score.php';

