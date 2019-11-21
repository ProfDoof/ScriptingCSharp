<?php

$cflags = "-Wall -pedantic-errors -Wextra -Wshadow -I ./";

// no prereq
function test_Compile() {
    //message("running Compile\n");
    return  compile("g++ $cflags -c Set.h")
    &&      score(null,'Compile');
}

// prereq: Compile
function test_Given() {
    //message("running Sample\n");
    compile("g++ $cflags sample.cpp new.cpp -o sample") &&
    run("sample","",$output) &&
    score(0.70,'Given');
}

// prereq: Compile
function test_All() {
    //message("running Everything\n");
    compile("g++ $cflags test.cpp new.cpp -o test") &&
    run("test","",$output) &&
    score(1,'All');
}

include 'auto_score.php';

//	"Given"    
//	"Format"   
//	"Standard" 
//	"Random"   
//	"Edge"     
//	"All"      
