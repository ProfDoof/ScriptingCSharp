<?php

/** @test
    @score  0.05 */
function compiles() {
    global $files;
    global $cflags;
    $source = file_get_contents($files[0]);
    return compile("g++ $cflags -I. $files[0] -o prog")
        &&  check_blacklist($source) && source_excludes_loops() && source_contains_recursive_function("fib");
}

/** @test
    @score  0.05 
    @prereq compiles */
function standard01() { return run_and_check("0\n","Fibonacci(0) is 0",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard02() { return run_and_check("1\n","Fibonacci(1) is 1",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard03() { return run_and_check("2\n","Fibonacci(2) is 1",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard04() { return run_and_check("3\n","Fibonacci(3) is 2",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard05() { return run_and_check("4\n","Fibonacci(4) is 3",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard06() { return run_and_check("5\n","Fibonacci(5) is 5",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard07() { return run_and_check("6\n","Fibonacci(6) is 8",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard08() { return run_and_check("7\n","Fibonacci(7) is 13",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard09() { return run_and_check("8\n","Fibonacci(8) is 21",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard10() { return run_and_check("9\n","Fibonacci(9) is 34",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard11() { return run_and_check("10\n","Fibonacci(10) is 55",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard12() { return run_and_check("12\n","Fibonacci(12) is 144",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard13() { return run_and_check("15\n","Fibonacci(15) is 610",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard14() { return run_and_check("16\n","Fibonacci(16) is 987",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard15() { return run_and_check("18\n","Fibonacci(18) is 2584",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard16() { return run_and_check("20\n","Fibonacci(20) is 6765",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard17() { return run_and_check("25\n","Fibonacci(25) is 75025",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard18() { return run_and_check("30\n","Fibonacci(30) is 832040",""); }

/** @test
    @score  0.05 
    @prereq compiles */
function standard19() { return run_and_check("35\n","Fibonacci(35) is 9227465",""); }




include 'scoring_functions.php';
include 'auto_score.php';

