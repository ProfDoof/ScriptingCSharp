<?php

/** @test
    @score  0.01 */
function complies() {
    $header = file_get_contents("Shapes.h");
    return source_does_not_contain_regex($header,"/using\\s+namespace\\s+/","using namespace in header");
}

/** @test
    @prereq complies
    @score  0.02 */
function compiles() { return compile_test("Shapes.h Shapes.cpp",""); }

/** @test
    @prereq compiles
    @score  0.02 */
function declaration() { return compile_test("Shapes.cpp",<<<EOF
#include "Shapes.h"
Box *b;
Circle *c;
Triangle *t;
Polygon *p;
Shape *s;
Line *l;
RoundBox *r;
Group *g;
Color x;
EOF
); }

/** @test
    @prereq declaration
    @score  0.10 */
function public_interface() { return compile_tests("Shapes.cpp","#include <iostream>\n#include \"Shapes.h\"\nShape *s;\nint main()\n{\n","\n}\n",
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
,<<<EOF
	Line l(WHITE,1.0,1.0,1.0,1.0);
	s = &l;
	double x;
	x = l.end1X();
	x = l.end2X();
	x = l.end1Y();
	x = l.end2Y();
	l.end1X(1.5);
	l.end2X(1.5);
	l.end1Y(1.5);
	l.end2Y(1.5);
EOF
,<<<EOF
	RoundBox rb(WHITE,1.0,1.0,1.0,1.0,1.0);
	s = &rb;
	double x;
	x = rb.left();
	x = rb.top();
	x = rb.right();
	x = rb.bottom();
	x = rb.radius();
	rb.left(1.5);
	rb.top(1.5);
	rb.right(1.5);
	rb.bottom(1.5);
	rb.radius(1.5);
EOF
,<<<EOF
	Shape * shapes[2];
	shapes[0] = new Circle(BLUE,2,2,2);
	shapes[1] = new Circle(RED,3,2,1);
	Group group(WHITE,2,shapes);
	s = &group;
	Shape * sh = group.shape(1);
	delete sh;
	int c = group.shapes();
	Shape * shapes2[2];
	shapes2[0] = new Line(WHITE,1,1,1,1);
	shapes2[1] = new RoundBox(RED,1,2,3,4,1);
	group.shapes(2,shapes2);
EOF
));}


/** @test
    @prereq public_interface
    @score  0.05 */
function const_correct() { return compile_tests("Shapes.cpp","#include <iostream>\n#include \"Shapes.h\"\nconst Shape *s;\nint main()\n{\n","\n}\n",
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
,<<<EOF
	const Line l(WHITE,1,1,1,1);
	s = &l;
	double x;
	x = l.end1X();
	x = l.end2X();
	x = l.end1Y();
	x = l.end2Y();
EOF
,<<<EOF
	const RoundBox rb(WHITE,1.0,1.0,1.0,1.0,1.0);
	s = &rb;
	double x;
	x = rb.left();
	x = rb.top();
	x = rb.right();
	x = rb.bottom();
	x = rb.radius();
EOF
,<<<EOF
	Shape * shapes[2];
	shapes[0] = new Circle(BLUE,2,2,2);
	shapes[1] = new Circle(RED,3,2,1);
	const Group group(WHITE,2,shapes);
	s = &group;
	int c = group.shapes();
EOF
));}

/** @test
    @prereq public_interface
    @score  0.10 */
function sample() {
    return execution_test("new.cpp Shapes.cpp sample.cpp",$output)
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
Group area: 13.5664
Group perimeter: 16.5664
Group(BLUE,2,Box(BLUE,1,2,2,1),Circle(BLUE,3,3,2))
Group(RED,2,Box(RED,1,2,2,1),Circle(RED,3,3,2))
Count: 2
Circle(RED,3,3,2)
EOF
);}

/** @test
	  @prereq sample
	  @score 0.10 */ //placeholder score
