<?php

/** @test
    @score  0.10 */
function compiles() { 
    global $files;
    global $cflags;
    return compile("g++ $cflags -I. $files[0] -o prog") && source_excludes_loops() && source_contains_recursive_function("fact");
}

/** @test
    @score  0.10 
    @prereq compiles */
function zero() { return run_and_check("0\n","0! = 1","1!"); }

/** @test
    @score  0.05 
    @prereq compiles */
function one() { return run_and_check("1\n","1! = 1","2!"); }

/** @test
    @score  0.05 
    @prereq compiles */
function two() { return run_and_check("2\n","2! = 2","= 1"); }

/** @test
    @score  0.05 
    @prereq compiles */
function three() { return run_and_check("3\n","3! = 6","= 3"); }

/** @test
    @score  0.05 
    @prereq compiles */
function four() { return run_and_check("4\n","4! = 24","3!"); }

/** @test
    @score  0.05 
    @prereq compiles */
function five() { return run_and_check("5\n","5! = 120","4!"); }

/** @test
    @score  0.05 
    @prereq compiles */
function six() { return run_and_check("6\n","6! = 720","5040"); }

/** @test
    @score  0.05 
    @prereq compiles */
function seven() { return run_and_check("7\n","7! = 5040","720"); }

/** @test
    @score  0.05 
    @prereq compiles */
function eight() { return run_and_check("8\n","8! = 40320","5040"); }

/** @test
    @score  0.05 
    @prereq compiles */
function nine() { return run_and_check("9\n","9! = 362880","40320"); }

/** @test
    @score  0.05 
    @prereq compiles */
function ten() { return run_and_check("10\n","10! = 3628800","9!"); }

/** @test
    @score  0.10 
    @prereq compiles */
function eleven() { return run_and_check("11\n","11! = 39916800","12"); }

/** @test
    @score  0.10 
    @prereq compiles */
function twelve() { return run_and_check("12\n","12! = 479001600",""); }

/** @test
    @score  0.10 
    @prereq compiles */
function thirteen() { return run_and_check("13\n","13! = 1932053504",""); }

include 'scoring_functions.php';
include 'auto_score.php';

