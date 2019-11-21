#include <cassert>
#include <iostream>
#include <sstream>
#include <cmath>
using namespace std;
#include "Geometry.h"

bool check(const string &a,const string &b) 
{
    if (a==b || a==b+"\n")
        return true;
    cout << "check(\"" << a << "\",\"" << b << "\")\n";
    return false;
}

void test() {
#ifndef TEST
    #error TEST must be defined (1..10)
#elif TEST==1
    double pts[] = {0,0,1,0,0,1};
    Box b(BLUE,0,1,1,0);
    Circle c(BLACK,0,0,1);
    Triangle t(RED,pts);
    Polygon p(YELLOW,pts,3);

    assert(b.getColor() == BLUE);
    assert(c.getColor() == BLACK);
    assert(t.getColor() == RED);
    assert(p.getColor() == YELLOW);
    
    b.setColor(GREEN);
    c.setColor(MAGENTA);
    t.setColor(CYAN);
    p.setColor(WHITE);
    assert(b.getColor() == GREEN);
    assert(c.getColor() == MAGENTA);
    assert(t.getColor() == CYAN);
    assert(p.getColor() == WHITE);
#elif TEST==2
    double pts[] = {0,0,1,0,0,1};
    Box b(BLUE,0,1,1,0);
    Circle c(BLACK,0,0,1);
    Triangle t(RED,pts);
    Polygon p(YELLOW,pts,3);
    
    assert(abs(b.area()-1) < 0.0000001);
    assert(abs(c.area()-M_PI) < 0.0000001);
    assert(abs(t.area()-0.5) < 0.0000001);
    assert(abs(p.area()-0.5) < 0.0000001);
#elif TEST==3
    double pts[] = {0,0,1,0,0,1,1,1,0,2};
    Box b(BLUE,-1,1,1,-1);
    Circle c(BLACK,5,5,2);
    double tri[] = {0,0,10,0,0,1};
    Triangle t(RED,tri);
    Polygon p(YELLOW,pts,5);
    
    assert(abs(b.area()-4) < 0.0000001);
    assert(abs(c.area()-4*M_PI) < 0.0000001);
    assert(abs(t.area()-5) < 0.0000001);
    assert(abs(p.area()-1) < 0.0000001);
#elif TEST==4
    double pts[] = {0,0,1,0,0,1};
    Box b(BLUE,0,1,1,0);
    Circle c(BLACK,0,0,1);
    Triangle t(RED,pts);
    Polygon p(YELLOW,pts,3);
    
    assert(abs(b.perimeter()-4) < 0.0000001);
    assert(abs(c.perimeter()-2*M_PI) < 0.0000001);
    assert(abs(t.perimeter()-(2+sqrt(2))) < 0.0000001);
    assert(abs(p.perimeter()-(2+sqrt(2))) < 0.0000001);
#elif TEST==5
    double pts[] = {0,0,1,0,0,1,1,1,0,2};
    Box b(BLUE,-1,1,1,-1);
    Circle c(BLACK,5,5,2);
    double tri[] = {0,0,10,0,0,1};
    Triangle t(RED,tri);
    Polygon p(YELLOW,pts,5);
    
    assert(abs(b.perimeter()-8) < 0.0000001);
    assert(abs(c.perimeter()-4*M_PI) < 0.0000001);
    assert(abs(t.perimeter()-(11+sqrt(101))) < 0.0000001);
    assert(abs(p.perimeter()-(4+2*sqrt(2))) < 0.0000001);
#elif TEST==6
    double pts[] = {0,0,1,0,0,1,1,1,0,2};
    Box b(BLUE,-1,1,1,-1);
    Circle c(BLACK,5,5,2);
    double tri[] = {0,0,10,0,0,1};
    Triangle t(RED,tri);
    Polygon p(YELLOW,pts,5);
    
    b.translate(-5,-6);
    c.translate(-15,-6);
    t.translate(-5,-16);
    p.translate(-15,-16);
    
    assert(abs(b.area()-4) < 0.0000001);
    assert(abs(c.area()-4*M_PI) < 0.0000001);
    assert(abs(t.area()-5) < 0.0000001);
    assert(abs(p.area()-1) < 0.0000001);

    assert(abs(b.perimeter()-8) < 0.0000001);
    assert(abs(c.perimeter()-4*M_PI) < 0.0000001);
    assert(abs(t.perimeter()-(11+sqrt(101))) < 0.0000001);
    assert(abs(p.perimeter()-(4+2*sqrt(2))) < 0.0000001);
#elif TEST==7
    double pts[] = {0,0,1,0,0,1,1,1,0,2};
    Box b(BLUE,-1,1,1,-1);
    Circle c(BLACK,5,5,2);
    double tri[] = {0,0,10,0,0,1};
    Triangle t(RED,tri);
    Polygon p(YELLOW,pts,5);
    stringstream ss;
    
    ss.str(""); b.draw(ss); assert(check(ss.str(),"Box(BLUE,-1,1,1,-1)"));
    ss.str(""); c.draw(ss); assert(check(ss.str(),"Circle(BLACK,5,5,2)"));
    ss.str(""); t.draw(ss); assert(check(ss.str(),"Triangle(RED,0,0,10,0,0,1)"));
    ss.str(""); p.draw(ss); assert(check(ss.str(),"Polygon(YELLOW,5,0,0,1,0,0,1,1,1,0,2)"));
#elif TEST==8
    double pts[] = {0,0,1,0,0,1,1,1,0,2};
    Box b(BLUE,-1,1,1,-1);
    Circle c(BLACK,5,5,2);
    double tri[] = {0,0,10,0,0,1};
    Triangle t(RED,tri);
    Polygon p(YELLOW,pts,5);

    b.translate(-5,-6);
    c.translate(-15,-6);
    t.translate(1,2);
    p.translate(0,-1);
    b.setColor(GREEN);
    c.setColor(MAGENTA);
    t.setColor(CYAN);
    p.setColor(WHITE);
    
    stringstream ss;
    
    ss.str(""); b.draw(ss); assert(check(ss.str(),"Box(GREEN,-6,-5,-4,-7)"));
    ss.str(""); c.draw(ss); assert(check(ss.str(),"Circle(MAGENTA,-10,-1,2)"));
    ss.str(""); t.draw(ss); assert(check(ss.str(),"Triangle(CYAN,1,2,11,2,1,3)"));
    ss.str(""); p.draw(ss); assert(check(ss.str(),"Polygon(WHITE,5,0,-1,1,-1,0,0,1,0,0,1)"));
#elif TEST==9
    double pts[] = {0,0,1,0,0,1,1,1,0,2};
    Box b(BLUE,-1,1,1,-1);
    Circle c(BLACK,5,5,2);
    double tri[] = {0,0,10,0,0,1};
    Triangle t(RED,tri);
    Polygon p(YELLOW,pts,5);
    Geometry *list[] = {&b,&c,&t,&p};
    double area[4];
    double peri[4];
    string draw[4];
    for (int i=0; i<4; i++) {
        list[i]->translate(1,1);
        list[i]->setColor(GREEN);
        assert(list[i]->getColor() == GREEN);
        area[i] = list[i]->area();
        peri[i] = list[i]->perimeter();
        stringstream ss;
        list[i]->draw(ss);
        draw[i] = ss.str();
    }
    assert(area[0] == b.area());    
    assert(area[1] == c.area());    
    assert(area[2] == t.area());    
    assert(area[3] == p.area());    
    
    assert(peri[0] == b.perimeter());    
    assert(peri[1] == c.perimeter());    
    assert(peri[2] == t.perimeter());    
    assert(peri[3] == p.perimeter()); 
       
    assert(check(draw[0],"Box(GREEN,0,2,2,0)"));    
    assert(check(draw[1],"Circle(GREEN,6,6,2)"));    
    assert(check(draw[2],"Triangle(GREEN,1,1,11,1,1,2)"));    
    assert(check(draw[3],"Polygon(GREEN,5,1,1,2,1,1,2,2,2,1,3)")); 
#else
    #error TEST must be defined
#endif
}

#include <windows.h>

int main()
{
    DWORD dwMode = SetErrorMode(SEM_NOGPFAULTERRORBOX);
    SetErrorMode(dwMode | SEM_NOGPFAULTERRORBOX);
    
    test();
}
