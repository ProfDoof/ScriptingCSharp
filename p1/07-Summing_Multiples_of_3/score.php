<?php

/** @test
    @score  0 */
function compiles() { 
    global $files;
    global $cflags;
    $sourcecode = file_get_contents($files[0]);
    return compile("g++ $cflags -I. $files[0] -o prog")
        && source_excludes_loops() && source_contains_recursive_function("sum3s");
}

/** @test
    @score  0.25 
    @prereq compiles */
function samples() { 
    return run_and_check("10\n","The sum is 18.","30")
       &&  run_and_check("12\n","The sum is 30.","18")
       &&  run_and_check("3\n","The sum is 3.",""); 
}

/** @test
    @score  0.25 
    @prereq samples */
function basic() { 
    return run_and_check("8\n","The sum is 9.","")
       &&  run_and_check("19\n","The sum is 63.","")
       &&  run_and_check("18\n","The sum is 63.","")
       &&  run_and_check("17\n","The sum is 45.","")
       &&  run_and_check("20\n","The sum is 63.","")
       &&  run_and_check("10\n","The sum is 18.","")
       &&  run_and_check("28\n","The sum is 135.","")
       &&  run_and_check("30\n","The sum is 165.","")
       &&  run_and_check("31\n","The sum is 165.","")
       &&  run_and_check("11\n","The sum is 18.",""); 
}

/** @test
    @score  0.25 
    @prereq basic */
function larger() { 
    return run_and_check("40\n","The sum is 273.","")
       &&  run_and_check("45\n","The sum is 360.","")
       &&  run_and_check("56\n","The sum is 513.","")
       &&  run_and_check("68\n","The sum is 759.","")
       &&  run_and_check("80\n","The sum is 1053.","")
       &&  run_and_check("83\n","The sum is 1134.","")
       &&  run_and_check("100\n","The sum is 1683.","")
       &&  run_and_check("120\n","The sum is 2460.","")
       &&  run_and_check("5000\n","The sum is 4165833.","")
       &&  run_and_check("10000\n","The sum is 16668333.",""); 
}

/** @test
    @score  0.25 
    @prereq basic */
function edge() { 
    return run_and_check("0\n","The sum is 0.","")
       &&  run_and_check("1\n","The sum is 0.","")
       &&  run_and_check("2\n","The sum is 0.","")
       &&  run_and_check("-1\n","The sum is 0.","")
       &&  run_and_check("-5\n","The sum is 0.",""); 
}

include 'scoring_functions.php';
include 'auto_score.php';


