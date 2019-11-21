<?php
include 'scoring_functions.php';
include 'auto_score.php';


function verify_output($in,$out) {
    return run("prog",$in,$output)
    && output_contains_lines($output,"The area is $out"); 
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
function standard1() { return verify_output("6.5\n3\n",9.75); }

/** @test
    @score  0 
    @prereq compiles */
function standard2() { return verify_output("11.7\n5.1\n",29.835); }

/** @test
    @score  0
    @prereq compiles */
function standard3() { return verify_output("1.2\n3.4\n",2.04); }

/** @test
    @score  0 
    @prereq compiles */
function standard4() { return verify_output("3\n1\n",1.5); }

/** @test
    @score  0 
    @prereq compiles */
function standard5() { return verify_output("12.02\n10\n",60.1); }

/** @test
    @score  0
    @prereq compiles */
function standard6() { return verify_output("97.131\n0\n",0); }

/** @test
    @score  0
    @prereq compiles */
function standard7() { return verify_output("1.8\n5.3\n",4.77); }

/** @test 
    @score  0
    @prereq standard1 standard2 standard3 standard4 standard5 standard6 standard7 */
function noformat() { return true; }

/** @test
    @score  0 
    @prereq noformat */
function format1() { 
    return run("prog","6.5\n3\n",$output)
    && output_contains_lines($output,<<<END
This program computes the area of a triangle.

Enter the base of the triangle: <span class=input>6.5</span>
Enter the height of the triangle: <span class=input>3</span>

The area is 9.75
END
); 
}

/** @test
    @score  0 
    @prereq noformat */
function format2() { 
    return run("prog","5.2\n3.7\n",$output)
    && output_contains_lines($output,<<<END
This program computes the area of a triangle.

Enter the base of the triangle: <span class=input>5.2</span>
Enter the height of the triangle: <span class=input>3.7</span>

The area is 9.62
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
