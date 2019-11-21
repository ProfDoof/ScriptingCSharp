<?php

/** @test
    @score  0 */
function compiles() {
    global $files;
    global $cflags;
    $source = file_get_contents($files[0]);
    return compile("g++ $cflags -I. $files[0] -o prog")
       &&  check_blacklist($source);
}

/** @test
    @score  0
    @prereq compiles */
function day() {
    return run_and_check("5 15 2009\n5 14 2009\n","5/14/2009 is earlier.","5/15/2009 is earlier.")
       &&  run_and_check("2 29 2012\n2 28 2012\n","2/28/2012 is earlier.","2/29/2012 is earlier.")
       &&  run_and_check("2 28 2012\n2 29 2012\n","2/28/2012 is earlier.","2/29/2012 is earlier.")
       &&  run_and_check("1 1 2000\n1 5 2000\n","1/1/2000 is earlier.","1/5/2000 is earlier.")
       &&  run_and_check("2 4 1980\n2 10 1980\n","2/4/1980 is earlier.","2/10/1980 is earlier.")
       &&  run_and_check("9 13 2012\n9 26 2012\n","9/13/2012 is earlier.","9/26/2012 is earlier.")
       &&  run_and_check("10 4 1956\n10 6 1956\n","10/4/1956 is earlier.","10/6/1956 is earlier.");
}

/** @test
    @score  0
    @prereq compiles */
function month() {
    return run_and_check("7 29 2012\n2 28 2012\n","2/28/2012 is earlier.","7/29/2012 is earlier.")
       &&  run_and_check("2 28 2012\n7 29 2012\n","2/28/2012 is earlier.","7/29/2012 is earlier.")
       &&  run_and_check("1 1 2000\n2 1 2000\n","1/1/2000 is earlier.","2/1/2000 is earlier.")
       &&  run_and_check("5 14 2012\n3 14 2012\n","3/14/2012 is earlier.","5/14/2012 is earlier.")
       &&  run_and_check("8 27 2012\n12 14 2012\n","8/27/2012 is earlier.","12/14/2012 is earlier.")
       &&  run_and_check("10 31 1978\n8 31 1978\n","8/31/1978 is earlier.","10/31/1978 is earlier.")
       &&  run_and_check("2 04 1980\n10 29 1989\n","2/4/1980 is earlier.","10/29/1980 is earlier.");
}

/** @test
    @score  0
    @prereq compiles */
function year() {
    return run_and_check("3 14 2012\n5 14 2009\n","5/14/2009 is earlier.","3/14/2012 is earlier.")
       &&  run_and_check("5 14 2009\n3 14 2012\n","5/14/2009 is earlier.","3/14/2012 is earlier.")
       &&  run_and_check("1 1 2000\n12 31 1999\n","12/31/1999 is earlier.","1/1/2000 is earlier.")
       &&  run_and_check("10 29 1979\n10 29 1980\n","10/29/1979 is earlier.","10/29/1980 is earlier.")
       &&  run_and_check("4 4 2004\n3 3 2003\n","3/3/2003 is earlier.","4/4/2004 is earlier.")
       &&  run_and_check("11 5 1991\n11 6 1892\n","11/6/1892 is earlier.","11/5/1991 is earlier.");
}

/** @test
    @prereq day month year
    @score  1.0 */
function points()
{
    return _count_case(true);
}

include 'scoring_functions.php';
include 'auto_score.php';


