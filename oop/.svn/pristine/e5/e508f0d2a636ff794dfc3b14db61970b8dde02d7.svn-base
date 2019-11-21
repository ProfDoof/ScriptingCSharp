<?php

/** @test
    @score 0.01 */
function set_includes() {
    global $files;
    $oldInclude = "INCLUDE_SHAPES";
    $newInclude = "#include \"$files[0]\"";

    // Set Include in sample.cpp
    $fileContents = file_get_contents("sample.cpp");
    $fileContents = str_replace("$oldInclude", "$newInclude", $fileContents);
    file_put_contents("sample.cpp", $fileContents);

    // Set Include in polymorphism.cpp
    $fileContents = file_get_contents("polymorphism.cpp");
    $fileContents = str_replace("$oldInclude", "$newInclude", $fileContents);
    file_put_contents("polymorphism.cpp", $fileContents);
    return true;
}

/** @test
    @score set_includes
    @score  0.01 */
function complies() {
    global $files;
    $header = file_get_contents("$files[0]");
    return source_does_not_contain_regex($header,"/using\\s+namespace\\s+/","using namespace in header");
}

/** @test
    @prereq complies
    @score  0.01 */
function compiles() {
    global $files;
    return compile_test("$files[0] $files[1]","");
}

/** @test
    @prereq compiles
    @score  0.02 */
function declaration() {
    global $files;
    return compile_test("$files[1]",<<<EOF
#include "$files[0]"
Box *b;
Circle *c;
Triangle *t;
Polygon *p;
Shape *s;
Color x;
EOF
); }

/** @test
    @prereq declaration
    @score  0.10 */
function public_interface() {
    global $files;
    return compile_tests("$files[1]","#include <iostream>\n#include \"$files[0]\"\nShape *s;\nint main()\n{\n","\n}\n",
array(
<<<EOF
    s->color(BLUE);
    Color c = s->color();
    double a = s->area();
    double b = s->perimeter();
    s->move(2.5,3.5);
    s->render(std::cout);
EOF
,
<<<EOF
    Box b(WHITE,1.0,1.0,1.0,1.0);
    s = &b;
    double x;
    x = b.left();
    x = b.top();
    x = b.right();
    x = b.bottom();
    b.left(2.5);
    b.top(2.5);
    b.right(2.5);
    b.bottom(2.5);
EOF
,<<<EOF
    Circle b(WHITE,1.0,1.0,1.0);
    s = &b;
    double x;
    x = b.centerX();
    x = b.centerY();
    x = b.radius();
    b.centerX(2.5);
    b.centerY(2.5);
    b.radius(2.5);
EOF
,<<<EOF
    Triangle b(WHITE,1.0,1.0,1.0,1.0,1.0,1.0);
    s = &b;
    double x;
    x = b.cornerX1();
    x = b.cornerX2();
    x = b.cornerX3();
    x = b.cornerY1();
    x = b.cornerY2();
    x = b.cornerY3();
    b.cornerX1(1.5);
    b.cornerX2(1.5);
    b.cornerX3(1.5);
    b.cornerY1(1.5);
    b.cornerY2(1.5);
    b.cornerY3(1.5);
EOF
,<<<EOF
    double p[] = {1,1,1,1,1,1};
    Polygon b(WHITE,p,3);
    s = &b;
    int n = b.points();
    double x;
    x = b.vertexX(0);
    b.vertexX(0,1.5);
EOF
));}


/** @test
    @prereq public_interface
    @score  0.05 */
function const_correct() {
    global $files;
    return compile_tests("$files[1]","#include <iostream>\n#include \"$files[0]\"\nconst Shape *s;\nint main()\n{\n","\n}\n",
array(<<<EOF
    Color c = s->color();
    double a = s->area();
    double b = s->perimeter();
    s->render(std::cout);
EOF
,<<<EOF
    const Box b(WHITE,1.0,1.0,1.0,1.0);
    s = &b;
    double x;
    x = b.left();
    x = b.top();
    x = b.right();
    x = b.bottom();
EOF
,<<<EOF
    const Circle c(WHITE,1.0,1.0,1.0);
    s = &c;
    double x;
    x = c.centerX();
    x = c.centerY();
    x = c.radius();
EOF
,<<<EOF
    const Triangle t(WHITE,1.0,1.0,1.0,1.0,1.0,1.0);
    s = &t;
    double x;
    x = t.cornerX1();
    x = t.cornerX2();
    x = t.cornerX3();
    x = t.cornerY1();
    x = t.cornerY2();
    x = t.cornerY3();
EOF
,<<<EOF
    double p[] = {1,1,1,1,1,1};
    const Polygon b(WHITE,p,3);
    s = &b;
    int n = b.points();
    double x;
    x = b.vertexX(0);
EOF
));}

