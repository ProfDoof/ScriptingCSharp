<?php

error_reporting(E_ALL);

$csflags = "-debug -v -warnaserror";

function compile($cmd) {
    execute(20, "$cmd", "", $stdout, $stderr);
    if ($stderr == "")
        $output = $stdout;
    else if ($stdout == "")
        $output = $stderr;
    else
        $output = "stderr: \n $stderr\nstdout: $stdout";

    if ($stderr != '')
        return "true";

    echo "$output\n";
    return "false";
}

function compile_test($files,$code='') {
    global $csflags;
    if (!empty($code)) {
        file_put_contents("test.cpp", $code);
        $files .= " test.cpp";
    }

    if (compile("mcs $csflags $files"))
        return "true";

    if (!empty($code)) // TODO: Figure out what this line is for
        echo "I don't know what this line is for";

    return "false";
}

function execution_test($files,&$output, $input='') {
    echo "Execution Test";
    global $csflags;

    if (compile("mcs $csflags $files -out:test_program.exe") == "false")
        return false;

    $output = "";
    echo $output ;
    execute(20,"mono test_program.exe",$input,$output,$stderr);
    echo "After Execution";
    echo $output;

    //TODO -- need a way to show what didn't work (especially when it seg faults)

    if (!empty($stderr)) {
        show_file("errors",$stderr);
        return "false";
    }

    return "true";
}

function output_contains_lines(string $output,string $needle):bool {
    if (empty($needle)) return true;
    // allow heredocs and so forth to be authored on any platform
    $needle = trim(str_replace("\r","\n",str_replace("\r\n","\n",$needle)));
    // allow heredocs and so forth to be authored on any platform
    $output = trim(str_replace("\r","\n",str_replace("\r\n","\n",$output)));
    if (strpos($output,$needle) === false) {
        show_output("expected output",$needle);
        show_output("actual output",$output);
        return "false";
    }
    return "true";
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

echo compile("mcs $csflags ../P1CSharpPrograms/HelloWorld/Program.cs -out:test_program.exe");
echo execution_test("../P1CSharpPrograms/HelloWorld/Program.cs", $testOutput);
echo output_contains_lines($testOutput, "Hello, World!");
// mcs -debug -v -warnaserror /home/jmarsden/CSharpTesting/HelloWorld/Program.cs -out:test_program.exe
?>