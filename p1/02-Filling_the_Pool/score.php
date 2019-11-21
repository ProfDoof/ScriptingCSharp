<?php
include 'scoring_functions.php';
include 'auto_score.php';


function verify_output($in,$out) {
    return run("prog",$in,$output)
    && output_contains_lines($output,"The pool will fill completely in $out minutes"); 
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
function standard1() { return verify_output("10\n8\n7\n14\n",299.2); }

/** @test
    @score  0 
    @prereq compiles */
function standard2() { return verify_output("32\n15\n4\n102\n",140.8); }

/** @test
    @score  0
    @prereq compiles */
function standard3() { return verify_output("10\n10\n10\n748\n",10); }

/** @test
    @score  0 
    @prereq compiles */
function standard4() { return verify_output("20\n30\n6\n45\n",598.4); }

/** @test
    @score  0 
    @prereq compiles */
function standard5() { return verify_output("100\n38\n8\n800\n",284.24); }

/** @test
    @score  0
    @prereq compiles */
function standard6() { return verify_output("125\n55\n11\n1000",565.675); }

/** @test
    @score  0
    @prereq compiles */
function standard7() { return verify_output("334\n6\n6\n220\n",408.816); }

/** @test 
    @score  0
    @prereq standard1 standard2 standard3 standard4 standard5 standard6 standard7 */
function noformat() { return true; }

/** @test
    @score  0 
    @prereq noformat */
function format1() { 
    return run("prog","10\n8\n7\n14\n",$output)
    && output_contains_lines($output,<<<END
Enter pool dimensions
Length: <span class=input>10</span>
Width: <span class=input>8</span>
Depth: <span class=input>7</span>

Water entry rate: <span class=input>14</span>

The pool will fill completely in 299.2 minutes
END
); 
}

/** @test
    @score  0 
    @prereq noformat */
function format2() { 
    return run("prog","800\n800\n8\n80\n",$output)
    && output_contains_lines($output,<<<END
Enter pool dimensions
Length: <span class=input>800</span>
Width: <span class=input>800</span>
Depth: <span class=input>8</span>

Water entry rate: <span class=input>80</span>

The pool will fill completely in 478720 minutes
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