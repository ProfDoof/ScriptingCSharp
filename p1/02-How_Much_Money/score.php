<?php

function run_and_check($in,$out) {
    return run("prog",$in,$output)
    && output_contains_lines($output,"The total is \$$out"); 
}

/** @test
    @score  0 */
function compiles() { 
    global $files;
    global $cflags;
    return compile("g++ $cflags -I. $files[0] -o prog"); 
}

/** @test
    @score  0
    @prereq compiles */
function baseline() {
    return run_and_check("1\n0\n0\n0\n","0.25")
       &&  run_and_check("0\n1\n0\n0\n","0.10")
       &&  run_and_check("0\n0\n1\n0\n","0.05")
       &&  run_and_check("0\n0\n0\n1\n","0.01")
       &&  run_and_check("6\n0\n0\n0\n","1.50")
       &&  run_and_check("0\n7\n0\n0\n","0.70")
       &&  run_and_check("0\n0\n8\n0\n","0.40")
       &&  run_and_check("0\n0\n0\n3\n","0.03");
}

/** @test
    @score  0 
    @prereq baseline */
function check1() { return run_and_check("1\n1\n1\n1\n","0.41"); } 

/** @test
    @score  0 
    @prereq baseline */
function check2() { return run_and_check("3\n2\n0\n4\n","0.99"); } 

/** @test
    @score  0 
    @prereq baseline */
function check3() { return run_and_check("4\n20\n60\n400\n","10.00"); } 

/** @test
    @score  0 
    @prereq check1 check2 check3 */
function check4() { return run_and_check("0\n0\n4\n5\n","0.25"); } 

/** @test
    @score  0 
    @prereq check1 check2 check3 */
function check5() { return run_and_check("6\n2\n1\n5\n","1.80"); } 

/** @test
    @score  0 
    @prereq check4 check5 */
function check6() { return run_and_check("3\n5\n5\n13\n","1.63"); } 

/** @test
    @score  0 
    @prereq check4 check5 */
function check7() { return run_and_check("5\n0\n1\n1\n","1.31"); } 

/** @test
    @score  0 
    @prereq check6 check7 */
function format() { 
    return run("prog","3\n4\n5\n6\n",$output)
       &&  output_contains_lines($output,<<<END
Quarters: <span class=input>3</span>
Dimes: <span class=input>4</span>
Nickels: <span class=input>5</span>
Pennies: <span class=input>6</span>

The total is $1.46
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

include 'auto_score.php';

