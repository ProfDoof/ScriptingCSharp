<?php

/** @test
    @score  0.01 */
function complies() { 
    $header = file_get_contents("Tuple.h");
    return source_does_not_contain_regex($header,"/using\\s+namespace\\s+/","using namespace in header"); 
}

/** @test
    @prereq complies
    @score  0.02 */
function compiles() { return compile_test("",<<<EOF
#include "Tuple.h"
EOF
); }

/** @test
    @prereq compiles
    @score  0.02 */
function declaration() { return compile_test("",<<<EOF
#include "Tuple.h"
Tuple<int> * t;
Tuple<double> * t2;
Tuple<long> * t3;
TupleData<int> * td;
TupleData<double> * td2;
TupleData<long> * td3;
EOF
); }

/** @test
    @prereq declaration
    @score  0.10 */
function public_interface() { return compile_tests("","#include <iostream>\n#include \"Tuple.h\"\nint main()\n{\n","\n}\n", 
array(
<<<EOF
    TupleData<int> td1(5);
    TupleData<int> td2(td1);
    int list[5] = {1,2,3,4,5};
    TupleData<int> td3(list,5);
    
	int item1 = td3[3];
    td3[3] = 5;
    
    int s = td3.size();
    int c = td2.useCount();
EOF
,
<<<EOF
	Tuple<int> t1(5);
    Tuple<int> t2(t1);
    int list[5] = {1,2,3,4,5};
    Tuple<int> t3(list,5);
    
    t2 = t3;
    int it1 = t2[2];
    const int it2 = t2[2];
    bool eq = t2 == t3;
    bool neq = t2 == t1;
    
    int s = t2.size();
    int m = t2.magnitude();
EOF
));}

/** @test
    @prereq public_interface
    @score  0.05 */
function const_correct() { return compile_tests("","#include <iostream>\n#include \"Tuple.h\"\nint main()\n{\n","\n}\n", 
array(
<<<EOF
    const TupleData<int> td1(5);
    const TupleData<int> td2(td1);
    int list[5] = {1,2,3,4,5};
    const TupleData<int> td3(list,5);
    
	int item1 = td3[3];
    const int item2 = td3[3];
    
    int s = td3.size();
    int c = td2.useCount();
EOF
,
<<<EOF
	const Tuple<int> t1(5);
    const Tuple<int> t2(t1);
    int list[5] = {1,2,3,4,5};
    const Tuple<int> t3(list,5);
    
    int it1 = t2[2];
    const int it2 = t2[2];
    bool eq = (t2 == t3);
    bool neq = (t2 == t1);
    
    int s = t2.size();
    int m = t2.magnitude();
EOF
));}

/** @test
    @prereq public_interface
    @score  0.10 */
function sample() { 
    return execution_test("sample.cpp new.cpp",$output)
        && output_contains_lines($output,<<<EOF
EOF
);}

/** @test
	@prereq public_interface
	@score 0.10 */
function addition() { return assertion_tests3('-DDEBUG',array('"Tuple.h"'), <<<EOF
	int data[] = {2,3,5,7,11,13,17,19,23};
    Tuple<int> a(data,9);
    assert(a.size()==9);
    
    Tuple<int> b(data,3);
    assert(b.size()==3);
   
    Tuple<int> c = a + b;
    assert(a.size()==9);
    assert(b.size()==3);
    assert(c.size()==9);
    for (int i=0; i<9; i++) {
        assert(a[i] == data[i]);
        assert(b[i] == (i<3 ? data[i] : 0));
        assert(c[i] == (i<3 ? data[i]*2 : data[i]));
    }
    assert(a.implementation().useCount()==1);
    assert(b.implementation().useCount()==1);
    assert(c.implementation().useCount()==1);
	
    Tuple<int> d(a);
	d += b; 
	assert(d == c);
	assert(a.implementation().useCount()==1);
EOF
) && assertion_tests3('-DDEBUG',array('"Tuple.h"'), <<<EOF
	double data[] = {2.5,3.3,5.2,7.4,11,13,17,19,23};
    Tuple<double> a(data,9);
    assert(a.size()==9);
    
    Tuple<double> b(data,3);
    assert(b.size()==3);
   
    Tuple<double> c = a + b;
    assert(a.size()==9);
    assert(b.size()==3);
    assert(c.size()==9);
    for (int i=0; i<9; i++) {
        assert(a[i] == data[i]);
        assert(b[i] == (i<3 ? data[i] : 0));
        assert(c[i] == (i<3 ? data[i]*2 : data[i]));
    }
    assert(a.implementation().useCount()==1);
    assert(b.implementation().useCount()==1);
    assert(c.implementation().useCount()==1);
    
	Tuple<double> d(a);
	d += b; 
	assert(d == c);
	assert(a.implementation().useCount()==1);
EOF
); }

