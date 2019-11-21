#include <iostream>
#include <cassert>
INCLUDE_SHAPES
using namespace std;

int main()
{
	Shape * shapes[3];
	shapes[0] = new Circle(WHITE, 2, 2, 3);
	shapes[1] = new Circle(YELLOW, 2, 2, 4);
	shapes[2] = new Circle(BLUE, 2, 2, 5);

	assert(Shape::colorAtPoint(shapes, 3, 2, 2) == WHITE);
	assert(Shape::colorAtPoint(shapes, 3, 2, 5.1) == YELLOW);
	assert(Shape::colorAtPoint(shapes, 3, 2, 6.1) == BLUE);
	//assert(Shape::colorAtPoint(shapes, 3, 2, 7.1) == INVALID);

	cout << "Test Succeeded";

	delete shapes[0];
	delete shapes[1];
	delete shapes[2];
}