function bounds() {	return assertion_tests3('new.cpp Shapes.cpp',array('"Shapes.h"'), <<<EOF

	//BOX
	Box b(BLUE,1,2,3,4);
	assert(b.left() == 1);
	assert(b.top() == 2);
	assert(b.right() == 3);
	assert(b.bottom() == 4);

	b.left(4);
	b.top(3);
	b.right(2);
	b.bottom(1);

	assert(b.left() == 4);
	assert(b.top() == 3);
	assert(b.right() == 2);
	assert(b.bottom() == 1);

	//CIRCLE
	Circle c(BLUE,1,2,3);
	assert(c.centerX() == 1);
	assert(c.centerY() == 2);
	assert(c.radius() == 3);

	c.centerX(3);
	c.centerY(1);
	c.radius(2);

	assert(c.centerX() == 3);
	assert(c.centerY() == 1);
	assert(c.radius() == 2);

	//TRIANGLE
	Triangle t(BLUE,1,2,3,4,5,6);
	assert(t.cornerX1() == 1);
	assert(t.cornerY1() == 2);
	assert(t.cornerX2() == 3);
	assert(t.cornerY2() == 4);
	assert(t.cornerX3() == 5);
	assert(t.cornerY3() == 6);

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

	//POLYGON
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

	//check for correct copying of pointers
	pts[1] = 5;
	assert(p.vertexY(0) == 2);

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

	//LINE
	Line l(WHITE,0,3,4,2);

	assert(l.end1X() == 0);
	assert(l.end1Y() == 3);
	assert(l.end2X() == 4);
	assert(l.end2Y() == 2);

	l.end1X(.5);
	l.end1Y(4.5);
	l.end2X(3.5);
	l.end2Y(2.5);

	assert(l.end1X() == .5);
	assert(l.end1Y() == 4.5);
	assert(l.end2X() == 3.5);
	assert(l.end2Y() == 2.5);

	//ROUNDBOX
	RoundBox rb(WHITE,0,5,5.5,0,1.5);

	assert(rb.left() == 0);
	assert(rb.top() == 5);
	assert(rb.right() == 5.5);
	assert(rb.bottom() == 0);
	assert(rb.radius() == 1.5);

	rb.left(.6);
	rb.top(4);
	rb.right(.7);
	rb.bottom(7.8);
	rb.radius(2);

	assert(rb.left() == .6);
	assert(rb.top() == 4);
	assert(rb.right() == .7);
	assert(rb.bottom() == 7.8);
	assert(rb.radius() == 2);
EOF
); }

/** @test
	  @prereq sample
	  @score 0.1 */
function perimeter() {	return assertion_tests3('new.cpp Shapes.cpp',array('"Shapes.h"','<cmath>'), <<<EOF
	double total = 0;

	Box b(BLUE,1,4,3,2);
	assert(fabs(b.perimeter()-8)<0.0000001);
	total += b.perimeter();

	Circle c(BLUE,1,2,3);
	assert(fabs(c.perimeter()-M_PI*6)<0.0000001);
	total += c.perimeter();

	Triangle t(BLUE,1,2,3,4,3,2);
	assert(fabs(t.perimeter()-sqrt(8)-4)<0.0000001);
	total += t.perimeter();

	double pts[] = {1,2,3,4,5,6,7,8,9,10};
	Polygon p(BLUE,pts,5);
    assert(fabs(p.perimeter()-sqrt(8)*4-sqrt(128))<0.0000001);
	total += p.perimeter();

    Box bb(BLUE,0,1,1,0);
    assert(fabs(bb.perimeter()-4) < 0.0000001);
	total += bb.perimeter();

	Circle cc(BLACK,0,0,1);
	assert(fabs(cc.perimeter()-2*M_PI) < 0.0000001);
	total += cc.perimeter();

	Triangle tt(RED,0,0,1,0,0,1);
	assert(fabs(tt.perimeter()-(2+sqrt(2))) < 0.0000001);
	total += tt.perimeter();

	double pts2[] = {0,0,1,0,0,1};
    Polygon pp(YELLOW,pts2,3);
    assert(fabs(pp.perimeter()-(2+sqrt(2))) < 0.0000001);
	total += pp.perimeter();

	Line l(YELLOW,0,1,1,0);
	assert(fabs(l.perimeter()-sqrt(2)) < 0.0000001);

	RoundBox rb(WHITE,0,3,4,0,1);
	assert(fabs(rb.perimeter()-(6.0+2.0*M_PI)) < 0.0000001);
	total += rb.perimeter();

	Shape * shapes[9];
	shapes[0] = new Box(BLUE,1,4,3,2);
	shapes[1] = new Circle(BLUE,1,2,3);
	shapes[2] = new Triangle(BLUE,1,2,3,4,3,2);
	shapes[3] = new Polygon(BLUE,pts,5);
	shapes[4] = new Box(BLUE,0,1,1,0);
	shapes[5] = new Circle(BLACK,0,0,1);
	shapes[6] = new Triangle(RED,0,0,1,0,0,1);
	shapes[7] = new Polygon(YELLOW,pts2,3);
	shapes[8] = new RoundBox(WHITE,0,3,4,0,1);
	Group g(RED, 9, shapes);
	assert(fabs(g.perimeter()-total) < 0.0000001);
EOF
); }

