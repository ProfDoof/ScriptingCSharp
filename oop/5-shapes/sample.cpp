#include <iostream>
#include <sstream>
#include "Shapes.h"
using namespace std;

int main()
{
	//Old
    double pts[] = {1,1,7,2,3,5,6,8,4,3};
    Shape * list[100];
    int count = 0;
    list[count++] = new Box(BLUE,0,1,1,0);
    list[count++] = new Box(CYAN,2,9,4,3);
    list[count++] = new Circle(WHITE,5,5,3);
    list[count++] = new Triangle(BLACK,1,1,5,1,3,3);
    list[count++] = new Polygon(GREEN,pts,5);

    double distance = 0;
    double area = 0;
    stringstream ss;

    for (int i=0; i<count; i++) {
        distance += list[i]->perimeter();
        area += list[i]->area();
        list[i]->render(ss);
        ss << "\n";
    }

    for (int i=0; i<count; i++) {
        list[i]->move(10,10);
        list[i]->render(ss);
        ss << "\n";
    }

    cout << "distance: " << distance << " area: " << area << "\n";
    cout << "drawing: " << ss.str();
    for (int i=0; i<count; i++)
    {
        delete list[i];
    }

	//Groups
	Shape * list3[2];
	list3[0] = new Box(GREEN, 0, 1, 1, 0);
	list3[1] = new Circle(YELLOW, 2, 2, 2);
	Group g(BLUE, 2, list3);

	cout << "Group area: " << g.area() << "\n";
	cout << "Group perimeter: " << g.perimeter() << "\n";

	g.move(1,1);
	g.render(cout); cout << "\n";
	g.color(RED);
	g.render(cout); cout << "\n";

	cout << "Count: " << g.shapes() << "\n";
	g.shape(1)->render(cout); cout << "\n";

	Shape * list2[3];
	list2[0] = new Circle(WHITE,5,5,1);
	list2[1] = new Box(GREEN,7,1,9,-10);
	list2[2] = new RoundBox(BLACK,5,5,8.5,4.5,0.1);
	g.shapes(3,list2);
	g.render(cout); cout << "\n";

}
