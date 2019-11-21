<?php

$cflags = "-Wall -pedantic-errors -Wextra -Wshadow -I ./";

/** @test
    @score  0.1 */
function compiles() {
    global $cflags;
    return  compile("g++ $cflags -c IntegerSet.h");
}

/** @test
    @prereq compiles
    @score  0.30 */
function given() {
    global $cflags;
    return compile("g++ $cflags sample.cpp new.cpp -o sample") 
        && run("sample","",$output);
}

/** @test
    @prereq given
    @score  0.60 */
function all() {
    global $cflags;
    return compile("g++ $cflags test.cpp new.cpp -o test") 
        && run("test","",$output);
}

include 'auto_score.php';