/** @test
	  @prereq sample
	  @score 0.05 */ //placeholder score
function area() {	return assertion_tests3('new.cpp Shapes.cpp', array('"Shapes.h"','<cmath>'),<<<EOF
	double total = 0;

	Box b(BLUE,1,4,3,2);
	assert(fabs(b.area()-4)<0.0000001);
	total += b.area();

	Circle c(BLUE,1,2,3);
	assert(fabs(c.area()-M_PI*3*3)<0.0000001);
	total += c.area();

	Triangle t(BLUE,1,2,3,4,3,2);
	assert(fabs(t.area()-2)<0.0000001);
	total += t.area();

	double pts[] = {1,2,3,4,5,6,7,8,9,10};
	Polygon p(BLUE,pts,5);
    assert(fabs(p.area()-0)<0.0000001);
	total += p.area();

    Box bb(BLUE,-1,1,1,-1);
	assert(fabs(bb.area()-4) < 0.0000001);
	total += bb.area();

    Circle cc(BLACK,5,5,2);
	assert(fabs(cc.area()-4*M_PI) < 0.0000001);
	total += cc.area();

    Triangle tt(RED,0,0,10,0,0,1);
	assert(fabs(tt.area()-5) < 0.0000001);
	total += tt.area();

	double pts2[] = {0,0,1,0,0,1,1,1,0,2};
    Polygon pp(YELLOW,pts2,5);
	assert(fabs(pp.area()-1) < 0.0000001);
	total += pp.area();

	RoundBox rb(WHITE,0,3,4,0,1);
	assert(fabs(rb.area()-(8.0+M_PI)) < 0.0000001);
	total += rb.area();

	Shape * shapes[9];
	shapes[0] = new Box(BLUE,1,4,3,2);
	shapes[1] = new Circle(BLUE,1,2,3);
	shapes[2] = new Triangle(BLUE,1,2,3,4,3,2);
	shapes[3] = new Polygon(BLUE,pts,5);
	shapes[4] = new Box(BLUE,-1,1,1,-1);
	shapes[5] = new Circle(BLACK,5,5,2);
	shapes[6] = new Triangle(RED,0,0,10,0,0,1);
	shapes[7] = new Polygon(YELLOW,pts2,5);
	shapes[8] = new RoundBox(WHITE,0,3,4,0,1);
	Group g(RED, 9, shapes);
	assert(fabs(g.area()-total) < 0.0000001);
EOF
); }


/** @test
    @prereq public_interface
    @score  0.05 */