/** @test
    @prereq public_interface
    @score  0.20 */
function sample() {
    global $files;
    return execution_test("new.cpp sample.cpp $files[1]",$output) // new.cpp must be first to get destruction order right (otherwise false errors are reported)
        && output_contains_lines($output,<<<EOF
distance: 72.8225 area: 47.7743
drawing: Box(BLUE,0,1,1,0)
Box(CYAN,2,9,4,3)
Circle(WHITE,5,5,3)
Triangle(BLACK,1,1,5,1,3,3)
Polygon(GREEN,5,1,1,7,2,3,5,6,8,4,3)
Box(BLUE,10,11,11,10)
Box(CYAN,12,19,14,13)
Circle(WHITE,15,15,3)
Triangle(BLACK,11,11,15,11,13,13)
Polygon(GREEN,5,11,11,17,12,13,15,16,18,14,13)
EOF
);}

/** @test
	  @prereq sample
	  @score 0.10 */ //placeholder score
function bounds() {
    global $files;
    return assertion_tests3("new.cpp $files[1]",array("\"$files[0]\""), <<<EOF
	Box b(BLUE,1,2,3,4);
	assert(b.left() == 1);
	assert(b.top() == 2);
	assert(b.right() == 3);
	assert(b.bottom() == 4);

	Circle c(BLUE,1,2,3);
	assert(c.centerX() == 1);
	assert(c.centerY() == 2);
	assert(c.radius() == 3);

	Triangle t(BLUE,1,2,3,4,5,6);
	assert(t.cornerX1() == 1);
	assert(t.cornerY1() == 2);
	assert(t.cornerX2() == 3);
	assert(t.cornerY2() == 4);
	assert(t.cornerX3() == 5);
	assert(t.cornerY3() == 6);

	b.left(4);
	b.top(3);
	b.right(2);
	b.bottom(1);
	assert(b.left() == 4);
	assert(b.top() == 3);
	assert(b.right() == 2);
	assert(b.bottom() == 1);

	c.centerX(3);
	c.centerY(1);
	c.radius(2);
	assert(c.centerX() == 3);
	assert(c.centerY() == 1);
	assert(c.radius() == 2);

	t.cornerX1(6);
	t.cornerY1(5);
	t.cornerX2(4);
	t.cornerY2(3);
	t.cornerX3(2);
	t.cornerY3(1);
	assert(t.cornerX1() == 6);
	assert(t.cornerY1() == 5);
	assert(t.cornerX2() == 4);
	assert(t.cornerY2() == 3);
	assert(t.cornerX3() == 2);
	assert(t.cornerY3() == 1);

	static double pts[] = {1,2,3,4,5,6,7,8,9,10};
	Polygon p(BLUE,pts,5);

	assert(p.vertexX(0) == 1);
	assert(p.vertexY(0) == 2);
	assert(p.vertexX(1) == 3);
	assert(p.vertexY(1) == 4);
	assert(p.vertexX(2) == 5);
	assert(p.vertexY(2) == 6);
	assert(p.vertexX(3) == 7);
	assert(p.vertexY(3) == 8);
	assert(p.vertexX(4) == 9);
	assert(p.vertexY(4) == 10);

	for(int i = 0; i < 5; i++)
	{
		p.vertexX(i,10-(i*2));
		p.vertexY(i,10-(i*2+1));
	}

	assert(p.vertexX(0) == 10);
	assert(p.vertexY(0) == 9);
	assert(p.vertexX(1) == 8);
	assert(p.vertexY(1) == 7);
	assert(p.vertexX(2) == 6);
	assert(p.vertexY(2) == 5);
	assert(p.vertexX(3) == 4);
	assert(p.vertexY(3) == 3);
	assert(p.vertexX(4) == 2);
	assert(p.vertexY(4) == 1);
EOF
); }

/** @test
	  @prereq sample
	  @score 0.1 */ //placeholder score
