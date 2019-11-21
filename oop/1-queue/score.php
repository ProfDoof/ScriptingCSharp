<?php

/** @test
    @score  0.05 */
function complies() {
    $header = file_get_contents("IntegerQueue.h");
    return source_does_not_contain_regex($header,"/using\\s+namespace\\s+/","using namespace in header"); 
}

/** @test
    @score  0.05 */
function compiles() { 
    return compile_test("IntegerQueue.cpp") 
        && compile_test("IntegerQueue.h"); 
}

/** @test
    @prereq compiles
    @score  0.05 */
function declaration() { return compile_test("",<<<EOF
#include "IntegerQueue.h"
IntegerQueue q;
EOF
); }

/** @test
    @prereq declaration complies
    @score  0.05 */
function interface_correct() { return compile_test("",<<<EOF
#include "IntegerQueue.h"
int main()
{
    IntegerQueue q;
    q.push(3);
    int x = q.pop();
    bool e = q.empty();
    int n = q.size();
}
EOF
); }

/** @test
    @prereq interface_correct
    @score  0.05 
function const_correct() { return compile_test("",<<<EOF
#include "IntegerQueue.h"
int main()
{
    const IntegerQueue q;
    bool e = q.empty();
    int n = q.size();
}
EOF
); } */

/** @test
    @prereq interface_correct
    @score  0.15 */
function simple() { return assertion_tests("skeleton.cpp IntegerQueue.cpp new.cpp",<<<EOF
   IntegerQueue q;
   assert(q.empty());
   assert(q.size()==0);
   
   q.push(5);
   assert(!q.empty());
   assert(q.size()==1);
   
   int x=q.pop();
   assert(x==5);
   assert(q.empty());
   assert(q.size()==0);
   
   q.push(13);
   assert(!q.empty());
   assert(q.size()==1);
   
   q.push(9);   
   assert(!q.empty());
   assert(q.size()==2);
   
   x = q.pop();
   assert(x==13);
   assert(!q.empty());
   assert(q.size()==1);
   
   x = q.pop();
   assert(x==9);
   assert(q.empty());
   assert(q.size()==0);
EOF
); }

/** @test   
    @prereq simple
    @score  0.15 */
function deep() { return assertion_tests("-O2 skeleton.cpp IntegerQueue.cpp new.cpp",<<<EOF
    IntegerQueue q;
    for (int i=0; i<5000000; i++) {
        q.push(42);
        assert(q.size()==i+1);
    }

    for (int i=0; i<5000000; i++) {
        int x=q.pop();
        assert(x==42);
        assert(q.size()==5000000-i-1);
    }

    assert(q.empty());
EOF
); }

/** @test
    @prereq simple
    @score  0.25 */
function ordered() { return assertion_tests("-O2 skeleton.cpp IntegerQueue.cpp new.cpp",<<<EOF
    IntegerQueue q;
    srand(time(0));
    int vals[1000];
    for (int i=0; i<1000; i++) {
        int x = rand();
        q.push(x);
        vals[i] = x;
        assert(q.size()==i+1);
    }

    for (int i=0; i<1000; i++) {
        int x = q.pop();
        assert(q.size()==1000-i-1);
        assert(x==vals[i]);
    }

    assert(q.empty());
EOF
); }

/** @test
    @prereq declaration
    @score  0.25 */
function rotate() { return assertion_tests("-O2 skeleton.cpp IntegerQueue.cpp new.cpp",<<<EOF
    IntegerQueue q;
    for (int i=0; i<2000000; i++) {
        q.push(i%7);
        int x=q.pop();
        assert(x==i%7);
    }
EOF
); }


include 'auto_score.php';
