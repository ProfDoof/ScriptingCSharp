<?php


/** @test
    @score  0 */
function compiles() {
    global $files;
    global $cflags;
    $source = file_get_contents($files[0]);
    return compile("g++ $cflags -I. $files[0] -o prog")
        &&  check_blacklist($source) && source_excludes_loops() && source_contains_recursive_function();
}

/** @test
    @score  0.25
    @prereq compiles */
function base_case() { 
    return run_and_check("0\n0\n", "Ackermann(0,0) = 1", "Ackermann(0,1)")
       &&  run_and_check("0\n1\n", "Ackermann(0,1) = 2", "Ackermann(0,0)")
       &&  run_and_check("0\n4\n", "Ackermann(0,4) = 5", "1")
       &&  run_and_check("0\n8\n", "Ackermann(0,8) = 9", "5")
       &&  run_and_check("0\n81\n", "Ackermann(0,81) = 82", "Ackermann(0,0)")
       &&  run_and_check("0\n64\n", "Ackermann(0,64) = 65", "82")
       &&  run_and_check("0\n28\n", "Ackermann(0,28) = 29", "65");
}

/** @test
    @score  0
    @prereq base_case */
function ones_and_twos() { 
    return run_and_check("1\n1\n", "Ackermann(1,1) = 3", "Ackermann(0,0)")
       &&  run_and_check("1\n2\n", "Ackermann(1,2) = 4", "Ackermann(1,1)")
       &&  run_and_check("1\n5\n", "Ackermann(1,5) = 7", "Ackermann(1,1)")
       &&  run_and_check("1\n10\n", "Ackermann(1,10) = 12", "Ackermann(1,1)")
       &&  run_and_check("2\n1\n", "Ackermann(2,1) = 5", "4")
       &&  run_and_check("2\n2\n", "Ackermann(2,2) = 7", "5")
       &&  run_and_check("2\n8\n", "Ackermann(2,8) = 19", "Ackermann(2,2)")
       &&  run_and_check("2\n10\n", "Ackermann(2,10) = 23", "Ackermann(2,8)")
       &&  run_and_check("2\n50\n", "Ackermann(2,50) = 103", "Ackermann(2,10)");
}

/** @test
    @score  0
    @prereq ones_and_twos */
function threes() { 
    return run_and_check("3\n0\n", "Ackermann(3,0) = 5", "Ackermann(0,0)")
       &&  run_and_check("3\n1\n", "Ackermann(3,1) = 13", "5")
       &&  run_and_check("3\n2\n", "Ackermann(3,2) = 29", "5")
       &&  run_and_check("3\n3\n", "Ackermann(3,3) = 61", "5")
       &&  run_and_check("3\n4\n", "Ackermann(3,4) = 125", "Ackermann(3,3)")
       &&  run_and_check("3\n5\n", "Ackermann(3,5) = 253", "125")
       &&  run_and_check("3\n6\n", "Ackermann(3,6) = 509", "Ackermann(3,5)")
       &&  run_and_check("3\n8\n", "Ackermann(3,8) = 2045", "Ackermann(3,5)")
       &&  run_and_check("3\n10\n", "Ackermann(3,10) = 8189", "Ackermann(3,5)");
}

/** @test
    @score  0.75
    @prereq threes */
function points() { 
    return _count_case(true);
}

include 'scoring_functions.php';
include 'auto_score.php';



