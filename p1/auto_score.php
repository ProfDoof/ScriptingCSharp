<?php

include_once 'source_parser.php';
include_once 'error_element.php';
include_once 'error_markup.php';

$log = "";//'<i>You passed 0 test cases.</i><br><br>';          // HTML/text visible to user/student after run completes
$score = 0;         // score produced, null or '' is not reported to LMS
$verbose = false;

$cases = 0;  // count how many test cases passed

$error_type = "";   //which compile error is generated (empty if none)
                    //used to retrieve proper example code for enhanced error message

/*
    Called automatically after success in assertion test or output_contains_lines, 
    Called manually from score.php to note "all" cases passed (not specific number)
*/
function _count_case($last = false)
{
    global $cases, $log;

    if( $last )
    {
        //$log = "You passed all of the test cases"; //str_replace("You passed $cases test cases","You passed all test cases",$log);
        $cases = "all";
    }
    else 
    {   
        //$log = str_replace("You passed $cases test cases","You passed ".strval($cases+1)." test cases",$log);
        //$log = "You passed " . strval($cases+1) . " of the test cases";
        $cases = $cases + 1;
    }
    //echo("# cases = ".strval($cases)." (".$mark.")\n");
    return true;
}

error_reporting(E_ALL);

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
    
    // I have postponed this conversion until later
    // because I need UTF-8 chars that were being stripped
    //$stdout =  @iconv('UTF-8','ASCII//IGNORE',$stdout);
    //$stderr =  @iconv('UTF-8','ASCII//IGNORE',$stderr);
    return true;
}

function show_file($name,$code,$class='file') {
    _append_log("<b>$name:</b><pre class=$class>".htmlentities($code,ENT_NOQUOTES)."</pre>");
}

