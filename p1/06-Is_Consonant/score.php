<?php

/** @test
    @score  0 */
// Compile program exactly as submitted by student, check for function declaration
function compiles() { 
    global $files;
    global $cflags;

    return compile("g++ $cflags -I. $files[0] -o prog");
}

/** @test
    @score  0
    @prereq compiles */
function uppercase_vowels() {
    return run_and_check("A\n","is a vowel","is a consonant")
       &&  run_and_check("E\n","is a vowel","is a consonant")
       &&  run_and_check("I\n","is a vowel","is a consonant")
       &&  run_and_check("O\n","is a vowel","is a consonant")
       &&  run_and_check("U\n","is a vowel","is a consonant");
}

/** @test
    @score  0
    @prereq uppercase_vowels */
function uppercase_consonants() {
    return run_and_check("B\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("C\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("D\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("F\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("G\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("H\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("J\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("K\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("L\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("M\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("N\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("P\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("Q\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("R\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("S\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("T\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("V\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("W\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("X\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("Y\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("Z\n","is a consonant","is a vowel","is a consonant");
}

/** @test
    @score  0
    @prereq uppercase_consonants uppercase_vowels */
function lowercase_consonants() {
    return run_and_check("B\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("C\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("D\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("F\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("G\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("H\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("J\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("K\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("L\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("M\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("N\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("P\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("Q\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("R\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("S\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("T\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("V\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("W\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("X\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("Y\n","is a consonant","is a vowel","is a consonant")
       &&  run_and_check("Z\n","is a consonant","is a vowel","is a consonant");
}

/** @test
    @score  0
    @prereq uppercase_consonants uppercase_vowels */
function lowercase_vowels() {
    return run_and_check("a\n","is a vowel","is a consonant")
       &&  run_and_check("e\n","is a vowel","is a consonant")
       &&  run_and_check("i\n","is a vowel","is a consonant")
       &&  run_and_check("o\n","is a vowel","is a consonant")
       &&  run_and_check("u\n","is a vowel","is a consonant");
}

/** @test
    @score  0
    @prereq lowercase_consonants lowercase_vowels */
function non_letter() {
    return run_and_check("7\n","is not a letter","is a vowel")
       &&  run_and_check("2\n","is not a letter","is a consonant")
       &&  run_and_check("$\n","is not a letter","is a consonant")
       &&  run_and_check("!\n","is not a letter","is a vowel")
       &&  run_and_check("*\n","is not a letter","is a vowel")
       &&  run_and_check("{\n","is not a letter","is a consonant")
       &&  run_and_check(">\n","is not a letter","is a vowel");
}

/** @test
    @prereq non_letter
    @score  1.0 */
function points()
{
    return _count_case(true);
}

include 'scoring_functions.php';
include 'auto_score.php';