function perimeter() {
    global $files;
    return assertion_tests3("new.cpp $files[1]",array("\"$files[0]\"",'<cmath>'), <<<EOF
	Box b(BLUE,1,4,3,2);
	assert(fabs(b.perimeter()-8)<0.0000001);

	Circle c(BLUE,1,2,3);
	assert(fabs(c.perimeter()-M_PI*6)<0.0000001);

	Triangle t(BLUE,1,2,3,4,3,2);
	assert(fabs(t.perimeter()-sqrt(8)-4)<0.0000001);

	double pts[] = {1,2,3,4,5,6,7,8,9,10};
	Polygon p(BLUE,pts,5);
    assert(fabs(p.perimeter()-sqrt(8)*4-sqrt(128))<0.0000001);

    Box bb(BLUE,0,1,1,0);
    assert(fabs(bb.perimeter()-4) < 0.0000001);

	Circle cc(BLACK,0,0,1);
	assert(fabs(cc.perimeter()-2*M_PI) < 0.0000001);

	Triangle tt(RED,0,0,1,0,0,1);
	assert(fabs(tt.perimeter()-(2+sqrt(2))) < 0.0000001);

	double pts2[] = {0,0,1,0,0,1};
    Polygon pp(YELLOW,pts2,3);
    assert(fabs(pp.perimeter()-(2+sqrt(2))) < 0.0000001);
EOF
); }

/** @test
	  @prereq sample
	  @score 0.10 */ //placeholder score
function area() {
    global $files;
    return assertion_tests3("new.cpp $files[1]", array("\"$files[0]\"",'<cmath>'),<<<EOF
	Box b(BLUE,1,4,3,2);
	assert(fabs(b.area()-4)<0.0000001);

	Circle c(BLUE,1,2,3);
	assert(fabs(c.area()-M_PI*3*3)<0.0000001);

	Triangle t(BLUE,1,2,3,4,3,2);
	assert(fabs(t.area()-2)<0.0000001);

	double pts[] = {1,2,3,4,5,6,7,8,9,10};
	Polygon p(BLUE,pts,5);
    assert(fabs(p.area()-0)<0.0000001);

    Box bb(BLUE,-1,1,1,-1);
	assert(fabs(bb.area()-4) < 0.0000001);

    Circle cc(BLACK,5,5,2);
	assert(fabs(cc.area()-4*M_PI) < 0.0000001);

    Triangle tt(RED,0,0,10,0,0,1);
	assert(fabs(tt.area()-5) < 0.0000001);

	double pts2[] = {0,0,1,0,0,1,1,1,0,2};
    Polygon pp(YELLOW,pts2,5);
	assert(fabs(pp.area()-1) < 0.0000001);
EOF
); }


/** @test
    @prereq public_interface
    @score  0.05 */
function non_value_semantics() {
    global $files;
    return anti_compile_test("Shapes should prevent value-semantic operations such as the following:",<<<EOF
#include "$files[0]"
int main()
{
    Box q(BLUE,1,2,3,4);
    Box p(q);
}
EOF
) && anti_compile_test("Shapes should prevent value-semantic operations such as the following:",<<<EOF
#include "$files[0]"
int main()
{
    Box q(BLUE,1,2,3,4);
    Box p(BLUE,1,2,3,4);
    q = p;
}
EOF
); }

/** @test
	@prereq sample
	@score 0.10 */