function non_value_semantics() { return anti_compile_test("Shapes should prevent value-semantic operations such as the following:",<<<EOF
#include "Shapes.h"
int main()
{
    Box q(BLUE,1,2,3,4);
    Box p(q);
}
EOF
) && anti_compile_test("Shapes should prevent value-semantic operations such as the following:",<<<EOF
#include "Shapes.h"
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
	@score 0.05 */
function rendering() { return assertion_tests3('new.cpp Shapes.cpp',array('"Shapes.h"','<sstream>'), <<<EOF
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
) && assertion_tests3('new.cpp Shapes.cpp',array('"Shapes.h"','<sstream>'), <<<EOF
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
) && assertion_tests3('new.cpp Shapes.cpp',array('"Shapes.h"','<sstream>'), <<<EOF
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
) && assertion_tests3('new.cpp Shapes.cpp',array('"Shapes.h"','<sstream>'), <<<EOF
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
) && assertion_tests3('new.cpp Shapes.cpp',array('"Shapes.h"','<sstream>'), <<<EOF
	std::string name[]={"BLACK","RED","GREEN","YELLOW","BLUE","MAGENTA","CYAN","WHITE"};
    for (int i=0; i<8; i++) {
        Line l((Color)i,2,5,9,3);
        std::stringstream ss;
        l.render(ss);
        assert(ss.str() == "Line("+name[i]+",2,5,9,3)");
        l.end1X(7.6);
        l.color((Color)((i+1)%8));
        ss.str("");
        l.render(ss);
        assert(ss.str() == "Line("+name[(i+1)%8]+",7.6,5,9,3)");
    }
EOF
) && assertion_tests3('new.cpp Shapes.cpp',array('"Shapes.h"','<sstream>'), <<<EOF
	std::string name[]={"BLACK","RED","GREEN","YELLOW","BLUE","MAGENTA","CYAN","WHITE"};
    for (int i=0; i<8; i++) {
        RoundBox rb((Color)i,2,5,9,3,1);
        std::stringstream ss;
        rb.render(ss);
        assert(ss.str() == "RoundBox("+name[i]+",2,5,9,3,1)");
        rb.left(7.6);
		rb.color((Color)((i+1)%8));
        ss.str("");
        rb.render(ss);
        assert(ss.str() == "RoundBox("+name[(i+1)%8]+",7.6,5,9,3,1)");
    }
EOF
) && assertion_tests3('Shapes.cpp',array('"Shapes.h"','<sstream>'), <<<EOF
	std::string name[]={"BLACK","RED","GREEN","YELLOW","BLUE","MAGENTA","CYAN","WHITE"};
    for (int i=0; i<8; i++) {
        Color j = (Color)((i+1)%8);
        Shape * s1[]={new Box(RED,2,5,9,3),new Circle(CYAN,2,2,2)};
        Group * g = new Group((Color)i,2,s1);
        std::stringstream ss;
        g->render(ss);
        assert(ss.str() == "Group(" + name[i] + ",2,Box("+name[i]+",2,5,9,3),Circle("+name[i]+",2,2,2))");
		g->color(j);
        ss.str("");
        g->render(ss);
        assert(ss.str() == "Group(" + name[j] + ",2,Box("+name[j]+",2,5,9,3),Circle("+name[j]+",2,2,2))");
    	Shape * s2[]={new Box(CYAN,1,2,2,1),g,new RoundBox(CYAN,2,2,2,2,2)};
    	Group h((Color)i,3,s2);
        ss.str("");
        h.render(ss);
        assert(ss.str() == "Group(" + name[i] + ",3,Box(" + name[i] + ",1,2,2,1),Group(" + name[i] + ",2,Box("+name[i]+",2,5,9,3),Circle("+name[i]+",2,2,2)),RoundBox("+name[i]+",2,2,2,2,2))");
    	h.color(j);
        ss.str("");
        h.render(ss);
        assert(ss.str() == "Group(" + name[j] + ",3,Box(" + name[j] + ",1,2,2,1),Group(" + name[j] + ",2,Box("+name[j]+",2,5,9,3),Circle("+name[j]+",2,2,2)),RoundBox("+name[j]+",2,2,2,2,2))");
    }
EOF
)
;}

/** @test
	@prereq bounds
	@score 0.05 */
function movement() { return assertion_tests3('new.cpp Shapes.cpp',array('"Shapes.h"'), <<<EOF
	double pts[] = {0,0,1,0,0,1,1,1,0,2};
	Box b(BLUE,-1,1,1,-1);
    Circle c(BLACK,5,5,2);
    Triangle t(RED,0,0,10,0,0,1);
    Polygon p(YELLOW,pts,5);
	Line l(WHITE,0,1,5,7.6);
	RoundBox rb(MAGENTA,-1,3,4,2,1.5);

	b.move(-2, -.5);
	c.move(-.5, 2.5);
	t.move(.5, .75);
	p.move(-1, 2.5);
	l.move(-.5,3);
	rb.move(1,2);

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

	//-.5, 4, 4, 10.6
	assert(l.end1X() == -.5);
	assert(l.end1Y() == 4);
	assert(l.end2X() == 4.5);
	assert(l.end2Y() == 10.6);

	//0,5,5,4
	assert(rb.left() == 0);
	assert(rb.top() == 5);
	assert(rb.right() == 5);
	assert(rb.bottom() == 4);
EOF
); }


/** @test
	@prereq movement
	@score 0.05 */
function polymorphism() {  return execution_test("new.cpp Shapes.cpp polymorphism.cpp",$output)
        && output_contains_lines($output,<<<EOF
Box(BLUE,-1,2,2,-1)
Circle(BLUE,2,5,3)
Triangle(YELLOW,0,0,0,3,3,2)
Polygon(WHITE,4,0,0,1,4,0,-1,-3,-1)
Line(BLACK,3,4,5,6)
RoundBox(GREEN,2.5,5,3,-1,1.5)
Box(BLUE,0,1,3,-2)
Circle(BLUE,3,4,3)
Triangle(YELLOW,1,-1,1,2,4,1)
Polygon(WHITE,4,1,-1,2,3,1,-2,-2,-2)
Line(BLACK,4,3,6,5)
RoundBox(GREEN,3.5,4,4,-2,1.5)
Group(RED,6,Box(RED,0,1,3,-2),Circle(RED,3,4,3),Triangle(RED,1,-1,1,2,4,1),Polygon(RED,4,1,-1,2,3,1,-2,-2,-2),Line(RED,4,3,6,5),RoundBox(RED,3.5,4,4,-2,1.5))
Group(RED,6,Box(RED,-1,2,2,-1),Circle(RED,2,5,3),Triangle(RED,0,0,0,3,3,2),Polygon(RED,4,0,0,1,4,0,-1,-3,-1),Line(RED,3,4,5,6),RoundBox(RED,2.5,5,3,-1,1.5))
EOF
);}

/** @test
	@prereq area
	@score 0.1 */
function thickness() { return assertion_tests3('new.cpp Shapes.cpp', array('"Shapes.h"','<cmath>'),<<<EOF
	Box b(BLUE,1,4,3,2);
	assert(fabs(b.thickness()-(4.0/8.0))<0.0000001);

	Circle c(BLUE,1,2,3);
	assert(fabs(c.thickness()-((M_PI*3*3)/(M_PI*6)))<0.0000001);

	Triangle t(BLUE,1,2,3,4,3,2);
	assert(fabs(t.thickness()-(2.0/(sqrt(8.0)+4.0)))<0.0000001);

	double pts[] = {1,2,3,4,5,6,7,8,9,10};
	Polygon p(BLUE,pts,5);
    assert(fabs(p.thickness()-(0))<0.0000001);

    Box bb(BLUE,0,1,1,0);
	assert(fabs(bb.thickness()-(.25)) < 0.0000001);

    Circle cc(BLACK,5,5,2);
	assert(fabs(cc.thickness()-((4.0*M_PI)/(4.0*M_PI))) < 2);

    Triangle tt(RED,0,0,10,0,0,1);
	assert(fabs(tt.thickness()-(5.0/(11.0 + sqrt(101.0)))) < 0.0000001);

	double pts2[] = {0,0,1,0,1,1, 0,2,0,1};
    Polygon pp(YELLOW,pts2,5);
	assert(fabs(pp.thickness()-(1.5/(4.0+sqrt(2.0)))) < 0.0000001);

	RoundBox rb(WHITE,0,3,4,0,1);
	assert(fabs(rb.thickness()-((8.0+M_PI)/(6.0+2.0*M_PI))) < 0.0000001);

	Shape * shapes[5];
	shapes[0] = new Box(BLUE,1,4,3,2);
	shapes[1] = new Circle(BLUE,1,2,3);
	shapes[2] = new Triangle(BLUE,1,2,3,4,3,2);
	shapes[3] = new Polygon(BLUE,pts,5);
	shapes[4] = new RoundBox(WHITE,0,3,4,0,1);

	double atotal = 0;
	double ptotal = 0;
	for(int i = 0; i < 5; i++)
	{
		atotal += shapes[i]->area();
		ptotal += shapes[i]->perimeter();
	}

	Group g(RED,5,shapes);
	assert(fabs(g.thickness()-(atotal/ptotal)) < 0.0000001);
EOF
);}

/** @test
	@prereq bounds
	@score 0.1 */
function inside() { return assertion_tests3('new.cpp Shapes.cpp', array('"Shapes.h"'),<<<EOF
	Box b(BLUE,1,4,3,2);
	assert(b.inside(1.5,3.5));
	assert(!b.inside(0,5));

	Circle c(BLUE,1,2,3);
	assert(c.inside(2,2.5));
	assert(!c.inside(4.5,5));

	Triangle t(BLUE,1,2,3,4,3,2);
	assert(t.inside(2.5,2.05));
	assert(!t.inside(3.5, 2.5));

	double pts[] = {1,2,3,5,5,6,5,3,4,1};
	Polygon p(BLUE,pts,5);
    assert(p.inside(3,2));
	assert(!p.inside(2,4));

	RoundBox rb(WHITE,0,3,4,0,1);
	assert(rb.inside(2.5,2.5));
	assert(!rb.inside(2.96,3.96));
	assert(!rb.inside(5, 5));

	Shape * shapes[2];
	shapes[0] = new Box(BLUE,0,1,2,0);
	shapes[1] = new Circle(RED,-1,-1,1);
	Group g(RED,2,shapes);
	assert(g.inside(1,.5));
	assert(g.inside(-1,-.5));
	assert(!g.inside(-2,-5));


EOF
);}

/** @test
	@prereq inside
	@score 0.05 */
function colorPoint() {  return assertion_tests3('Shapes.cpp', array('"Shapes.h"'),<<<EOF
	Shape * sg[2];
	sg[0] = new Circle(YELLOW, 2, 2, 6);
	sg[1] = new Circle(BLUE, -6, -6, 1);

	Shape * shapes[4];
	shapes[0] = new Circle(WHITE, 2, 2, 3);
	shapes[1] = new Circle(YELLOW, 2, 2, 4);
	shapes[2] = new Circle(BLUE, 2, 2, 5);
	shapes[3] = new Group(RED, 2, sg);

	assert(Shape::colorAtPoint(shapes, 4, 2, 2) == WHITE);
	assert(Shape::colorAtPoint(shapes, 4, 2, 5.1) == YELLOW);
	assert(Shape::colorAtPoint(shapes, 4, 2, 6.1) == BLUE);
	assert(Shape::colorAtPoint(shapes, 4, 2, 7.1) == RED);
	assert(Shape::colorAtPoint(shapes, 4, -6, -6) == RED);

	delete shapes[0];
	delete shapes[1];
	delete shapes[2];
	delete shapes[3];
EOF
);}

include 'oop_scoring.php';
