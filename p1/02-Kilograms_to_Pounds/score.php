<?php

include 'scoring_functions.php';
include 'auto_score.php';

function verify_output($in,$out1,$out2) {
    return run("prog",$in,$output)
    && output_contains_lines($output," $out1 pounds and $out2 ounces"); 
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
    return verify_output("10\n",22,"0.0");
}

/** @test
    @score  0 
    @prereq compiles */
function standard2() { 
    return verify_output("9.6\n",21,1.9);
}

/** @test
    @score  0 
    @prereq compiles */
function standard3() { 
    return verify_output("123.45\n",271,9.4);
}

/** @test
    @score  0 
    @prereq compiles */
function standard4() { 
    return verify_output("2.5\n",5,"8.0");
}

/** @test
    @score  0 
    @prereq compiles */
function standard5() { 
    return verify_output("4\n",8,12.8);
}

/** @test
    @score  0 
    @prereq compiles */
function standard6() { 
    return verify_output("0\n",0,"0.0");
}

/** @test
    @score  0 
    @prereq compiles */
function standard7() { 
    return verify_output(".5\n",1,1.6);
}

/** @test
    @score 0
    @prereq standard1 standard2 standard3 standard4 standard5 standard6 standard7 */
function noformat() { return true; }

/** @test
    @score  0 
    @prereq noformat */
function format1() { 
    return run("prog","1\n",$output)
    && output_contains_lines($output,<<<END
This program converts kilograms to pounds and ounces.
Kilograms: <span class=input>1</span>

1 kilograms is 2 pounds and 3.2 ounces.
END
); 
}

/** @test
    @score  0 
    @prereq noformat */
function format2() { 
    return run("prog","8.25\n",$output)
    && output_contains_lines($output,<<<END
This program converts kilograms to pounds and ounces.
Kilograms: <span class=input>8.25</span>

8.25 kilograms is 18 pounds and 2.4 ounces.
END
); 
}

/** @test
    @prereq format1 format2
    @score  1.0 */
function points()
{
    return _count_case(true);
}