function rendering() {
    global $files;
    return assertion_tests3("new.cpp $files[1]",array("\"$files[0]\"",'<sstream>'), <<<EOF
	std::string name[]={"BLACK","RED","GREEN","YELLOW","BLUE","MAGENTA","CYAN","WHITE"};
    for (int i=0; i<8; i++) {
        Box b((Color)i,-1,2,3,-4);
        std::stringstream ss;
        b.render(ss);
        assert(ss.str() == "Box("+name[i]+",-1,2,3,-4)");
        b.right(7);
        b.top(-5);
        b.color((Color)((i+1)%8));
        ss.str("");
        b.render(ss);
        assert(ss.str() == "Box("+name[(i+1)%8]+",-1,-5,7,-4)");
    }
EOF
) && assertion_tests3("new.cpp $files[1]",array("\"$files[0]\"",'<sstream>'), <<<EOF
	std::string name[]={"BLACK","RED","GREEN","YELLOW","BLUE","MAGENTA","CYAN","WHITE"};
    for (int i=0; i<8; i++) {
        Circle c((Color)i,5,9,2);
        std::stringstream ss;
        c.render(ss);
        assert(ss.str() == "Circle("+name[i]+",5,9,2)");
        c.radius(6);
        c.color((Color)((i+1)%8));
        ss.str("");
        c.render(ss);
        assert(ss.str() == "Circle("+name[(i+1)%8]+",5,9,6)");
    }
EOF
) && assertion_tests3("new.cpp $files[1]",array("\"$files[0]\"",'<sstream>'), <<<EOF
	std::string name[]={"BLACK","RED","GREEN","YELLOW","BLUE","MAGENTA","CYAN","WHITE"};
    for (int i=0; i<8; i++) {
        Triangle t((Color)i,0,0,10,0,0,1);
        std::stringstream ss;
        t.render(ss);
        assert(ss.str() == "Triangle("+name[i]+",0,0,10,0,0,1)");
        t.cornerY2(9);
        t.color((Color)((i+1)%8));
        ss.str("");
        t.render(ss);
        assert(ss.str() == "Triangle("+name[(i+1)%8]+",0,0,10,9,0,1)");
    }
EOF
) && assertion_tests3("new.cpp $files[1]",array("\"$files[0]\"",'<sstream>'), <<<EOF
	std::string name[]={"BLACK","RED","GREEN","YELLOW","BLUE","MAGENTA","CYAN","WHITE"};
    for (int i=0; i<8; i++) {
        double pts[] = {0,0,1,0,0,1,1,1,0,2};
        Polygon p((Color)i,pts,5);
        std::stringstream ss;
        p.render(ss);
        assert(ss.str() == "Polygon("+name[i]+",5,0,0,1,0,0,1,1,1,0,2)");
        p.vertexX(3, 7.6);
        p.color((Color)((i+1)%8));
        ss.str("");
        p.render(ss);
        assert(ss.str() == "Polygon("+name[(i+1)%8]+",5,0,0,1,0,0,1,7.6,1,0,2)");
    }
EOF
);}

/** @test
	@prereq bounds
	@score 0.10 */
function movement() {
    global $files;
    return assertion_tests3("new.cpp $files[1]",array("\"$files[0]\""), <<<EOF
	double pts[] = {0,0,1,0,0,1,1,1,0,2};
    Box b(BLUE,-1,1,1,-1);
    Circle c(BLACK,5,5,2);
    Triangle t(RED,0,0,10,0,0,1);
    Polygon p(YELLOW,pts,5);

	b.move(-2, -.5);
	c.move(-.5, 2.5);
	t.move(.5, .75);
	p.move(-1, 2.5);

	//-3,.5, -1, -1.5
	assert(b.left() == -3);
	assert(b.top() == .5);
	assert(b.right() == -1);
	assert(b.bottom() == -1.5);

	//(4.5, 7.5)
	assert(c.centerX() == 4.5);
	assert(c.centerY() == 7.5);

	//(.5,.75), (10.5, .75), (.5, 1.75)
	assert(t.cornerX1() == .5);
	assert(t.cornerY1() == .75);
	assert(t.cornerX2() == 10.5);
	assert(t.cornerY2() == .75);
	assert(t.cornerX3() == .5);
	assert(t.cornerY3() == 1.75);

	// (-1, 2.5), (0, 2.5), (-1, 3.5), (0, 3.5), (-1, 4.5)
	assert(p.vertexX(0) == -1);
	assert(p.vertexY(0) == 2.5);
	assert(p.vertexX(1) == 0);
	assert(p.vertexY(1) == 2.5);
	assert(p.vertexX(2) == -1);
	assert(p.vertexY(2) == 3.5);
	assert(p.vertexX(3) == 0);
	assert(p.vertexY(3) == 3.5);
	assert(p.vertexX(4) == -1);
	assert(p.vertexY(4) == 4.5);
EOF
); }


/** @test
	@prereq movement
	@score 0.05 */
function polymorphism() {
    global $files;
    return execution_test("new.cpp polymorphism.cpp $files[1]",$output)
        && output_contains_lines($output,<<<EOF
Box(BLUE,-1,2,2,-1)
Circle(BLUE,2,5,3)
Triangle(YELLOW,0,0,0,3,3,2)
Polygon(WHITE,4,0,0,1,4,0,-1,-3,-1)
Box(BLUE,0,1,3,-2)
Circle(BLUE,3,4,3)
Triangle(YELLOW,1,-1,1,2,4,1)
Polygon(WHITE,4,1,-1,2,3,1,-2,-2,-2)
EOF
);}


include 'oop_scoring.php';
