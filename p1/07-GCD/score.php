<?php

/** @test
    @score  0.05 */
function compiles() {
    global $files;
    global $cflags;
    $source = file_get_contents($files[0]);
    return compile("g++ $cflags -I. $files[0] -o prog")
        &&  check_blacklist($source) && source_excludes_loops() && source_contains_recursive_function("gcd");
}

/** @test
    @score  0.05 
    @prereq compiles */
function standard01() { return run_and_check("6\n8\n","GCD = 2",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard02() { return run_and_check("24\n30\n","GCD = 6",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard03() { return run_and_check("6\n2\n","GCD = 2",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard04() { return run_and_check("40\n30\n","GCD = 10",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard05() { return run_and_check("33\n90\n","GCD = 3",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard06() { return run_and_check("22\n6\n","GCD = 2",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard07() { return run_and_check("3\n5\n","GCD = 1",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard08() { return run_and_check("15\n15\n","GCD = 15",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard09() { return run_and_check("2843\n7057\n","GCD = 1",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard10() { return run_and_check("105\n63\n","GCD = 21",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard11() { return run_and_check("39\n221","GCD = 13",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard12() { return run_and_check("440\n280\n","GCD = 40",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard13() { return run_and_check("30\n40\n","GCD = 10",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard14() { return run_and_check("2\n5\n","GCD = 1",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard15() { return run_and_check("6\n11\n","GCD = 1",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard16() { return run_and_check("5780\n6362","GCD = 2",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard17() { return run_and_check("5856\n1035\n","GCD = 3",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard18() { return run_and_check("42738\n89454\n","GCD = 102",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard19() { return run_and_check("38932453\n892345\n","GCD = 1",""); }




include 'scoring_functions.php';
include 'auto_score.php';

