<?php

/** @test
    @score 0.01 */
function set_includes() {
  global $files;
  $oldInclude = "INCLUDE_INTEGER_QUEUE";
  $newInclude = "#include \"$files[0]\"";

  // Set Include in sample.cpp
  $fileContents = file_get_contents("sample.cpp");
  $fileContents = str_replace("$oldInclude", "$newInclude", $fileContents);
  file_put_contents("sample.cpp", $fileContents);

  // Set Include in empty.cpp
  $fileContents = file_get_contents("empty.cpp");
  $fileContents = str_replace("$oldInclude", "$newInclude", $fileContents);
  file_put_contents("empty.cpp", $fileContents);

  // Set Include in skeleton.cpp
  $fileContents = file_get_contents("skeleton.cpp");
  $fileContents = str_replace("$oldInclude", "$newInclude", $fileContents);
  file_put_contents("skeleton.cpp", $fileContents);

  return true;
}

/** @test
    @prereq set_includes
    @score  0.01 */
function complies() {
    global $files;
    $header = file_get_contents("$files[0]");
    $code = file_get_contents("$files[1]");
    return source_does_not_contain_regex($header,"/using\\s+namespace\\s+/","using namespace in header")
        && source_does_not_contain_regex($header,"/friend/", "using friend on function");
}

/** @test
    @prereq complies
    @score  0.01 */
function compiles() {
    global $files;
    return compile_test("$files[0]","");
}

/** @test
    @prereq compiles
    @score  0.02 */
function declaration() {
    global $files;
    return compile_test("",<<<EOF
#include "$files[0]"
IntegerQueue q;
EOF
); }

/** @test
    @prereq declaration
    @score  0.05 */
function public_interface() {
      global $files;
      return compile_test("",<<<EOF
#include <iostream>
#include "$files[0]"
int main()
{
    IntegerQueue q;
    q.push(3);
    int x = q.pop();
    bool e = q.empty();
    int n = q.size();
    std::cout << q;
}
EOF
); }

/** @test
    @prereq public_interface
    @score  0.05 */
function const_correct() {
      global $files;
      return compile_test("",<<<EOF
#include <iostream>
#include "$files[0]"
int main()
{
    const IntegerQueue q;
    bool e = q.empty();
    int n = q.size();

    const IntegerQueue r=q;
    IntegerQueue s;
    (s = r).size();
}
EOF
); }

/** @test
    @prereq public_interface
    @score  0.20 */
function sample() {
    global $files;
    return execution_test("sample.cpp $files[1] new.cpp",$output)
        && output_contains_lines($output,<<<EOF
q = {0,1,2,3,4,5,6,7,8,9}
r = {0,1,2,3,4,5,6,7,8,9}
s = {0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8}
t = {0,0,1,1,2,2,3,3}
{}
{0}
{0,1}
{0,1,2}
{0,1,2,3}
{0,1,2,3,4}
{0,1,2,3,4,5}
{}
{0,1,2,3,4,5,6,7}
{0,1,2,3,4,5,6,7,8}
{0,1,2,3,4,5,6,7,8,9}
{0,1,2,3,4,5,6,7,8,9}
{0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8}
{0,0,1,1,2,2,3,3}
{0}
{0,1,2}
{0,0,1,1,2,2,3,3,4,4}
{}
EOF
        );
}

/** @test
    @prereq sample
    @score  0.03 */
function deep() {
      global $files;
      return assertion_tests("-O2 skeleton.cpp $files[1] new.cpp",<<<EOF
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
    @prereq sample
    @score  0.03 */
function ordered() {
      global $files;
      return assertion_tests("-O2 skeleton.cpp $files[1] new.cpp",<<<EOF
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
    @prereq sample declaration
    @score  0.04 */
function rotate() {
      global $files;
      return assertion_tests("-O2 skeleton.cpp $files[1] new.cpp",<<<EOF
    IntegerQueue q;
    for (int i=0; i<2000000; i++) {
        q.push(i%7);
        int x=q.pop();
        assert(x==i%7);
    }
EOF
); }

/** @test
    @prereq public_interface
    @score  0.05 */
function value_semantic_interface() {
    global $files;
    return compile_test("",<<<EOF
#include "$files[0]"
int main()
{
    IntegerQueue q;
    IntegerQueue p(q);
    q = p;
}
EOF
); }

/** @test
    @prereq value_semantic_interface
    @score  0.05 */
function const_value_semantic_interface() {
    global $files;
    return compile_test("",<<<EOF
#include "$files[0]"
int main()
{
    IntegerQueue q;
    const IntegerQueue p(q);
    q = p;
}
EOF
); }


/** @test
    @prereq sample
    @score  0.20 */
function copy_semantics() {
    global $files;
    return assertion_tests("skeleton.cpp $files[1] new.cpp",<<<EOF
    IntegerQueue q;
    q.push(7);
    assert(q.size()==1);

    IntegerQueue p(q);
    assert(p.size()==1);
    assert(q.size()==1);

    int x = p.pop();
    assert(x==7);
    assert(p.empty());
    assert(q.size()==1);

    x = q.pop();
    assert(x==7);
    assert(q.size()==0);
    assert(p.size()==0);
EOF
); }

/** @test
    @prereq copy_semantics
    @score  0.20 */
function assignment_semantics() {
    global $files;
    return assertion_tests("skeleton.cpp $files[1] new.cpp",<<<EOF
    IntegerQueue q;
    q.push(7);
    q.push(5);
    assert(q.size()==2);

    IntegerQueue p;
    p = q;
    assert(p.size()==2);
    assert(q.size()==2);

    int x = p.pop();
    assert(x==7);
    assert(p.size()==1);
    assert(q.size()==2);

    x = q.pop();
    assert(x==7);
    assert(q.size()==1);
    assert(p.size()==1);

    x = q.pop();
    assert(x==5);
    assert(p.size()==1);
    assert(q.size()==0);

    x = p.pop();
    assert(x==5);
    assert(q.size()==0);
    assert(p.size()==0);
EOF
); }


/** @test
    @prereq copy_semantics
    @score  0.05 */
function self_assignment() {
    global $files;
    return assertion_tests("skeleton.cpp $files[1] new.cpp",<<<EOF
    IntegerQueue q;
    q.push(7);
    q.push(5);
    assert(q.size()==2);

    q = q;
    assert(q.size()==2);

    int x = q.pop();
    assert(x==7);
    assert(q.size()==1);

    x = q.pop();
    assert(x==5);
    assert(q.size()==0);
EOF
); }


include 'oop_scoring.php';
