<?php
include 'scoring_functions.php';
include 'auto_score.php';

/** @test
    @score  0 */
function compiles() {
    global $files;
    global $cflags;
    $source = file_get_contents($files[0]);
    return compile("g++ $cflags -I. $files[0] -o prog")
        &&  check_blacklist($source);
// && source_contains_function("absval","int",array("int"));
}

/** @test
    @score  0
    @prereq compiles */
function zero() { 
    return run_and_check("0\n","|0| = 0","1");
}

/** @test
    @score  0
    @prereq compiles */
function positive() { 
    return run_and_check(   "1\n","|1| = 1","-")
       &&  run_and_check(   "8\n","|8| = 8","-")
       &&  run_and_check(  "37\n","|37| = 37","-")
       &&  run_and_check(  "52\n","|52| = 52","-")
       &&  run_and_check( "784\n","|784| = 784","-")
       &&  run_and_check("5035\n","|5035| = 5035","-");
}

/** @test
    @score  0
    @prereq compiles */
function negative() { 
    return run_and_check(   "-1\n","|-1| = 1","= -")
       &&  run_and_check(   "-8\n","|-8| = 8","= -")
       &&  run_and_check(  "-49\n","|-49| = 49","= -")
       &&  run_and_check(  "-22\n","|-22| = 22","= -")
       &&  run_and_check( "-907\n","|-907| = 907","= -")
       &&  run_and_check("-1066\n","|-1066| = 1066","= -");
}

/** @test
    @prereq zero positive negative
    @score  1.0 */
function points()
{
    return _count_case(true);
}
