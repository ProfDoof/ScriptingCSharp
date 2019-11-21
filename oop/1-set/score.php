<?php

$cflags = "-Wall -pedantic-errors -Wextra -Wshadow -I ./";

/** @test
    @score 0.10 */
function compiles() {
    global $cflags;
    trace("running Compile\n");
    return compile("g++ $cflags -c IntegerSet.cpp");
}

/** @test
    @prereq compiles
    @score 0.30 */
function given() {
    global $cflags;
    trace("running Sample\n");
    return compile("g++ $cflags sample.cpp new.cpp IntegerSet.cpp -o sample")
        && run("./sample","",$output);
}

/** @test
    @prereq compiles given
    @score 0.60 */
function all() {
    global $cflags;
    trace("running Everything\n");
    return compile("g++ $cflags test.cpp new.cpp IntegerSet.cpp -o test")
        && run("test","",$output);
}

include 'oop_scoring.php';
