<?php

/** @test
    @score  0.01 */
function complies() {
    foreach (glob("*.h") as $f) { 
        $header = file_get_contents($f);
        if (!source_does_not_contain_regex($header,"/using\\s+namespace\\s+/","using namespace in header"))
            return false;
    }
    return true; 
}

/** @test
    @prereq complies
    @score  0.02 */
function compiles() {
    global $files;
    $zip = new ZipArchive;
    $res = $zip->open($files[0]);
    if (!$res === TRUE) {
        message("archive ($files[0]) unextractable");
        return false;
    }
    for ($i=0; $i<$zip->numFiles; $i++)
        file_put_contents($zip->getNameIndex($i),$zip->getFromIndex($i));
    $zip->close();

    global $cflags;
    return compile("g++ -std=gnu++0x $cflags ".implode(' ',glob("*.cpp"))." -o indexer"); 
}

/*
function test($book,$ign,$syn,$res) {
    file_put_contents("book.txt",$book);
    file_put_contents("ignore.txt",$ign);
    file_put_contents("synonyms.txt",$syn);
    
    // technically not needed
    file_put_contents("expected.txt",$res);
    
    if (!run("indexer book.txt ignore.txt synonyms.txt","",$output))
        return false;
    
    // not needed
    file_put_contents("output.txt",$output);
    
    $output = preg_split("/[\r\n]+/",$output);
    $res = preg_split("/[\r\n]+/",$res);
    while (count($output)!=0 && count($res)!=0 && $output[0]==$res[0]) {
        array_shift($output);
        array_shift($res);
    }
    if (count($output)!=0 || count($res)!=0) {
        show_output("expected output",$res[0]);
        show_output("actual output",$output[0]);
        return false;
    }
    return true;
}
*/

function test($i) {
    if (!run("indexer {$i}_book.txt {$i}_ignore.txt {$i}_synonyms.txt","",$output))
        return false;
    
    // not needed
    $res = file_get_contents("{$i}_results.txt");
    
    $output = preg_split("/[\r\n]+/",$output);
    $res = preg_split("/[\r\n]+/",$res);
    while (count($output)!=0 && count($res)!=0 && $output[0]==$res[0]) {
        array_shift($output);
        array_shift($res);
    }
    if (count($output)!=0 || count($res)!=0) {
        show_output("expected output",$res[0]);
        show_output("actual output",$output[0]);
        return false;
    }
    return true;
}

/** @test
	@prereq compiles
	@score 0.07 */
function test1() { return test("1"); }

/** @test
	@prereq compiles
	@score 0.30 */
function test2() { return test("2"); }

/** @test
	@prereq compiles
	@score 0.30 */
function test3() { return test("3"); }

/** @test
	@prereq compiles
	@score 0.30 */
function test4() { return test("4"); }

include 'auto_score.php'; 
