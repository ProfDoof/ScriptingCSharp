<?php

/** @test
    @score  0.05 */
function complies() { 
    $header = file_get_contents("Geometry.h");
    return source_does_not_contain_regex($header,"/#include/","#include in header") 
    &&     source_does_not_contain_regex($header,"/using\\s+namespace\\s+/","using namespace in header"); 
}

/** @test
    @score  0.02 */
function compiles() { return compile_test("",<<<EOF
#include <iostream>
using namespace std;
#include "Geometry.h"
EOF
); }

/** @test
    @prereq compiles
    @score  0.03 */
function declaration() { return compile_test("",<<<EOF
#include <iostream>
using namespace std;
#include "Geometry.h"
Point<int> p;
Vector<int> v;
EOF
); }

/** @test
    @prereq declaration
    @score  0.05 */
function public_interface() { return compile_test("",<<<EOF
#include <iostream>
using namespace std;
#include "Geometry.h"
int main()
{
    Point<int> p;
    Vector<int> v;
    p.x=1; p.y=2; 
    v.dx=3; v.dy=4;
    p = v;
}
EOF
); }

/** @test
    @prereq declaration
    @score  0.05 */
function public_interface2() { return compile_test("",<<<EOF
#include <iostream>
using namespace std;
#include "Geometry.h"
int main()
{
    Point<double> p;
    Vector<double> v;
    p.x=1; p.y=2; 
    v.dx=3; v.dy=4;
    p = v;
}
EOF
); }

/** @test
    @prereq public_interface complies
    @score  0.10 */
function sample() { 
    return execution_test("sample.cpp",$output)
        && output_contains_lines($output,<<<EOF
v does not equal w
p = (3,4)
q = (1,-3)
v = [-2,-7]
w = [2,10]
EOF
); }


/** @test   
    @prereq sample
    @score  0.05 */
function point_addition() { 
    $generic = <<<EOF
        Point<T> p;
        assert(p==Point<T>(0,0));
        assert(p.x==0);
        assert(p.y==0);
        
        Point<T> q(1,2);
        assert(q.x==1);
        assert(q.y==2);
        
        Vector<T> v(3,4);
        p = q + v;
        assert(q.x==1);
        assert(q.y==2);
        assert(p.x==4);
        assert(p.y==6);
        assert(v.dx==3);
        assert(v.dy==4);
        
        Vector<T> w(6,7);
        p = q + w;
        assert(q.x==1);
        assert(q.y==2);
        assert(p.x==7);
        assert(p.y==9);
        assert(w.dx==6);
        assert(w.dy==7);
        
        q += v;
        assert(q.x==4);
        assert(q.y==6);
        assert(v.dx==3);
        assert(v.dy==4);
        
        q += w;
        assert(q.x==10);
        assert(q.y==13);
        assert(w.dx==6);
        assert(w.dy==7);    
EOF;
    return assertion_tests("skeleton.cpp","typedef int    T;\n$generic")
        && assertion_tests("skeleton.cpp","typedef double T;\n$generic") 
        && assertion_tests("skeleton.cpp","typedef long   T;\n$generic"); 
}

/** @test   
    @prereq sample
    @score  0.05 */
function point_subtraction() { 
    $generic = <<<EOF
        Point<T> p;
        assert(p==Point<T>(0,0));
        assert(p.x==0);
        assert(p.y==0);
        
        Point<T> q(10,2);
        assert(q.x==10);
        assert(q.y==2);
        
        Vector<T> v(3,4);
        p = q - v;
        
        assert(q.x==10);
        assert(q.y==2);
        assert(p.x==7);
        assert(p.y==-2);
        assert(v.dx==3);
        assert(v.dy==4);
        
        Vector<T> w(6,7);
        p = q - w;
        assert(q.x==10);
        assert(q.y==2);
        assert(p.x==4);
        assert(p.y==-5);
        assert(w.dx==6);
        assert(w.dy==7);
        
        q -= v;
        assert(q.x==7);
        assert(q.y==-2);
        assert(v.dx==3);
        assert(v.dy==4);
        
        q -= w;
        assert(q.x==1);
        assert(q.y==-9);
        assert(w.dx==6);
        assert(w.dy==7);    
EOF;
    return assertion_tests("skeleton.cpp","typedef int    T;\n$generic")
        && assertion_tests("skeleton.cpp","typedef double T;\n$generic")
        && assertion_tests("skeleton.cpp","typedef long   T;\n$generic"); 
}

/** @test   
    @prereq sample
    @score  0.05 */
function vector_addition() { 
    $generic = <<<EOF
        Vector<T> v;
        assert(v==Vector<T>(0,0));
        assert(v.dx==0);
        assert(v.dy==0);
        
        Vector<T> w(1,2);
        assert(w.dx==1);
        assert(w.dy==2);
        
        Vector<T> a(3,4);
        v = w + a;
        assert(w.dx==1);
        assert(w.dy==2);
        assert(v.dx==4);
        assert(v.dy==6);
        assert(a.dx==3);
        assert(a.dy==4);
        
        w += a;
        assert(w.dx==4);
        assert(w.dy==6);
        assert(a.dx==3);
        assert(a.dy==4);
EOF;
    return assertion_tests("skeleton.cpp","typedef int    T;\n$generic")
        && assertion_tests("skeleton.cpp","typedef double T;\n$generic") 
        && assertion_tests("skeleton.cpp","typedef long   T;\n$generic"); 
}

/** @test   
    @prereq sample
    @score  0.05 */
function vector_subtraction() { 
    $generic = <<<EOF
        Vector<T> v;
        assert(v==Vector<T>(0,0));
        assert(v.dx==0);
        assert(v.dy==0);
        
        Vector<T> w(1,2);
        assert(w.dx==1);
        assert(w.dy==2);
        
        Vector<T> a(3,5);
        v = w - a;
        assert(w.dx==1);
        assert(w.dy==2);
        assert(v.dx==-2);
        assert(v.dy==-3);
        assert(a.dx==3);
        assert(a.dy==5);
        
        w -= a;
        assert(w.dx==-2);
        assert(w.dy==-3);
        assert(a.dx==3);
        assert(a.dy==5);
EOF;
    return assertion_tests("skeleton.cpp","typedef int    T;\n$generic")
        && assertion_tests("skeleton.cpp","typedef double T;\n$generic") 
        && assertion_tests("skeleton.cpp","typedef long   T;\n$generic"); 
}






/** @ test
    @prereq sample
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

/** @ test
    @prereq sample declaration
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