/** @test
	@prereq public_interface
	@score 0.15 */
function dot_multiplication() { return assertion_tests3('',array('"Tuple.h"'), <<<EOF
	//dot mult	
	int data[] = {2,3,5,7,11,13,17,19,23};
    Tuple<int> a(data,9);
    Tuple<int> b(data+1,5);
    assert(a*b == 2*3 + 3*5 + 5*7 + 7*11 + 11*13);
    assert(b*b == 3*3 + 5*5 + 7*7 + 11*11 + 13*13);
    assert(a*a == 2*2 + 3*3 + 5*5 + 7*7 + 11*11 + 13*13 + 17*17 + 19*19 + 23*23);
    assert(a.magnitude() == (int)sqrt(a*a));  
EOF
) && assertion_tests3('',array('"Tuple.h"', '<math.h>'), <<<EOF
	double data[] = {2,3.4,5,7.75,11,13.6,17,19,23};
    Tuple<double> a(data,9);
    Tuple<double> b(data+1,5);
    assert(fabs((a*b) - (2*3.4 + 3.4*5 + 5*7.75 + 7.75*11 + 11*13.6)) < .00001);
    assert(fabs((b*b) - (3.4*3.4 + 5*5 + 7.75*7.75 + 11*11 + 13.6*13.6)) < .00001);
    assert(fabs((a*a) - (2*2 + 3.4*3.4 + 5*5 + 7.75*7.75 + 11*11 + 13.6*13.6 + 17*17 + 19*19 + 23*23)) < .00001);
    assert(fabs(a.magnitude() - sqrt(a*a)) < .00001);
EOF
); }

/** @test
	@prereq public_interface
	@score 0.10 */
function scalar_multiplication() { return assertion_tests3('',array('"Tuple.h"'), <<<EOF
    int data[] = {2,3,5,7,11,13,17,19,23};
    Tuple<int> a(data,9);
    Tuple<int> b = a*3;
    for (int i=0; i<9; i++) {
        assert(b[i] == data[i]*3);
    }
        
    Tuple<int> c(data,9);
    c *= 3;
    assert(c == b);
EOF
) && assertion_tests3('',array('"Tuple.h"', '<math.h>'), <<<EOF
    double data[] = {2.5,3.2,5.4,7.5,11,13,17,19,23};
    Tuple<double> a(data,9);
    Tuple<double> b = a*(double)3;
    for (int i=0; i<9; i++) {
        assert(fabs(b[i] - (data[i]*3)) < .00001);
    }
    
    Tuple<double> c(data,9);
    c *= (double)3;
    assert(c == b);
EOF
);}

/** @test
	@prereq public_interface
	@score 0.05 */
function bool_operators() { return assertion_tests3('',array('"Tuple.h"'), <<<EOF
	Tuple<int> a(5);
    Tuple<int> b(a);
    Tuple<int> c(b);
    
    assert(a==b);
    assert(a==c);
    assert(c==b);
    assert(!(a!=b));
    assert(!(a!=c));
    assert(!(b!=c));
    a[3] = 7;
    
    Tuple<int> d(a);
    assert(a!=b);
    assert(a!=c);
    assert(!(a==b));
    assert(!(a==c));
    assert(d==a);
    assert(c==b);
    assert(d!=b);
    assert(d!=c);
    
    a[5] = 9;
    for (int i=0; i<5; i++) {
        assert(b[i] == 0);
        assert(c[i] == 0);
        assert(a[i] == (i==3 ? 7 : 0));
    }
EOF
);}

/** @test
	@prereq sample
	@score 0.30 */
function copy_on_write() { return assertion_tests3('-DDEBUG',array('"Tuple.h"'), <<<EOF
	Tuple<int> *a[1000];
    a[0] = new Tuple<int>(3);
    for (int i=1; i<1000; i++)
        a[i] = new Tuple<int>(*a[i-1]);
    for (int i=1; i<1000; i++) {
        assert(&a[i]->implementation() == &a[i-1]->implementation());
    }
    
    for (int i=0; i<1000; i++) {
        assert(a[i]->implementation().useCount() == 1000-i);
        delete a[i];
    }

    a[0] = new Tuple<int>(4);
    for (int i=1; i<1000; i++)
        a[i] = new Tuple<int>(*a[i-1]);
    for (int i=1; i<1000; i++) {
        assert(&a[i]->implementation() == &a[i-1]->implementation());
    }

    Tuple<int> b(4);
    for (int i=0; i<1000; i++) {
        *a[i] = b;
        assert(b.implementation().useCount() == i+2);
    }
    
    for (int i=0; i<1000; i++) {
        assert(a[i]->implementation().useCount() == 1000-i+1);
        delete a[i];
    }
    assert(b.implementation().useCount() == 1);
EOF
);}


include 'auto_score.php'; 
