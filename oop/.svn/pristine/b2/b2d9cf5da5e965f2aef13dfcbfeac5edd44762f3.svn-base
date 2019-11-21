#include <iostream>
INCLUDE_SHAPES
using namespace std;

int main()
{
	double pts[] = {0,0,1,4,0,-1,-3,-1};
	Shape * shapes[6];
	shapes[0] = new Box(BLUE,-1,2,2,-1);
	shapes[1] = new Circle(BLUE, 2, 5, 3);
	shapes[2] = new Triangle(YELLOW, 0,0,0,3,3,2);
	shapes[3] = new Polygon(WHITE, pts, 4);
	shapes[4] = new Line(BLACK,3,4,5,6);
	shapes[5] = new RoundBox(GREEN, 2.5, 5, 3, -1, 1.5);

	for(int i = 0; i < 6; i++)
	{
		shapes[i]->render(cout);
		cout << "\n";
	}

	for(int i = 0; i < 6; i++)
	{
		shapes[i]->move(1,-1);
	}

	for(int i = 0; i < 6; i++)
	{
		shapes[i]->render(cout);
		cout << "\n";
	}

	for(int i = 0; i < 6; i++)
		delete shapes[i];
}
