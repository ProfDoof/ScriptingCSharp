<?php

error_reporting(E_ALL);

// NOTE: This curriculum assumes C++ is available via SCL devtoolset-8 and runnable via "scl enable devtoolset-8 'g++ --version'".

$cflags = "-Wall -pedantic-errors -Werror -Wextra -Wshadow -Wno-unused-but-set-variable -Wno-unused-variable -Wfatal-errors -std=gnu++11";

// -----------------------------------------------------------------------------
// execution functions
// -----------------------------------------------------------------------------

// compile code using $cmd
// any compile error/warning halts testing
function compile($cmd) {
    execute(20,"scl enable devtoolset-8 '$cmd'","",$stdout,$stderr);
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

function compile_test($files,$code='') {
    global $cflags;
    if (!empty($code)) {
        file_put_contents("test.cpp",$code);
        $files .= " test.cpp";
    }

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

    execute(20,"scl enable devtoolset-8 'g++ $cflags test.cpp -c'","",$stdout,$stderr);
    if (!empty($stderr))
        return true;

    hint($description);
    show_file("test.cpp",$code);
    return false;
}

function execution_test($files,&$output) {
    global $cflags;

    if (!compile("g++ $cflags $files -o test_program"))
        return false;

    $output = "";
    execute(20,"./test_program",'',$output,$stderr);

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
    if (!compile("g++ $cflags $files -o assert_test"))
        return false;
    //trace("test compile completed\n");

    for ($i=1; $i<=count($subtests); $i++) {
        execute(20,"./assert_test $i",'',$output,$stderr);
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
        execute(20,"./assert_test $i",'',$output,$stderr);
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
function source_contains(string $code,string $needle):bool {
    if (strpos($code,$needle) !== false)
        return true;
    hint("<b>Source code did not contain expected code.</b></br>Source code should contain <tt>".htmlentities($needle,ENT_NOQUOTES)."</tt>.<br><br>\n");
    return false;
}

// verify program source contains the regular expression
// failure halts testing, $description is used to describe regular expression
function source_contains_regex(string $code,string $needle,string $description):bool {
    if (preg_match($needle,$code) != 0)
        return true;
    hint("<b>Source code did not contain expected code.</b></br>Source code should contain ".htmlentities($description,ENT_NOQUOTES).".<br><br>\n");
    return false;
}

// verify program source does not contain a particular string
// failure halts testing
function source_does_not_contain(string $code,string $needle):bool {
    if (strpos($code,$needle) === false)
        return true;
    hint("<b>Source code contains unexpected code.</b></br>Source code should not contain <tt>".htmlentities($needle,ENT_NOQUOTES)."</tt>.<br><br>\n");
    return false;
}

// verify program source does not contain the regular expression
// failure halts testing, $description is used to describe regular expression
function source_does_not_contain_regex(string $code,string $needle,string $description):bool {
    if (preg_match($needle,$code) == 0)
        return true;
    hint("<b>Source code contains unexpected code.</b></br>Source code should not contain ".htmlentities($description,ENT_NOQUOTES).".<br><br>\n");
    return false;
}

// verify program output contains lines in $needle
// lines must be in order and without additional interior lines
// failure halts testing
function output_contains_lines(string $output,string $needle):bool {
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
function output_does_not_contain_lines(string $output,string $needle):bool {
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

require_once "unit_tests.php";
