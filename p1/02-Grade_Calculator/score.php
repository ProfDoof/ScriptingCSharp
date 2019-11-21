<?php

include 'scoring_functions.php';
include 'auto_score.php';


function verify_output($in,$out) {
    return run("prog",$in,$output)
    && output_contains_lines($output,"Overall grade: $out"); 
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
function standard1() { return verify_output("88\n90\n91\n85\n80\n",85); }

/** @test
    @score  0 
    @prereq compiles */
function standard2() { return verify_output("85\n85\n85\n85\n88\n",86); }

/** @test
    @score  0
    @prereq compiles */
function standard3() { return verify_output("100\n100\n100\n0\n100\n",83); }

/** @test
    @score  0 
    @prereq compiles */
function standard4() { return verify_output("83\n82\n85\n86\n91\n",86); }

/** @test
    @score  0 
    @prereq compiles */
function standard5() { return verify_output("50\n90\n91\n90\n88\n",82); }

/** @test
    @score  0
    @prereq compiles */
function standard6() { return verify_output("74\n80\n67\n71\n75\n",73); }

/** @test
    @score  0
    @prereq compiles */
function standard7() { return verify_output("98\n93\n87\n85\n90\n",90); }

/** @test 
    @score  0.00
    @prereq standard1 standard2 standard3 standard4 standard5 standard6 standard7 */
function noformat() { return true; }

/** @test
    @score  0 
    @prereq noformat */
function format1() { 
    return run("prog","88\n90\n91\n85\n80\n",$output)
    && output_contains_lines($output,<<<END
Homework: <span class=input>88</span>
Exam #1: <span class=input>90</span>
Exam #2: <span class=input>91</span>
Exam #3: <span class=input>85</span>
Final Exam: <span class=input>80</span>

Overall grade: 85
END
); 
}

/** @test
    @score  0 
    @prereq noformat */
function format2() { 
    return run("prog","75\n85\n82\n79\n73\n",$output)
    && output_contains_lines($output,<<<END
Homework: <span class=input>75</span>
Exam #1: <span class=input>85</span>
Exam #2: <span class=input>82</span>
Exam #3: <span class=input>79</span>
Final Exam: <span class=input>73</span>

Overall grade: 77
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