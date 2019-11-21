<?php

include 'scoring_functions.php';
include 'auto_score.php';


function verify_output($in,$h,$m,$s) {
    return run("prog",$in,$output)
    && output_contains_lines($output,"This corresponds to $h hours, $m minutes, and $s seconds."); 
}

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
function standard1() { 
    return verify_output("3600\n",1,0,0);
}

/** @test
    @score  0 
    @prereq compiles */
function standard2() { 
    return verify_output("3669\n",1,1,9);
}

/** @test
    @score  0 
    @prereq compiles */
function standard3() { 
    return verify_output("8000\n",2,13,20);
}

/** @test
    @score  0 
    @prereq compiles */
function standard4() { 
    return verify_output("123\n",0,2,3);
}

/** @test
    @score  0 
    @prereq compiles */
function standard5() { 
    return verify_output("7116\n",1,58,36);
}

/** @test
    @score  0 
    @prereq compiles */
function standard6() { 
    return verify_output("9874\n",2,44,34);
}

/** @test
    @score  0 
    @prereq compiles */
function standard7() { 
    return verify_output("1\n",0,0,1);
}

/** @test
    @score  0 
    @prereq compiles */
function standard8() { 
    return verify_output("0\n",0,0,0);
}

/** @test
    @score  0
    @prereq standard1 standard2 standard3 standard4 standard5 standard6 standard7 standard8 */
function format() {
    return run("prog","8412\n",$output)
    && output_contains_lines($output,<<<END
This program converts seconds into hours, minutes and seconds.
Enter the number of seconds: <span class=input>8412</span>
This corresponds to 2 hours, 20 minutes, and 12 seconds.
END
);
}

/** @test
    @prereq format
    @score  1.0 */
function points()
{
    return _count_case(true);
}