function show_output($name,$output,$class='file') {
    _append_log("\n<b>$name:</b>\n<pre class=$class>$output</pre>\n");
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

// helper-function for 'compose_feedback'
function compose_case(&$feedback,AtheneErrorCase $case,$lineNo,array $variableTokens) {
    $result = $case->get_result($variableTokens);
    // only include the result if it had content
    if ($result == "")
        return false;
    $feedback .= "<p>On line $lineNo: $result</p>";
    return true;
}

// compose verbose error feedback based on the specified 
// compiler message; returns null if the error case
// represented in the compiler message is not supported
// by the markup definition system; any parse errors are 
// placed into $errorOutput (regardless of success)
function compose_feedback($compilerMessage,&$errorOutput)
{
    $feedback = null;
    $errorOutput = '';
    $fileErrors = new GPPFileNameElement; // this object will parse GCC compiler output for a single source file element
    if ( $fileErrors->read_compiler_output($compilerMessage) ) { // error output was successfully understood
        $errorManager = new AtheneErrorManager; // create an error case manager
        $caseHandled = false; // we only want to show feedback if a case was actually successfully handled
        $preFeedback = "<h3> Feedback for submission file '{$fileErrors->get_file_name()}':<br /></h3>";

        // append any errors that may have occurred during
        // parsing the markup file(s)
        $errorOutput .= $errorManager->get_parse_error_log();

        // go through each scope description
        while (true) {
            $nextScope = $fileErrors->get_next_scope();
            if ( $nextScope == null )
                break;

            // prepare the scope description message
            $sdesc = $nextScope->get_tokenized_source_string();
            if ( $sdesc == "In function %1:" ) {
                $vtoks = $nextScope->get_variable_tokens();
                $sdesc = "in a function defined in your program called '" . $vtoks['%1'] . "':";
            }
            else
                $sdesc = "in your program:";

            // go through any errors, warnings, or fatal errors
            // that were found in the scope description

            // handle source strings which the compiler flagged as 'error'
            $errCnt = $nextScope->get_number_of_errors();
            if ( $errCnt > 0 ) {
                $preFeedback .= "<b>The following error" . ($errCnt>1 ? "s were " : " was ") . "found " . $sdesc . "</b>";
                while (true) {
                    $nextError = $nextScope->get_next_error();
                    if ( $nextError == null )
                        break;
                    $case = $errorManager->get_error_case( $nextError->get_tokenized_source_string() );
                    if ( $case != null ) { // the case is supported
                        if ( compose_case($preFeedback,$case,$nextError->get_line_no(),$nextError->get_variable_tokens()) )
                            $caseHandled = true;
                        $errorOutput .= $case->get_parse_error_log(); // append parse errors, if any
                    }
                }
            }

            // handle source strings which the compiler flagged as 'warning'
            $warnCnt = $nextScope->get_number_of_warnings();
            if ( $warnCnt > 0 ) {
                $preFeedback .= "<b>The following warning" . ($errCnt>1 ? "s were " : " was ") . "found " . $sdesc . "</b>";
                while (true) {
                    $nextWarning = $nextScope->get_next_warning();
                    if ( $nextWarning == null )
                        break;
                    $case = $errorManager->get_error_case( $nextWarning->get_tokenized_source_string() );
                    if ( $case != null ) { // the case is supported
                        if ( compose_case($preFeedback,$case,$nextWarning->get_line_no(),$nextWarning->get_variable_tokens()) )
                            $caseHandled = true;
                        $errorOutput .= $case->get_parse_error_log(); // append parse errors, if any
                    }
                }
            }

            // handle source strings which the compiler flagged as 'fatal error'
            $fatalErrCnt = $nextScope->get_number_of_fatalerrs();
            if ( $fatalErrCnt > 0 ) {
                $preFeedback .= "<b>The following fatal error" . ($errCnt>1 ? "s were " : " was ") . "found " . $sdesc . "</b>";
                while (true) {
                    $nextFatalErr = $nextScope->get_next_fatalerr();
                    if ( $nextFatalErr == null )
                        break;
                    $case = $errorManager->get_error_case( $nextFatalErr->get_tokenized_source_string() );
                    if ( $case != null ) { // the case is supported
                        if ( compose_case($preFeedback,$case,$nextFatalErr->get_line_no(),$nextFatalErr->get_variable_tokens()) )
                            $caseHandled = true;
                        $errorOutput .= $case->get_parse_error_log(); // append parse errors, if any
                    }
                }
            }
        }

        // if a case was handled, set $feedback so that the feedback is added to the log
        if ( $caseHandled )
            $feedback = $preFeedback;
    }
    return $feedback;
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

    // get verbose compiler error feedback
    $feedback = compose_feedback($output,$parseErrors);
    if ( $parseErrors != '' )
        show_file("Parse errors",@iconv('UTF-8','ASCII//TRANSLIT',$parseErrors),'parse-errors');
    show_file("Compile errors",@iconv('UTF-8','ASCII//TRANSLIT',$output),'errors'); // preserve original compiler error output
    if ( $feedback != null )
        _append_log( @iconv('UTF-8','ASCII//TRANSLIT',$feedback) ); // add verbose feedback to the page
    return false;
}


// helper-function for 'compose_feedback'
// duplicate of above; for hiding feedback in expandable section
function compose_hidden_case(&$feedback,AtheneErrorCase_hidden $case,$lineNo,array $variableTokens) {
    $result = $case->get_result($variableTokens);
    // only include the result if it had content
    if ($result == "")
        return false;
    $feedback .= "<p>On line $lineNo: $result</p>";
    return true;
}

// compose verbose error feedback based on the specified 
// compiler message; returns null if the error case
// represented in the compiler message is not supported
// by the markup definition system; any parse errors are 
// placed into $errorOutput (regardless of success)
// duplicate of above; for hiding feedback in expandable section
function compose_hidden_feedback($compilerMessage,&$errorOutput)
{
    $feedback = null;
    $errorOutput = '';
    $fileErrors = new GPPFileNameElement; // this object will parse GCC compiler output for a single source file element
    if ( $fileErrors->read_compiler_output($compilerMessage) ) { // error output was successfully understood
        $errorManager = new AtheneErrorManager_hidden; // create an error case manager
        $caseHandled = false; // we only want to show feedback if a case was actually successfully handled
        $preFeedback = "<h3> Feedback for submission file '{$fileErrors->get_file_name()}':<br /></h3>";

        // append any errors that may have occurred during
        // parsing the markup file(s)
        $errorOutput .= $errorManager->get_parse_error_log();

        // go through each scope description
        while (true) {
            $nextScope = $fileErrors->get_next_scope();
            if ( $nextScope == null )
                break;

            // prepare the scope description message
            $sdesc = $nextScope->get_tokenized_source_string();
            if ( $sdesc == "In function %1:" ) {
                $vtoks = $nextScope->get_variable_tokens();
                $sdesc = "in a function defined in your program called '" . $vtoks['%1'] . "':";
            }
            else
                $sdesc = "in your program:";

            // go through any errors, warnings, or fatal errors
            // that were found in the scope description

            // handle source strings which the compiler flagged as 'error'
            $errCnt = $nextScope->get_number_of_errors();
            if ( $errCnt > 0 ) {
                $preFeedback .= "<b>The following error" . ($errCnt>1 ? "s were " : " was ") . "found " . $sdesc . "</b>";
                while (true) {
                    $nextError = $nextScope->get_next_error();
                    if ( $nextError == null )
                        break;
                    $case = $errorManager->get_error_case( $nextError->get_tokenized_source_string() );
                    if ( $case != null ) { // the case is supported
                        if ( compose_hidden_case($preFeedback,$case,$nextError->get_line_no(),$nextError->get_variable_tokens()) )
                            $caseHandled = true;
                        $errorOutput .= $case->get_parse_error_log(); // append parse errors, if any
                    }
                }
            }

            // handle source strings which the compiler flagged as 'warning'
            $warnCnt = $nextScope->get_number_of_warnings();
            if ( $warnCnt > 0 ) {
                $preFeedback .= "<b>The following warning" . ($errCnt>1 ? "s were " : " was ") . "found " . $sdesc . "</b>";
                while (true) {
                    $nextWarning = $nextScope->get_next_warning();
                    if ( $nextWarning == null )
                        break;
                    $case = $errorManager->get_error_case( $nextWarning->get_tokenized_source_string() );
                    if ( $case != null ) { // the case is supported
                        if ( compose_hidden_case($preFeedback,$case,$nextWarning->get_line_no(),$nextWarning->get_variable_tokens()) )
                            $caseHandled = true;
                        $errorOutput .= $case->get_parse_error_log(); // append parse errors, if any
                    }
                }
            }

            // handle source strings which the compiler flagged as 'fatal error'
            $fatalErrCnt = $nextScope->get_number_of_fatalerrs();
            if ( $fatalErrCnt > 0 ) {
                $preFeedback .= "<b>The following fatal error" . ($errCnt>1 ? "s were " : " was ") . "found " . $sdesc . "</b>";
                while (true) {
                    $nextFatalErr = $nextScope->get_next_fatalerr();
                    if ( $nextFatalErr == null )
                        break;
                    $case = $errorManager->get_error_case( $nextFatalErr->get_tokenized_source_string() );
                    if ( $case != null ) { // the case is supported
                        if ( compose_hidden_case($preFeedback,$case,$nextFatalErr->get_line_no(),$nextFatalErr->get_variable_tokens()) )
                            $caseHandled = true;
                        $errorOutput .= $case->get_parse_error_log(); // append parse errors, if any
                    }
                }
            }
        }

        // if a case was handled, set $feedback so that the feedback is added to the log
        if ( $caseHandled )
            $feedback = $preFeedback;
    }
    return $feedback;
}


// compile code using $cmd
// any compile error/warning halts testing
// duplicate of above; for hiding feedback in expandable section
function compile_hide_feedback($cmd) {
    //_append_log("This addition is at the beginning of where we hide feedback.\n");
    execute(20,$cmd,"",$stdout,$stderr);
    if ($stderr == "")
        $output = $stdout;
    else if ($stdout == "")
        $output = $stderr;
    else
        $output = "stderr: \n$stderr\nstdout: $stdout";

    if ($output == '')
        return true;

    //since we only care about 4 errors
    //determine error here, if any
    global $error_type;
    if ( $error_type == "" ) {
        if ( strpos($output, 'comparison between signed and unsigned integer expressions') !== false ) {
            $error_type = "unsigned";
        } else if ( strpos($output, '\'setw\' was not declared in this scope') !== false ) {
            $error_type = "setw";
        } else if ( strpos($output, 'no return statement in function returning non-void') !== false ) {
            $error_type = "non-void";
        } else if ( strpos($output, 'was not declared in this scope') !== false ) {
            $error_type = "undeclared";
        }
    }

    // get verbose compiler error feedback
    $feedback = compose_hidden_feedback($output,$parseErrors);
    if ( $parseErrors != '' )
        show_file("Parse errors",@iconv('UTF-8','ASCII//TRANSLIT',$parseErrors),'parse-errors');
    show_file("Compile errors",@iconv('UTF-8','ASCII//TRANSLIT',$output),'errors'); // preserve original compiler error output
    if ( $feedback != null ) {

        //here we insert the enhanced feedback into $log inside a section 
        //that is hidden until a button is pressed
        //this should have its own function in the future
        /*$beginning_of_button = "<button onclick=\"myFunction()\">Need More Help?</button><div id=\"myDIV\" style=\"display: none;\"> <style> div { padding: 20px; border: 10px solid gray; margin: 10; }</style>";
        $end_of_button = "</div><script> function myFunction() { var x = document.getElementById('myDIV'); if (x.style.display === 'none') { x.style.display = 'block'; } else { x.style.display = 'none'; }}</script>";*/

        //$feedback = "<p>".$error_type."</p>";

        /*$feedback .= "<p>".format_cpp(file_get_contents('./122_14-Most_Frequent_Character_2015-12-02-22-51-22.cpp', true))."</p>";
        $feedback .= "<p>".format_cpp(file_get_contents('./fixed.cpp', true))."</p>";*/


        $feedback = create_button_and_hide_feedback($feedback);

        _append_log( @iconv('UTF-8','ASCII//TRANSLIT', $feedback) );

        /*_append_log( @iconv('UTF-8','ASCII//TRANSLIT', $beginning_of_button) );

        _append_log( @iconv('UTF-8','ASCII//TRANSLIT', $feedback) ); // add verbose feedback to the page

        _append_log( @iconv('UTF-8','ASCII//TRANSLIT', $end_of_button) );*/

        //_append_log("<b>$name:</b><pre class=$class>".htmlentities ($code,ENT_NOQUOTES)."</pre>")
    }
    return false;
}

//adds a small (?) next to all "vocab" words
//icons can be hovered over to get a definition of the word
function format_definitions($feedback) {
    //add font size to match example code line for enhanced feedback after <h3> title of "Feedback for submission file..."
    $feedback = str_replace("<b>", "<font size=\"4\"><b>", $feedback);
    $feedback = str_replace("</b>", "</b></font><font size=\"4\">", $feedback)."</font>";
    $feedback = str_replace("<font size=\"4\"><font size=\"4\">", "<font size=\"4\">", $feedback);

    //save substring of $feedback that cuts off the header
    $temp_feedback = substr($feedback, strpos($feedback, ":<br></h3>") - sizeof($feedback) + 11);

    //save substring of $temp_feedback 
                                                                    //or "<p> ... ) - sizeo ... + 1"
    $temp_feedback = substr($temp_feedback, strpos($temp_feedback, ":</b></font><font size=\"4\">") - sizeof($temp_feedback) + 28);
    
    //goal of the above substring creations is to isolate the portion of the feedback that contains the helpful messages

    //hard-coded definitions for testing now, not for extending later
    $vocab = array(
       0 => array("to use a function", "called", "call"),
       1 => array("to assign a value to a variable or to write code for a function", "definition", "defined", "define"),
       2 => array("the creation of a variable or function", "declaration", "declared", "declare"),
       3 => array("capable of representing only positive integers and zero", "unsigned"),
       4 => array("capable of representing all integers, positive, negative, and zero", " signed"), //space is intentional to distinguish 'signed' from un'signed'
       5 => array("to explicitly change a variable from one type to another, such as float to integer", "type-cast"),
       #6 => array("the variable type returned by a function, specified in the word before the function's name", "return type"), was replaced by:
       6 => array("the type specified before the function name that shows what type of variable will be returned in the function, e.g. void (no return type), int, flaot, char, etc.", "function return type", "return type"),
       7 => array("return type of nothing; a function that does not return a value", "void"),
       8 => array("function in <iomanip> that adds a set number of spaces to a portion of output", "setw"),
       9 => array("the portion of a program in which a variable exists", "scope"),
       10 => array("preexisting variable definitions and functions that can used within a program when included at the top of a source file; denoted using #include", "header"),
       11 => array("Equals sign (=)", "assignment operator"),
       12 => array("A statement that compares values to return true or false, usually an if statement", "conditional statement", "conditional") #second definition for "conditional" not used
       ); //eliminated "in scope" from 9

    //loop through all vocab words
    for ($i = 0; $i < sizeof($vocab); $i++) {
        //loop through all forms of the vocab word
        $first_instance = -1;   //-1 if not found yet
        for ($j = 1; $j < sizeof($vocab[$i]); $j++) {
            //if a vocab word is in the feedback
            if (strpos($temp_feedback, $vocab[$i][$j]) && (strpos($temp_feedback, $vocab[$i][$j]) != strpos($temp_feedback, $vocab[$i][$j - 1]) || $j == 1) && strpos($temp_feedback, $vocab[$i][$j]) != strpos($temp_feedback, $vocab[$i][$j]."<")) {  //returns false if not in, otherwise return number of beginning of found string
                //if not found yet or found a form of the vocab word earlier than the earliest
                if ($first_instance < 0 || strpos($temp_feedback, $vocab[$i][$j]) < strpos($temp_feedback, $vocab[$i][$first_instance])) {
                    //first_instance holds index in $temp_feedback string of the first occurrence of the vocab word
                    $first_instance = $j;
                }
            }
        }

        if ($first_instance >= 0) { //then it was found
            $start_of_vocab_word = strpos($temp_feedback, $vocab[$i][$first_instance]);
            $where_to_insert = ($start_of_vocab_word - strlen($temp_feedback) + strlen($vocab[$i][$first_instance]));

            $new_feedback = substr($temp_feedback, 0, $where_to_insert);  //string from start of $temp_feedback to the space after the vocab word
            
            $start_of_last_append = strlen($new_feedback);

            //string of question mark hover icon with vocab definition
            $new_feedback .= "<a data-toggle=\"popover\" data-placement=\"bottom\" data-content=\"".$vocab[$i][0]."\"><span class=\"glyphicon glyphicon-question-sign\" aria-hidden=\"true\"></span></a>";

            //the rest of $temp_feedback
            $new_feedback .= substr($temp_feedback, $start_of_last_append);


            //echo "new last: $new_feedback\n";
            //save changes to temp_feedback
            $temp_feedback = $new_feedback;
        }
    }

    //append $temp_feedback back into its original place in $feedback, and return
    return substr($feedback, 0, strpos($feedback, ":</b>") - strlen($feedback) + 5).$temp_feedback;
}

//formats example code
//adds highlights to important lines
//$mark_lines is an array of lines that need to be highlighted
//the array has different values depending on the error
function format_cpp($cpp, $mark_lines) {
    global $error_type; //describes the type of error found by Athene (that we care about)
    $extra_feedback = "";   //to return
    $openedCurlies = 0; //number of '{' seen
    $counter = 0;   //keeps track of current line
    foreach(preg_split("/((\r?\n)|(\r\n?))/", $cpp) as $line){
        $line = str_replace("<", "&lt;", $line);
        $line = str_replace(">", "&gt;", $line);
        //$line = str_replace("*", "\*", $line);

        //for every open curly that is not closed, add a tab to the beginning of the line
        /*if (strpos($line, '}') !== false) {
            $openedCurlies--;
        }

        //$added_whitespace = "test";
        for ($i = 0; $i < $openedCurlies; $i++) {
            $line = "&emsp;".$line;
            //$added_whitespace .= "&emsp;";
        }

        if (strpos($line, '{') !== false) {
            $openedCurlies++;
        }*/

        //if this is a line to mark
        $to_mark = false;
        //marks line with problem after whitespace is added
        foreach($mark_lines as $line_to_mark) {
            if ($counter == $line_to_mark) {
                $to_mark = true;
                break;
            }
        }
        if ($to_mark) {
            $extra_feedback .= "<p class=\"bg-primary\">".$line."</p>";
        } else {
            $extra_feedback .= "<p>".$line."</p>";
        }

        $counter++; //next line
    }

    //return code with highlights in a <pre> tag
    return "<pre>".$extra_feedback."</pre>";
}

function create_button_and_hide_feedback($feedback) {
    //creates button, border around hidden section
    /*$all_feedback = "<button onclick=\"myFunction()\">Need More Help?</button><div id=\"myDIV\" style=\"display: none;\"> <style> div { padding: 10px; border: 10px solid gray; margin: 10; }</style>";*/

    //these are divided up for debug purposes.

    //bootstrap and jquery
    $all_feedback = "<link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css\" integrity=\"sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u\" crossorigin=\"anonymous\"><link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css\" integrity=\"sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp\" crossorigin=\"anonymous\"><script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js\"></script><script src=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js\" integrity=\"sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa\" crossorigin=\"anonymous\"></script>";

    //big blue button
    //$all_feedback .= "<button class=\"btn btn-primary btn-block\" onclick=\"myFunction()\"><font size = \"3\">Need More Help?</font></button>";    //start of button div

    //full div that button controls
    //$all_feedback .= "<div id=\"myDIV\" style=\"display: none;\">";

    //collapsible feedback section with bootstrap accordion instead of button
    $all_feedback .= "<div class=\"panel-group\" id=\"accordion\"><div class=\"panel panel-default\"><div class=\"panel-heading\" style=\"background-color:#f5f5f5!important;background-image: none!important;border: none;\"><h4 class=\"panel-title\" style=\"display: block;\"><a class=\"accordion-toggle\" data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapseOne\" style=\"display: block\"><span class=\"glyphicon glyphicon-triangle-right\"></span>More Information</a></h4></div><div id=\"collapseOne\" class=\"panel-collapse collapse\" style=\"background-color:#f5f5f5!important;\"><div class=\"panel-body\" style=\"important;background-image: none!important;border: none;\">";

    //puts enhanced error message in the hidden section
    $all_feedback .= format_definitions($feedback);
    
    //title of enhanced section, bad code section
    //beginning of removing code snippets
    /*$all_feedback .= "<p style=\"padding:10px 0px 0px 0px;\"><font size=\"4\"><b>Here is an example of code that caused the same error and how it was fixed.</b></font></p><div id=\"badCode\" style = \"display: myDiv.display;\"><p><b>Incorrect Code:</b></p>";

    $bad_code = "bad_code"; //will eventually hold .cpp contents, if an important error is found
    $good_code = "good_code";   //will eventually hold .cpp contents, if an important error is found
    $mark_array_bad = null; //will hold values of lines to mark, specific to the .cpp, if an important error is found
    $mark_array_good = null;    //will hold values of lines to mark, specific to the .cpp, if an important error is found
    global $error_type;
    if ( $error_type == "unsigned" ) {
        //these filenames do not relate to the code anymore, but they have not changed yet
        if (file_exists('./122_14-Most_Frequent_Character_2015-12-02-22-51-22.cpp')) {
            $bad_code = file_get_contents('./122_14-Most_Frequent_Character_2015-12-02-22-51-22.cpp', true);    
        } else {
          $bad_code = file_get_contents('./154_08-Fibonacci_2015-09-20-23-05-10.cpp', true);  
        }
        
        if (file_exists('./fixed.cpp')) {
            $good_code = file_get_contents('./fixed.cpp', true);
        } else {
          $good_code = file_get_contents('./154_08-Fibonacci_2015-09-20-23-06-03.cpp', true);
        }

        $mark_array_bad = array(8);
        $mark_array_good = array(8);
    } else if ( $error_type == "setw" ) {
        if (file_exists('./233_10-Pascal_Triangle_2014-10-14-22-55-38.cpp')) {
            $bad_code = file_get_contents('./233_10-Pascal_Triangle_2014-10-14-22-55-38.cpp', true);    
        } else {
            //this is from debugging, as we could always get Fib to display
          $bad_code = file_get_contents('./154_08-Fibonacci_2015-09-20-23-05-10.cpp', true);  
        }
        
        if (file_exists('./233_10-Pascal_Triangle_2014-10-14-22-58-24.cpp')) {
            $good_code = file_get_contents('./233_10-Pascal_Triangle_2014-10-14-22-58-24.cpp', true);
        } else {
          $good_code = file_get_contents('./154_08-Fibonacci_2015-09-20-23-06-03.cpp', true);
        }

        $mark_array_bad = array(7);
        $mark_array_good = array(1);
    } else if ( $error_type == "non-void" ) {
        if (file_exists('./154_12-Exploding_Integer_2015-11-28-22-24-40.cpp')) {
            $bad_code = file_get_contents('./154_12-Exploding_Integer_2015-11-28-22-24-40.cpp', true);    
        } else {
          $bad_code = file_get_contents('./154_08-Fibonacci_2015-09-20-23-05-10.cpp', true);  
        }
        
        if (file_exists('./154_12-Exploding_Integer_2015-11-29-22-41-37.cpp')) {
            $good_code = file_get_contents('./154_12-Exploding_Integer_2015-11-29-22-41-37.cpp', true);
        } else {
          $good_code = file_get_contents('./154_08-Fibonacci_2015-09-20-23-06-03.cpp', true);
        }

        $mark_array_bad = array(0);
        $mark_array_good = array(3);
    } else if ( $error_type == "undeclared" ) {
        $bad_code = file_get_contents('./154_08-Fibonacci_2015-09-20-23-05-10.cpp', true);
        $good_code = file_get_contents('./154_08-Fibonacci_2015-09-20-23-06-03.cpp', true);
        $mark_array_bad = array(4);
        $mark_array_good = array(4);
    }



    $all_feedback .= format_cpp($bad_code, $mark_array_bad);  //formatting of bad code

    //title of good code
    $all_feedback .= "</div><div id=\"goodCode\"style = \"display: myDiv.display;\"><p><b>Correct Code:</b></p>";

    $all_feedback .= format_cpp($good_code, $mark_array_good);    //formatting of good code

    $all_feedback .= "</div>";*/ //end of removing code snippets


    //scripts and Was this Helpful section
    //finishes button, hidden section
    //creates function that allows button to show/hide section
    //removal of "Was this Helpful section"
    /*$all_feedback .= "<div class=\"panel panel-success\" style=\"width:250\"><div class=\"panel-heading\">
        <font color=\"black\"><p><b>Was this helpful?<font style = \"color: rgba(0,0,0,0)\">__</font></b><input id = \"yesButton\" type = \"button\" value = \"Yes\" onclick = \"showLess()\"><input id = \"noButton\" type = \"button\" value = \"No\" onclick = \"showLess()\"><div id=\"Thanks\"style=\"display: none;\">Thank you!</div><p></font></div></div>";*/
    
    $all_feedback .= "</div></div></div></div>";

    //$all_feedback .= "</div>";    //end of button div

    $all_feedback .= "<script type=\"text/javascript\">\$('.collapse').on('shown.bs.collapse', function(){\$(this).parent().find(\".glyphicon-triangle-right\").removeClass(\"glyphicon-triangle-right\").addClass(\"glyphicon-triangle-bottom\");}).on('hidden.bs.collapse', function(){\$(this).parent().find(\".glyphicon-triangle-bottom\").removeClass(\"glyphicon-triangle-bottom\").addClass(\"glyphicon-triangle-right\");});</script>";

    $all_feedback .= "<script> function myFunction() { var x = document.getElementById('myDIV'); if (x.style.display === 'none') { x.style.display = 'block'; } else { x.style.display = 'none'; }}";

    $all_feedback .= "var yesButton = document.getElementById(\"yesButton\");var noButton = document.getElementById(\"noButton\");var thanksText = document.getElementById(\"Thanks\");function showLess() {yesButton.style.display=\"none\";noButton.style.display=\"none\";thanksText.style.display=\"block\";}function addcss(){ var head = document.getElementsByTagName('head')[0]; var s = document.createElement('link'); s.setAttribute('type', 'text/css');s.setAttribute('rel', 'stylesheet');s.setAttribute('href', './hidden_feedback_formatting.css');head.appendChild(s);}addcss();</script>";

    $all_feedback .= "<script type=\"text/javascript\">\$('[data-toggle=\"popover\"]').on({mouseenter: function () {\$(this).popover('show');},mouseleave: function () {\$(this).popover('hide');}});</script>";

    return $all_feedback;
}

function get_error_type() {
    global $error_type;
    return $error_type;
}

$cflags = "-Wall -pedantic-errors -Werror -Wextra -Wshadow -Wfatal-errors -Wno-unused-variable";
if (!$MACINTOSH) $cflags .= " -Wno-unused-but-set-variable";

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
    
    execute(20,"g++ $cflags test.cpp -c","",$stdout,$stderr);
    if (!empty($stderr))
        return true;

    _append_log($description);
    show_file("test.cpp",$code);
    return false;
}


function execution_test($files,&$output) {
    global $cflags; 
    
    if (!compile("g++ $cflags $files -o test_program"))
        return false;

    $output = "";
    execute(20,execution_name("test_program"),'',$output,$stderr);
    
    //TODO -- need a way to show what didn't work (especially when it seg faults)
    
    if (!empty($stderr)) {
        show_file("errors",$stderr);
        return false;
    }
    _count_case();
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
        execute(20,execution_name("assert_test $i"),'',$output,$stderr);
        if (!empty($stderr)) {
            show_file("error",$stderr);
            show_file("test.cpp",$subtests[$i-1]);
            return false;
        }
        _count_case();
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
        _count_case();
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
    _count_case();
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

// parse files using functionality in 'source_parser.php'
$sources = array_map(function($f) {return new SourceCode(@file_get_contents($f));},$files);

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


$log = "You passed $cases of the test cases.<br /><br />\n\n". $log;
    
$result = (object)array();
//These results are returned to submit.php at the root of Athene
$result->score = round($score,5);
$result->log = substr($log,0,8000);
$result->parts = []; // TODO this is wrong, convert to unit_tests.php to avoid duplication of code
echo json_encode($result);
