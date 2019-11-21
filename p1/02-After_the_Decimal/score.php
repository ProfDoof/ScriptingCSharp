<?php

include 'scoring_functions.php';
include 'auto_score.php';
	
function test_function($in,$arg,$out) {
	run("mod",$in,$output);
	if (strpos($output,$out) === false) {
		_append_log("<b>Function returned incorrect value.</b></br>Given argument $arg, the return value should be $out</br>");
		return false;
	}
	return true;
}
	
/** @test
    @score  0 */
function compiles1() {
    global $files;
    global $cflags;
    $source = file_get_contents($files[0]);
    return compile("g++ $cflags -I. $files[0] -o prog")
       &&  check_blacklist($source);
}

/** @test
 @score  0
 @prereq compiles1 */
function format1() { 
	return run("prog","1.5\n",$output)
	&& output_contains_lines($output,<<<END
Enter number: <span class=input>1.5</span>

After the decimal: 0.5
END
);
}

/** @test
 @score  0
 @prereq compiles1 */
function format2() { 
	return run("prog","99.1875\n",$output)
	&& output_contains_lines($output,<<<END
Enter number: <span class=input>99.1875</span>

After the decimal: 0.1875
END
);
}

/** @test
 @score  0
 @prereq compiles1 */
function format3() { 
	return run("prog","17.3125\n",$output)
	&& output_contains_lines($output,<<<END
Enter number: <span class=input>17.3125</span>

After the decimal: 0.3125
END
);
}

// Use if requiring function
/* @test
    @score  0
    @prereq format1 format2 format3 
// Recompile with extra code to test function directly
function compiles2() { 
    global $files;
    global $cflags;
    $source = file_get_contents($files[0]);
    file_put_contents("modified.cpp",$source.<<<EOF

int function_test()
{
    double F;
    cin >> F;
    cout << digitsAfter(F) << endl;
    exit(0);
}

static int __init=function_test();
EOF
); 
    //print(file_get_contents("modified.cpp")); print("\n\n");
    return compile("g++ $cflags -I. modified.cpp -o mod");
}
*/

/** @test
    @score  0
    @prereq format1 format2 format3 */
function check1() {
//    return test_function("1.0\n","(1.0)","0");
	return run("prog","1.025\n",$output)
	&& output_contains_lines($output,<<<END
Enter number: <span class=input>1.025</span>

After the decimal: 0.025
END
);
}

/** @test
    @score  0
    @prereq format1 format2 format3 */
function check2() {
//    return test_function("9.4\n","(9.4)","0.4");
	return run("prog","9.4\n",$output)
	&& output_contains_lines($output,<<<END
Enter number: <span class=input>9.4</span>

After the decimal: 0.4
END
);
}

/** @test
    @score  0
    @prereq format1 format2 format3 */
function check3() {
//    return test_function("8.1625\n","(8.1625)","0.1625");
	return run("prog","8.1625\n",$output)
	&& output_contains_lines($output,<<<END
Enter number: <span class=input>8.1625</span>

After the decimal: 0.1625
END
);
}

/** @test
    @score  0
    @prereq format1 format2 format3 */
function check4() {
//    return test_function("0.77\n","(0.77)","0.77");
	return run("prog","0.77\n",$output)
	&& output_contains_lines($output,<<<END
Enter number: <span class=input>0.77</span>

After the decimal: 0.77
END
);
}

/** @test
    @score  0
    @prereq format1 format2 format3 */
function check5() {
//    return test_function("6.5185\n","(6.5185)","0.5185");
	return run("prog","6.5185\n",$output)
	&& output_contains_lines($output,<<<END
Enter number: <span class=input>6.5185</span>

After the decimal: 0.5185
END
);
}

/** @test
    @score  0
    @prereq format1 format2 format3 */
function check6() {
//    return test_function("19.239\n","(19.239)","0.239");
	return run("prog","19.239\n",$output)
	&& output_contains_lines($output,<<<END
Enter number: <span class=input>19.239</span>

After the decimal: 0.239
END
);
}

/** @test
    @score  0
    @prereq format1 format2 format3 */
function check7() {
//    return test_function("1423.25\n","(1423.25)","0.25");
	return run("prog","1423.25\n",$output)
	&& output_contains_lines($output,<<<END
Enter number: <span class=input>1423.25</span>

After the decimal: 0.25
END
);
}

/** @test
    @score  0
    @prereq format1 format2 format3 */
function check8() {
//    return test_function("33.82\n","(33.82)","0.82");
	return run("prog","33.82\n",$output)
	&& output_contains_lines($output,<<<END
Enter number: <span class=input>33.82</span>

After the decimal: 0.82
END
);
}

/** @test
    @prereq check1 check2 check3 check4 check5 check6 check7 check8
    @score  1.0 */
function points()
{
    return _count_case(true);
}
