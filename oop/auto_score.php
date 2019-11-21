<?php

$log = '';          // HTML/text visible to user/student after run completes
$score = 0;         // score produced, null or '' is not reported to LMS
$verbose = false;

function trace($msg) {
    global $verbose;
    if ($verbose) fwrite(STDERR,$msg);
}

function _append_log($msg) {
    global $log;
    $log .= $msg;
}

$WINDOWS = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
$MACINTOSH = strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN';

// -----------------------------------------------------------------------------
// execution functions 
// -----------------------------------------------------------------------------

// execute $program, limiting the time of execution to $limit
// "succeeds" even if errors are reported to stderr
// FYI: if limit is reached an error is generated in stderr
function execute($limit,$program,$stdin,&$stdout,&$stderr) {
    trace("executing $program (limit $limit)\n");
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
        _append_log("execution error: process could not be opened\n");
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

function show_file($name,$code,$class='file') {
    _append_log("<b>$name:</b><pre class=$class>".htmlentities ($code,ENT_NOQUOTES)."</pre>");
}

function show_output($name,$output,$class='file') {
    _append_log("<b>$name:</b><pre class=$class>$output</pre>");
}

function execution_name($cmd) {
    global $WINDOWS;
    if (!$WINDOWS)
        $cmd = "./$cmd";
    return $cmd;
}

// execute a command providing $input, capture output
// any runtime error or output written to stderr halts testing
function run($cmd,$input,&$output) {
    return run_non_local(execution_name($cmd),$input,$output);
}

// execute a command providing $input, capture output
// any runtime error or output written to stderr halts testing
function run_non_local($cmd,$input,&$output) {
    trace("running $cmd\n");
    
    execute(20,$cmd,$input,$output,$stderr);
    
//echo $output;
//echo "##############\n";
//echo $stderr;
    
    if ($stderr == '')
        return true;
        
    show_file("Runtime errors",$stderr);
    if (!empty($input)) show_file("Input used",$input);
    if (!empty($output)) show_output("Output",$output); 
    return false;
}

// compile code using $cmd
// any compile error/warning halts testing
function compile($cmd) {
    execute(20,$cmd,"",$stdout,$stderr);
    if ($stderr == "")
        $output = $stdout;
    else if ($stdout == "")
        $output = $stderr;
    else
        $output = "stderr: \n$stderr\nstdout: $stdout";

    if ($output == '')
        return true;

    show_file("Compile errors",$output,'errors');
    return false;
}

$cflags = "-Wall -pedantic-errors -Werror -Wextra -Wshadow -Wfatal-errors -Wno-unused-variable -std=gnu++11";
if (!$MACINTOSH) $cflags .= " -Wno-unused-but-set-variable";

function compile_test($files,$code='') {
    global $cflags; 
    if (!empty($code)) {
        file_put_contents("test.cpp",$code);
        $files .= " test.cpp";
    }

    // switch to "scl enable devtoolset-8 'g++ $cflags ...'" to use 8.3.1 instead of 4.8.5
    if (compile("g++ $cflags $files -c"))
        return true;
        
    if (!empty($code))
        show_file("test.cpp",$code);
    return false;
}

function compile_tests($files,$pre,$post,array $code) {
    foreach ($code as $c)
        if (!compile_test($files,$pre.$c.$post))
            return false;
    return true;
}

function anti_compile_test($description,$code) {
    global $cflags;
    
    file_put_contents("test.cpp",$code);
    
    // switch to "scl enable devtoolset-8 'g++ $cflags ...'" to use 8.3.1 instead of 4.8.5
    execute(20,"g++ $cflags test.cpp -c","",$stdout,$stderr);
    if (!empty($stderr))
        return true;

    _append_log($description);
    show_file("test.cpp",$code);
    return false;
}


function execution_test($files,&$output) {
    global $cflags; 
    
    // switch to "scl enable devtoolset-8 'g++ $cflags $files -o test_program'" to use 8.3.1 instead of 4.8.5
    if (!compile("g++ $cflags $files -o test_program"))
        return false;

    $output = "";
    execute(20,execution_name("test_program"),'',$output,$stderr);
    
    //TODO -- need a way to show what didn't work (especially when it seg faults)
    
    if (!empty($stderr)) {
        show_file("errors",$stderr);
        return false;
    }
    
    return true;
}


function generate_subtests($code) {
    $code = explode("\n",$code);
    $code = array_map('rtrim',$code);
    
    // find number of chunks and 
    $chunks = 1;
    $divides = array();
    $asserts[$chunks-1] = 0;
    foreach ($code as $k=>$line) {
        if (empty($line)) {
            $divides[$chunks-1] = $k;
            $asserts[$chunks] = $asserts[$chunks-1];
            $chunks++;
        }
        else
            $asserts[$chunks-1] += preg_match("/^[ \\t]+assert\\(.+\\);$/",$line);
    }
    $divides[$chunks-1] = $k+1;

    $subtests = array();
    for ($i=0; $i<$chunks; $i++) {
        for ($j=0; $j<$asserts[$i]; $j++) {
            if ($i>0 && $j<$asserts[$i-1]) continue;
            $prog = "";
            $assert_count = 0;
            foreach ($code as $k=>$line) {
                if ($k>=$divides[$i]) break;
                if (preg_match("/^[ \\t]+assert\\(.+\\);$/",$line)) {
                    if ($assert_count++ == $j) $prog .= "$line\n";
                }
                else if (!empty($line))
                    $prog .= "$line\n";
            }
            $subtests[] = $prog;
        }
    }
    
    return $subtests;
}

function generate_subtest_functions($subtests) {
    $functions = "";
    $table = "typedef void (*fp)(); fp tests[] = {";
    foreach ($subtests as $k=>$test) {
        $functions .= "void test$k()\n{\n#line 1 \"test.cpp\"\n$test}\n\n";
        $table .= "test$k,";
    }
    $table .= "};\n\n";
    return "$functions$table";
}

function assertion_tests2($include,$files,$code='') {
    return assertion_tests($files.' -DHEADER=\\"'.$include.'\\" skeleton.cpp',$code);
}

function assertion_tests($files,$code='') {
    global $cflags;

    if (!empty($code)) {
        $subtests = generate_subtests($code);
        //trace("code:\n$code");
        //trace("subtests: ".print_r($subtests,true));
        $code = generate_subtest_functions($subtests);
        file_put_contents("subtests.cpp",$code);
    }
    
    //trace("compiling: $files\n");
    // switch to "scl enable devtoolset-8 'g++ $cflags ...'" to use 8.3.1 instead of 4.8.5
    if (!compile("g++ $cflags $files -o assert_test"))
        return false;
    //trace("test compile completed\n");
    
    for ($i=1; $i<=count($subtests); $i++) {
        execute(20,execution_name("assert_test $i"),'',$output,$stderr);
        if (!empty($stderr)) {
            show_file("error",$stderr);
            show_file("test.cpp",$subtests[$i-1]);
            return false;
        }
    }
    return true;
}

function assertion_tests3($files,$includes,$code) {
    global $cflags;

    $subtests = generate_subtests($code);
    $code = generate_subtest_functions($subtests);
    $includes[] = "<cstdlib>";
    $includes[] = "<cstdio>";
    $includes[] = "<iostream>";
    
    $skeleton = "#include ".implode("\n#include ",$includes)."\n".<<<EOF
#ifdef assert
    #undef assert
#endif
#define assert(x) if (x) ; else (fprintf(stderr,"%s:%d: assert(%s) failed\\n",__FILE__,__LINE__,#x),exit(1))
$code
#if WIN32
    #define WIN32_LEAN_AND_MEAN 1
    #include <windows.h>
#endif
int main(int argc,char *argv[])
{
#if WIN32
    DWORD dwMode = SetErrorMode(SEM_NOGPFAULTERRORBOX);
    SetErrorMode(dwMode | SEM_NOGPFAULTERRORBOX);
#endif
    if (argc != 2)
        fprintf(stderr,"must have exactly one argument (not %d)\\n",argc);
    else {
        int i = atoi(argv[1]);
        if (i == 0)
            fprintf(stderr,"argument must be positive integer\\n");
        else
            tests[i-1]();
    }
}
EOF;
    file_put_contents("assert_test.cpp",$skeleton);

    //trace("compiling: $files\n");
    // switch to "scl enable devtoolset-8 'g++ $cflags ...'" to use 8.3.1 instead of 4.8.5
    if (!compile("g++ $cflags $files assert_test.cpp -o assert_test")) {
        // TODO -- this displays too much information
        // show_file("test.cpp",$code);
        return false;
    }
    //trace("test compile completed\n");
    
    for ($i=1; $i<=count($subtests); $i++) {
        execute(20,execution_name("assert_test $i"),'',$output,$stderr);
        if (!empty($stderr)) {
            show_file("error",$stderr);
            show_file("test.cpp",$subtests[$i-1]);
            return false;
        }
    }
    return true;
}

// -----------------------------------------------------------------------------
// source verification functions 
// -----------------------------------------------------------------------------

// verify program source contains a particular string
// failure halts testing
function source_contains($code,$needle) {
    if (strpos($code,$needle) !== false)
        return true;
    _append_log("<b>Source code did not contain expected code.</b></br>Source code should contain <tt>".htmlentities($needle,ENT_NOQUOTES)."</tt>.<br><br>\n");
    return false;
}

// verify program source contains the regular expression
// failure halts testing, $description is used to describe regular expression
function source_contains_regex($code,$needle,$description) {
    if (preg_match($needle,$code) != 0)
        return true;
    _append_log("<b>Source code did not contain expected code.</b></br>Source code should contain ".htmlentities($description,ENT_NOQUOTES).".<br><br>\n");
    return false;
}

// verify program source does not contain a particular string
// failure halts testing    
function source_does_not_contain($code,$needle) {
    if (strpos($code,$needle) === false)
        return true;
    _append_log("<b>Source code contains unexpected code.</b></br>Source code should not contain <tt>".htmlentities($needle,ENT_NOQUOTES)."</tt>.<br><br>\n");
    return false;
}

// verify program source does not contain the regular expression
// failure halts testing, $description is used to describe regular expression
function source_does_not_contain_regex($code,$needle,$description) {
    if (preg_match($needle,$code) == 0)
        return true;
    _append_log("<b>Source code contains unexpected code.</b></br>Source code should not contain ".htmlentities($description,ENT_NOQUOTES).".<br><br>\n");
    return false;
}

// verify program output contains lines in $needle
// lines must be in order and without additional interior lines
// failure halts testing
function output_contains_lines($output,$needle) {
    if (empty($needle)) return true;
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

// verify program output DOES NOT contain each line in $needle
// lines must be in order and without additional interior lines
// failure halts testing
function output_does_not_contain_lines($output,$needle) {
    if (empty($needle)) return true;
    // allow heredocs and so forth to be authored on any platform
    $needle = trim(str_replace("\r","\n",str_replace("\r\n","\n",$needle)));
    // allow heredocs and so forth to be authored on any platform
    $output = trim(str_replace("\r","\n",str_replace("\r\n","\n",$output)));
    if (strpos($output,$needle) !== false) {
        show_output("forbidden output",$needle);
        show_output("actual output",$output);
        return false;
    }
    return true;
}


// -----------------------------------------------------------------------------
// report functions 
// -----------------------------------------------------------------------------

function message($msg) {
    _append_log($msg);
    return true;
}


// parse command line parameters
$verbose = false;
$files = array();
$usage = false;
foreach ($argv as $k=>$a) {
    if ($k==0) continue;
    if ($a == '-h' || $a == '--help') 
        $usage = true;
    else if ($a =='-v' || $a == '--verbose')
        $verbose = true;
    else if ($a[0] == '-')
        $usage = true;
    else 
        $files[] = $a;
}

if ($usage || count($files)<1) {
    echo "usage: score.php [-h] [-v] <summitted-files-list>\n";
    exit;
}

// find all test functions
// find prereq list for each function
$allfuncs = get_defined_functions();
foreach ($allfuncs['user'] as $f) {
    //trace("found: $f\n");
    $attribs = array('status'=>'READY','duration'=>0);
    $rf = new ReflectionFunction($f);
    $cmt = trim(trim($rf->getDocComment(),'/'),'* ');
    preg_match_all('|@(\\w+)(?:[ \\t]+([^\\n]*))?|',$cmt,$matches,PREG_SET_ORDER);
    foreach ($matches as $pair)
        $attribs[$pair[1]]=trim(@$pair[2]);
    if (array_key_exists('prereq',$attribs))
        $attribs['prereq'] = explode(' ',$attribs['prereq']);
    else 
        $attribs['prereq'] = array();
    if (array_key_exists('test',$attribs))
        $test[$f]=(object)$attribs;
}

// $test[name]->prereq[]    list of prereqs for test function
// $test[name]->status      status of test (READY,FAILED,OK)
trace("will test: ".implode(", ",array_keys($test))."\n");

$done = false;
while (!$done) {
    $done = true;
    foreach ($test as $t=>$v) {
        if ($v->status == 'READY') {
            $ok = true;
            foreach ($v->prereq as $p)
                $ok &= (@$test[$p]->status=='OK');
            if ($ok) {
                $done = false;
                trace("\n----------------- run test $t ------------------\n");
                $start_time = time();
                $good = call_user_func($t);
                $v->duration = time()-$start_time;
                trace("$t took $v->duration seconds\n");
                if ($good) {
                    $v->status = 'OK';
                    $score += $v->score;
                }
                else {
                    $v->status = 'FAILED';
                    trace("test $t FAILED\n");
                    break;
                }
            }
        }
    }
}

trace("\nRESULTS:\n");
foreach ($test as $t=>$v)
    trace(sprintf("%-25s%-9s%5.2f%5d secs\n",$t,$v->status,$v->score,$v->duration));
trace("\n");

if ($log == '')
	$log = "Success.\n";
    
$result = (object)array();
$result->score = round($score,5);
$result->log = substr($log,0,2000);
$result->parts = []; // TODO this should be the parts of the source that should be considered for diff'ing for cheat detection
echo json_encode($result);
