#include <iostream>
INCLUDE_SHAPES
using namespace std;

int main()
{
	double pts[] = {0,0,1,4,0,-1,-3,-1};
	Shape * shapes[4];
	shapes[0] = new Box(BLUE,-1,2,2,-1);
	shapes[1] = new Circle(BLUE, 2, 5, 3);
	shapes[2] = new Triangle(YELLOW, 0,0,0,3,3,2);
	shapes[3] = new Polygon(WHITE, pts, 4);

	for(int i = 0; i < 4; i++)
	{
		shapes[i]->render(cout);
		cout << "\n";
	}

	for(int i = 0; i < 4; i++)
		shapes[i]->move(1,-1);

	for(int i = 0; i < 4; i++)
	{
		shapes[i]->render(cout);
		cout << "\n";
	}

	for(int i = 0; i < 4; i++)
		delete shapes[i];
}
