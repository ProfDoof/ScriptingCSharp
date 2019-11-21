#include <iostream>
#include <cassert>
#include "Shapes.h"
using namespace std;

int main()
{
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
}