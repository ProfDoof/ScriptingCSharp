<?php

error_reporting(E_ALL);

$csassemblyloader = "-r:../AtheneStreamReader.dll";
$csflags = "-v -warnaserror";

function compile($cmd) {
    echo $cmd."\n";
    execute(20, "$cmd", "", $stdout, $stderr);
    if ($stderr == "")
        $output = $stdout;
    else if ($stdout == "")
        $output = $stderr;
    else
        $output = "stderr: \n $stderr\nstdout: $stdout";

    if ($output == "")
        return true;

    echo "$output\n";
    return false;
}

function compile_test($files,$code='') {
    global $csflags;
    global $csassemblyloader;
    if (!empty($code)) {
        file_put_contents("test.cpp", $code);
        $files .= " test.cpp";
    }

    if (compile("mcs $csassemblyloader $csflags $files"))
        return true;

    if (!empty($code)) // TODO: Figure out what this line is for
        echo "I don't know what this line is for";

    return false;
}

function execution_test($entry, $files,&$output, $input='') {
    global $csflags;
    global $csassemblyloader;

    if (!compile("mcs $csassemblyloader $csflags -main:$entry $files -out:test_program.exe"))
        return false;

    $output = "";
    execute(20,"mono test_program.exe",$input,$output,$stderr);

    //TODO -- need a way to show what didn't work (especially when it seg faults)

    if (!empty($stderr)) {
        show_file("errors",$stderr);
        return false;
    }

    return true;
}

function output_contains_lines(string $output,string $needle) {
    if (empty($needle)) return "true";
    // allow heredocs and so forth to be authored on any platform
    $needle = trim(str_replace("\r","\n",str_replace("\r\n","\n",$needle)));
    // allow heredocs and so forth to be authored on any platform
    $output = trim(str_replace("\r","\n",str_replace("\r\n","\n",$output)));
    if (strpos($output,$needle) === false) {
        show_output("expected output",$needle);
        show_output("actual output",$output);
        return false;
    }
    return true;
}

// TODO: As soon as this is on Athene this needs to be deleted.
function execute($limit,$program,$stdin,&$stdout,&$stderr) {
    // trace("executing $program (limit $limit)\n");
    // Debug code for determining version of compiler
    // exec("g++ --version", $gazebo);
    // _append_log("g++ version: " . var_export($gazebo, true) . "</ br>");
    $descriptorspec = array(
       0 => array("pipe", "r"),  // stdin
       1 => array("pipe", "w"),  // stdout
       2 => array("pipe", "w")); // stderr
       
    global $WINDOWS;
    if (!$WINDOWS)
        $program = "timeout $limit $program"; // ulimit -v 800000;
    else {
        $descriptorspec[1] = array("file",tempnam("./","out"),'w');
        $descriptorspec[2] = array("file",tempnam("./","err"),'w');
        //trace($descriptorspec[1][1]."\n");
        //trace($descriptorspec[2][1]."\n");
    }
    
    $process = proc_open($program,$descriptorspec,$pipes,getcwd());
    if (!is_resource($process)) {
        print("execution error: process could not be opened\n");
        return false;
    }

    fwrite($pipes[0], $stdin);
    fclose($pipes[0]);

    if (!$WINDOWS) {
        // read and close stderr BEFORE stdout, otherwise g++ hangs (not sure why)
        $stderr = stream_get_contents($pipes[2],80000);
        while (fread($pipes[2],10000)); // flush
        fclose($pipes[2]);
        
        $stdout = stream_get_contents($pipes[1],80000);
        while (fread($pipes[1],10000)); // flush
        fclose($pipes[1]);
    }
    $statusArray = proc_get_status($process);
    if($statusArray['exitcode'] == 124)
    {
        $stderr .= "Assignment timed out";
    }

    proc_close($process);
    if ($WINDOWS) {
        //sleep(1);
        $stdout = file_get_contents($descriptorspec[1][1]);
        $stderr = file_get_contents($descriptorspec[2][1]);
        //trace($descriptorspec[1][1]."\n");
        //trace($descriptorspec[2][1]."\n");
    }
    
    $stdout =  @iconv('UTF-8','ASCII//IGNORE',$stdout);
    $stderr =  @iconv('UTF-8','ASCII//IGNORE',$stderr);
    return true;
}

// // Hello World Testing
// echo "Compile Test (Hello World): ".compile("mcs $csassemblyloader $csflags -main:HelloWorldRunner 01-Hello_World/HelloWorldRunner.cs 01-Hello_World/Solutions/Program.cs -out:test_hello_world.exe");
// echo "\n";

// echo "Execution Test (Hello World): ".execution_test("-main:HelloWorldRunner 01-Hello_World/HelloWorldRunner.cs 01-Hello_World/Solutions/Program.cs", $testOutput);
// echo "\n";

// echo "Output Test (Hello World): ".output_contains_lines($testOutput, "Hello, World!");
// echo "\n";

// // Add Two Integers Testing
// echo "Compile Test (Add Two Integers): ".compile("mcs $csassemblyloader $csflags -main:AddTwoIntegersRunner 01-Add_Two_Integers/AddTwoIntegersRunner.cs 01-Add_Two_Integers/Solutions/Program.cs -out:test_add_two_integers.exe");
// echo "\n";

// echo "Execution Test (Add Two Integers): ".execution_test("-main:AddTwoIntegersRunner 01-Add_Two_Integers/AddTwoIntegersRunner.cs 01-Add_Two_Integers/Solutions/Program.cs", $testOutput, "1\n2\n");
// echo "\n";

// echo "Output Test (Add Two Integers): ".output_contains_lines($testOutput,<<<END
// This program adds two numbers.
// 1st number? <span class=input>1</span>
// 2nd number? <span class=input>2</span>
// The total is 3.
// END
// );
// echo "\n";

// echo execution_test("../P1CSharpPrograms/AddTwoIntegers/Program.cs -out:test_program.exe", $testOutput, "1\n2\n");
// echo "\n";
// echo output_contains_lines($testOutput, "This program adds two numbers.\n1st number? 1\n2nd number? 2\nThe total is 3.\n");
// // mcs -debug -v -warnaserror /home/jmarsden/CSharpTesting/HelloWorld/Program.cs -out:test_program.exe
?>