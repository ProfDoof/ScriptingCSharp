<?php

/** @test
    @score  0.05 */
function complies() {
    $header = file_get_contents("IntegerStack.h");
    return source_does_not_contain_regex($header,"/using\\s+namespace\\s+/","using namespace in header");
}

/** @test
    @prereq complies
    @score  0.05 */
function compiles() {
    return compile_test("IntegerStack.cpp")
        && compile_test("IntegerStack.h");
}

/** @test
    @prereq compiles
    @score  0.05 */
function declaration() { return compile_test("",<<<EOF
#include "IntegerStack.h"
IntegerStack s;
EOF
); }

/** @test
    @prereq declaration
    @score  0.05 */
function interface_correct() { return compile_test("",<<<EOF
#include "IntegerStack.h"
int main()
{
    IntegerStack s;
    s.push(3);
    int x = s.pop();
    bool e = s.empty();
    int n = s.size();
}

EOF
); }

/** @test
    @prereq interface_correct
    @score  0.15 */
function simple() { return assertion_tests("skeleton.cpp IntegerStack.cpp new.cpp",<<<EOF
   IntegerStack s;
   assert(s.empty());
   assert(s.size()==0);

   s.push(5);
   assert(!s.empty());
   assert(s.size()==1);

   int x=s.pop();
   assert(x==5);
   assert(s.empty());
   assert(s.size()==0);

   s.push(13);
   assert(!s.empty());
   assert(s.size()==1);

   s.push(9);
   assert(!s.empty());
   assert(s.size()==2);

   x = s.pop();
   assert(x==9);
   assert(!s.empty());
   assert(s.size()==1);

   x = s.pop();
   assert(x==13);
   assert(s.empty());
   assert(s.size()==0);
EOF
); }

/** @test
    @prereq simple
    @score  0.15 */
function deep() { return assertion_tests("-O2 skeleton.cpp IntegerStack.cpp new.cpp",<<<EOF
    IntegerStack s;
    for (int i=0; i<5000000; i++) {
        s.push(42);
        assert(s.size()==i+1);
    }

    for (int i=0; i<5000000; i++) {
        int x=s.pop();
        assert(x==42);
        assert(s.size()==5000000-i-1);
    }

    assert(s.empty());
EOF
); }

/** @test
    @prereq simple
    @score  0.25 */
function ordered() { return assertion_tests("-O2 skeleton.cpp IntegerStack.cpp new.cpp",<<<EOF
    IntegerStack s;
    srand(time(0));
    int vals[1000];
    for (int i=0; i<1000; i++) {
        int x = rand();
        s.push(x);
        vals[i] = x;
        assert(s.size()==i+1);
    }

    for (int i=0; i<1000; i++) {
        int x = s.pop();
        assert(s.size()==1000-i-1);
        assert(x==vals[1000-i-1]);
    }

    assert(s.empty());
EOF
); }

/** @test
    @prereq simple
    @score  0.25 */
function rotate() { return assertion_tests("-O2 skeleton.cpp IntegerStack.cpp new.cpp",<<<EOF
    IntegerStack s;
    for (int i=0; i<2000000; i++) {
        s.push(i%7);
        int x=s.pop();
        assert(x==i%7);
    }
EOF
); }

include 'auto_score.php';
