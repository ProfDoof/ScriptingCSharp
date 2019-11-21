<?php

/** @test
    @score  0.00 */
function setup() {
    if (!compile("g++ -std=gnu++0x -O0 -ftest-coverage -fprofile-arcs book-index.cpp -o indexer"))
        return false;
        
    global $files;
    $zip = new ZipArchive;
    $res = $zip->open($files[0]);
    if (!$res === TRUE) {
        message("archive ($files[0]) unextractable");
        return false;
    }
    for ($i=1; true; $i++) {
        //message("Extracting: {$i}_book.txt {$i}_ignore.txt {$i}_synonyms.txt {$i}_results.txt\n");
        unset($book,$ign,$syn,$res);
        $book = $zip->getFromName("{$i}_book.txt");
        $ign  = $zip->getFromName("{$i}_ignore.txt");
        $syn  = $zip->getFromName("{$i}_synonyms.txt");
        $res  = $zip->getFromName("{$i}_results.txt");
        if ($book===FALSE || $ign===FALSE || $syn===FALSE || $res===FALSE) break;
        
        message("Running test case $i.\n");

        file_put_contents("./book.txt",$book);
        file_put_contents("./ignore.txt",$ign);
        file_put_contents("./synonyms.txt",$syn);
        file_put_contents("./results.txt",$res);
        if (!run("indexer book.txt ignore.txt synonyms.txt","",$output))
            return false;
        file_put_contents("./output.txt",$output);
        
        $output = preg_split("/[\r\n]+/",$output);
        $res = preg_split("/[\r\n]+/",$res);
        while (count($output)!=0 && count($res)!=0 && $output[0]==$res[0]) {
//echo count($output)." ".count($res)."\n";
            array_shift($output);
            array_shift($res);
        }
        if (count($output)!=0 && count($res)!=0) {
            show_output("computed output for given input",$output[0]);
            show_output("{$i}_results.txt",$res[0]);
            return false;
        }
        if (count($output)!=0) {
            show_output("extra computed output for given input",$output[0]);
            return false;
        }
        if (count($res)!=0) {
            show_output("{$i}_results.txt contains extra line(s)",$res[0]);
            return false;
        }
    }
    message(($i-1)." test cases used.\n");
    $zip->close();
    
    message("Running indexer with no parameters.\n");
    if (!run("indexer","",$output)) return false;
    message("Checking coverage.\n");
    if (!run_non_local("gcov -n book-index.cpp","",$output)) return false;
    message("Getting coverage.\n");
    if (!preg_match("|File 'book-index.cpp'\\s+Lines executed:(\\d+).\\d+% of |m",$output,$m)) return false;
        
    global $score;
    $coverage = $m[1]/100;
    global $WINDOWS,$MACINTOSH;
    if (!$WINDOWS && !$MACINTOSH)
        $coverage += 0.03;
    if ($coverage >= .99) 
        $coverage = 1.0;
    $score = round(pow($coverage,3),2);
    if ($score < 1)
        message("You got to $m[1]% code coverage; you can do better.");
    return $score>0;
}

//File 'book-index.cpp'
//Lines executed:4.55% of 110

include 'auto_score.php';
