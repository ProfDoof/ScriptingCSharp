#include <iostream>
#include <math.h>
#include "Shapes.h"

// returns distance between coordinates
double dist(double x1, double y1, double x2, double y2)
{
	double result = double( (sqrt(pow(x1 - x2, 2) + pow(y1 - y2, 2))));
	return result;
}

std::string colorNum[8] = { "BLACK", "RED", "GREEN", "YELLOW", "BLUE", "MAGENTA", "CYAN", "WHITE" };

double Box::area() const
{
	return (rightVal-leftVal)*(topVal-bottomVal);
}

double Box::perimeter() const
{
	return 2*(rightVal-leftVal+topVal-bottomVal);
}

void Box::render(std::ostream& os) const
{
	os << "Box(" << colorNum[colorVal] << "," << leftVal << "," << topVal << "," << rightVal << "," << bottomVal << ")";
}

Box::Box(const Box &other):Shape(other)
{
	leftVal = other.leftVal;
	topVal = other.topVal;
	rightVal = other.rightVal;
	bottomVal = other.bottomVal;
	color(other.color());
}

double Circle::area() const
{
	return 3.14159 * radiusVal * radiusVal;
}

double Circle::perimeter() const
{
	return 3.14159 * radiusVal * 2;
}

void Circle::render(std::ostream& os) const
{
	os << "Circle(" << colorNum[colorVal] << "," << centerXVal << "," << centerYVal << "," << radiusVal << ")";
}

Circle::Circle(const Circle &other):Shape(other) {}

double Triangle::area() const
{
	// double areaVal = 0;
	
	
	
	
	// areaVal += cornerX1Val * cornerY1Val;
	// areaVal += cornerX1Val * cornerY1Val;
	
	
	// for (int i = 0; i < pointsVal; i += 2)
	// {
		// int j = (i + 1) % pointsVal;
		// areaVal += pts[i] * pts[j];
		// areaVal -= pts[i] * pts[j];
	// }
	
	// areaVal /= 2;
	
	// return (areaVal < 0 ? -areaVal : areaVal);
	
	
	
	double a = dist(cornerX1Val, cornerY1Val, cornerX2Val, cornerY2Val);
	double b = dist(cornerX1Val, cornerY1Val, cornerX3Val, cornerY3Val);
	double c = dist(cornerX2Val, cornerY2Val, cornerX3Val, cornerY3Val);
	double s = (a + b + c)*.5;
	return sqrt(s*(s-a)*(s-b)*(s-c));
}

double Triangle::perimeter() const
{
	double a = dist(cornerX1Val, cornerY1Val, cornerX2Val, cornerY2Val);
	double b = dist(cornerX1Val, cornerY1Val, cornerX3Val, cornerY3Val);
	double c = dist(cornerX2Val, cornerY2Val, cornerX3Val, cornerY3Val);
	return a + b + c;
}

void Triangle::render(std::ostream& os) const
{
	os << "Triangle(" << colorNum[colorVal] << "," << cornerX1Val << "," << cornerY1Val << "," << cornerX2Val << "," << cornerY2Val << "," << cornerX3Val << "," << cornerY3Val << ")";
}

Triangle::Triangle(const Triangle &other):Shape(other) {}

double Polygon::area() const
{
	double areaVal = 0;

	for (int i = 0; i < pointsVal*2; i += 2)
	{
		int j = (i + 3) % (pointsVal*2);
		areaVal += pts[i] * pts[j];
		areaVal -= pts[j-1] * pts[i+1];
	}

	areaVal /= 2;
	
	return (areaVal < 0 ? -areaVal : areaVal);
}

double Polygon::perimeter() const
{
	double perimeterVal = 0;
	for (int i = 0; i < pointsVal*2; i += 2)
	{
		perimeterVal += dist(pts[i], pts[(i+1)], pts[(i+2)% (pointsVal*2)], pts[(i+3)% (pointsVal*2)]);
	}
	return perimeterVal;
}

void Polygon::render(std::ostream& os) const
{
	os << "Polygon(" << colorNum[colorVal] << "," << pointsVal << ",";
	for (int i = 0; i < pointsVal*2-1; i++)
	{
		os << pts[i] << ",";
	}
	os << pts[pointsVal*2-1] << ")";
}

void Polygon::move(double a, double b)
{
	for (int i = 0; i < pointsVal*2-1; i += 2)
	{
		pts[i] += a;
		pts[i+1] += b;
	}
}

	// getting values
double Polygon::vertexX(int i) const
{
	return pts[i*2];
}

double Polygon::vertexY(int i) const
{
	return pts[i*2+1];
}

	// setting values
void Polygon::vertexX(int i, double value)
{
	pts[i*2] = value;
}

void Polygon::vertexY(int i, double value)
{
	pts[i*2+1] = value;
}

Polygon::Polygon(const Polygon &other):Shape(other) {}
