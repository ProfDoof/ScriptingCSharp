<?php

include '../scripting1csharp_scoring.php';
$runner = "AddTwoIntegersRunnerNoNamespace.cs";
$entryMain = "AddTwoIntegersRunner";

function compilation_test() {
    global $csassemblyloader;
    global $csflags;
    global $files;
    global $entryMain;
    global $runner;
    return compile("mcs $csassemblyloader $csflags -main:$entryMain $runner $files[0] -out:test_program.exe");
}

function check_1_and_2() {
    global $files;
    global $entryMain;
    global $runner;
    return execution_test($entryMain, "$runner $files[0]", $testOutput, "1\n2\n") && 
           output_contains_lines($testOutput, <<<END
This program adds two numbers.
1st number? <span class=input>1</span>
2nd number? <span class=input>2</span>
The total is 3.
END
        );
}

function check_2_and_3() {
    global $files;
    global $entryMain;
    global $runner;
    return execution_test($entryMain, "$runner $files[0]", $testOutput, "2\n3\n") && 
           output_contains_lines($testOutput, <<<END
This program adds two numbers.
1st number? <span class=input>2</span>
2nd number? <span class=input>3</span>
The total is 5.
END
        );
}

function check_10_and_13() {
    global $files;
    global $entryMain;
    global $runner;
    return execution_test($entryMain, "$runner $files[0]", $testOutput, "10\n13\n") && 
           output_contains_lines($testOutput, <<<END
This program adds two numbers.
1st number? <span class=input>10</span>
2nd number? <span class=input>13</span>
The total is 23.
END
        );
}

function check_126_and_847() {
    global $files;
    global $entryMain;
    global $runner;
    return execution_test($entryMain, "$runner $files[0]", $testOutput, "126\n847\n") && 
           output_contains_lines($testOutput, <<<END
This program adds two numbers.
1st number? <span class=input>126</span>
2nd number? <span class=input>847</span>
The total is 973.
END
        );
}

$files = ["/home/johnmarsden/PratherTA/scripting1csharp/01-Add_Two_Integers/Solutions/ProgramNoNamespace.cs"];
echo "Compile Test: ".compilation_test()."\n";
echo "Check Execution (1 and 2): ".check_1_and_2()."\n";
echo "Check Execution (2 and 3): ".check_2_and_3()."\n";
echo "Check Execution (10 and 13): ".check_10_and_13()."\n";
echo "Check Execution (126 and 847): ".check_126_and_847()."\n";

// Before this next portion gets used I need to get some regex set up to mangle the Runner file into
// the correct state to run a program whether it has a namespace or not.
// $files = ["/home/johnmarsden/PratherTA/scripting1csharp/01-Add_Two_Integers/Solutions/Program.cs"];
// echo "Compile Test: ".compilation_test()."\n";
// echo "Check Execution (1 and 2): ".check_1_and_2()."\n";
// echo "Check Execution (2 and 3): ".check_2_and_3()."\n";
// echo "Check Execution (10 and 13): ".check_10_and_13()."\n";
// echo "Check Execution (126 and 847): ".check_126_and_847()."\n";
// echo "Execution Test (Add Two Integers): ".execution_test("./AddTwoIntegersRunner.cs ./Solutions/Program.cs", $testOutput, "1\n2\n");
// echo "\n";

// echo "Output Test (Add Two Integers): ".output_contains_lines($testOutput,<<<END
// This program adds two numbers.
// 1st number? <span class=input>1</span>
// 2nd number? <span class=input>2</span>
// The total is 3.
// END
// );
// echo "\n";
?>