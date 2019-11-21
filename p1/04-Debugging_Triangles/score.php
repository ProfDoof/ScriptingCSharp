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
function standard1() { 
    $res = run_and_check("2.0\n2.0\n4.0\n","Those lengths do not form a triangle.","This is an isoceles triangle.");
    if( !$res ) hint("Make sure that a proper triangle can be formed from three lengths.");
    return $res; 
}

/** @test
    @score  0 
    @prereq compiles */
function standard2() { 
    $res = run_and_check("1\n1\n1\n","This is an equilateral triangle.","This is an isoceles triangle.");
    if( !$res ) hint("Check for equilateral triangles first.");
    return $res; 
}

/** @test
    @score  0 
    @prereq compiles */
function standard3() { 
    $res = run_and_check("3\n4\n5\n","This is a right triangle.","This is an acute scalene triangle.");
    if( !$res ) hint("A right triangle is not an acute scalene triangle.");
    return $res; 
}

/** @test
    @score  0 
    @prereq standard1 standard2 standard3 */
function standard4() { 
    $res = run_and_check("5\n4\n3\n","This is a right triangle.","This is an acute scalene triangle.");
    if( !$res ) hint("A (3,4,5) triangle is the same as a (5,4,3) triangle.");
    return $res; 
}

/** @test
    @score  0 
    @prereq standard1 standard2 standard3 */
function standard5() { 
    $res = run_and_check("4\n5\n3\n","This is a right triangle.","This is an acute scalene triangle.");
    if( !$res ) hint("A (4,5,3) triangle is the same as a (5,4,3) triangle.");
    return $res; 
}

/** @test
    @score  0 
    @prereq standard4 standard5 */
function standard6() { 
    $res = run_and_check("5.2\n8.3\n7.6\n","This is an acute scalene triangle.","This is an obtuse scalene triangle.");
    if( !$res ) hint("Don't forget to test for scalene triangles.");
    return $res; 
}

/** @test
    @score  0 
    @prereq standard4 standard5 */
function standard7() { 
    $res = run_and_check("3\n5\n6\n","This is an obtuse scalene triangle.","This is an acute scalene triangle.");
    return $res; 
}

/** @test
    @score  0 
    @prereq standard6 standard7 */
function standard8() { 
    $res = run_and_check("10.5\n10.5\n10\n","This is an isosceles triangle.","This is a scalene triangle.");
    return $res; 
}

/** @test
    @score  0 
    @prereq standard6 standard7 */
function standard9() { 
    $res = run_and_check("6\n8\n5\n","This is an obtuse scalene triangle.","This is an acute scalene triangle.");
    return $res; 
}

/** @test
    @prereq standard8 standard9
    @score  1.0 */
function points()
{
    return _count_case(true);
}


include 'scoring_functions.php';
include 'auto_score.php';

