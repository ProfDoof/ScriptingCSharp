#include <iostream>
#include "Shapes.h"
using namespace std;

int main()
{
	double pts[] = {0,0,1,0,0,1,1,1,0,2};
    Box b(BLUE,-1,1,1,-1);
    Circle c(BLACK,5,5,2);
    Triangle t(RED,0,0,10,0,0,1);
    Polygon p(YELLOW,pts,5);
  
	
	b.render(cout);
	cout << "\n";
	c.render(cout); 
	cout << "\n";
	t.render(cout); 
	cout << "\n";
	p.render(cout); 
	cout << "\n";
	
	b.right(3);
	c.radius(6);
	t.cornerY2(9);
	p.vertexX(3, 7.6);
	
	b.render(cout);
	cout << "\n";
	c.render(cout); 
	cout << "\n";
	t.render(cout); 
	cout << "\n";
	p.render(cout);
	cout << "\n";
}