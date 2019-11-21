#include <string>
#include "Shapes.h"
#include <iostream>

std::string names[] = {"BLACK", "RED", "GREEN", "YELLOW", "BLUE", "MAGENTA", "CYAN", "WHITE" };


Color Shape::colorAtPoint(Shape * shapes[], int n, double x, double y)
{
	for(int i = 0; i < n; i++)
		if(shapes[i]->inside(x,y))
			return shapes[i]->color();
			
	return (Color)300;
}


bool Box::inside(double x, double y) const
{
	return x > left() && x < right() && y < top() && y > bottom();
}

double Box::area() const
{
    return (r-l)*(t-b);
}

double Box::perimeter() const
{
    return 2*(r-l) + 2*(t-b);
}

void Box::render(std::ostream &os) const
{
	os << "Box(" << names[color()] << "," << l << "," << t << "," << r << "," << b << ")";
}

void Box::move(double dx,double dy) 
{
    l += dx;
    r += dx;
    t += dy;
    b += dy;
}

bool Circle::inside(double x, double y) const
{
	return sqrt((x - centerX())*(x - centerX()) + (y - centerY())*(y - centerY())) < radius();
}

double Circle::area() const
{
    return M_PI * r*r;
}

double Circle::perimeter() const
{
    return M_PI * r * 2;
}

void Circle::render(std::ostream &os) const
{
    os << "Circle(" << names[color()] << "," << cx << "," << cy << "," << r << ")";
}

void Circle::move(double dx,double dy) 
{
    cx += dx;
    cy += dy;
}

bool Triangle::inside(double x, double y) const
{
	bool isInside = false;
	int i, j;
	
	double vertexX[3] = {cornerX1(), cornerX2(), cornerX3()};
	double vertexY[3] = {cornerY1(), cornerY2(), cornerY3()};
	
	for(i = 0, j = 2; i < 3; j = i++)
	{
		if( ((vertexY[i] > y) != (vertexY[j] > y)) &&
			(x < (vertexX[j] - vertexX[i]) * (y - vertexY[i]) / (vertexY[j] - vertexY[i]) + vertexX[i]))
			isInside = !isInside;
	}
	
	return isInside;
}

double Triangle::dist(double dx,double dy) 
{
    return sqrt(dx*dx+dy*dy);
}

double Triangle::area() const
{
    double a = dist(x2-x1,y2-y1);
    double b = dist(x3-x2,y3-y2);
    double c = dist(x1-x3,y1-y3);
    double s = (a+b+c)/2;
    return sqrt(s*(s-a)*(s-b)*(s-c));
}

double Triangle::perimeter() const
{
    return dist(x2-x1,y2-y1)+dist(x3-x2,y3-y2)+dist(x1-x3,y1-y3);
}

void Triangle::render(std::ostream &os) const
{
    os << "Triangle(" << names[color()] << "," << x1 << "," << y1 << "," << x2 << "," << y2 << "," << x3 << "," << y3 << ")";
}

void Triangle::move(double dx,double dy) 
{
    x1 += dx;
    y1 += dy;
    x2 += dx;
    y2 += dy;
    x3 += dx;
    y3 += dy;
}

Polygon::Polygon(Color c,double *pts,int n)
  : Shape(c), N(0), p(0)
{
    p = new double[n*2];
    for (int i=0; i<n*2; i++)
        p[i] = pts[i];
    N = n;
}

bool Polygon::inside(double x, double y) const
{
	bool isInside = false;
	int i, j;
	
	for(i = 0, j = points()-1; i < points(); j = i++)
	{
		if( ((vertexY(i) > y) != (vertexY(j) > y)) &&
			(x < (vertexX(j) - vertexX(i)) * (y - vertexY(i)) / (vertexY(j) - vertexY(i)) + vertexX(i)))
			isInside = !isInside;
	}
	
	return isInside;
}

double Polygon::area() const
{
	double a = 0;
	for(int i=0; i<N; i++)
		a += (p[2*i]*p[2*((i+1)%N)+1] - p[(2*i)+1]*p[2*((i+1)%N)]);
	return a/2;
}

double Polygon::perimeter() const
{
	double perim = 0;
	for(int i=0; i<N; i++)
		perim += sqrt(pow(p[2*i]-p[2*((i+1)%N)],2) + pow(p[2*i+1]-p[2*((i+1)%N)+1],2));
	return perim;
}

void Polygon::render(std::ostream &os) const 
{
    os << "Polygon(" << names[color()] << "," << N;
    for (int i=0; i<N*2; i++)
        os << "," << p[i];
    os << ")";
}

void Polygon::move(double dx,double dy) 
{
    for (int i=0; i<N; i++) {
        p[i*2  ] += dx;
        p[i*2+1] += dy;
    }
}

void	Line::move(double dx, double dy)
{
	x1 += dx;
	x2 += dx;
	y1 += dy;
	y2 += dy;
}

void	Line::render(std::ostream &os) const
{
	os << "Line(" << names[color()] << "," << x1 << "," << y1 << "," << x2 << "," << y2 << ")";
}

bool RoundBox::inside(double x, double y) const
{
	//cover the 2 inner rectangles
	if((x > left() && x < right() && y < top() - radius() && y > bottom() + radius()) ||
		(x > left() + radius() && x < right() - radius() && y < top() && y > bottom()))
		return true;
		
	//must be in one of the quarter circles
	double tl, bl, tr, br;
	tl = sqrt((x-left())*(x-left())+(y-top())*(y-top()));
	bl = sqrt((x-left())*(x-left())+(y-bottom())*(y-bottom()));
	tr = sqrt((x-right())*(x-right())+(y-top())*(y-top()));
	br = sqrt((x-right())*(x-right())+(y-bottom())*(y-bottom()));

	return tl < radius() || bl < radius() || tr < radius() || br < radius();
}

double RoundBox::perimeter() const
{
	return (right()-left())*2.0 + (top()-bottom())*2.0 - radius()*8.0 + M_PI*radius()*2.0;
}

double RoundBox::area() const
{
	return (right()-left())*(top()-bottom()) - radius()*radius()*4.0 + radius()*radius()*M_PI;
}

void RoundBox::render(std::ostream &os) const
{
	os << "RoundBox(" << names[color()] << "," << left() << "," << top() << "," << right() << "," << bottom() << "," << r << ")";
}

Group::Group(Color c, int n, Shape * list[])
	:Shape(c), count(n)
{
	s = new Shape*[count];
	for(int i = 0; i < count; i++)
		s[i] = list[i];
	color(c);
}

Group::~Group()
{
	for(int i = 0; i < count; i++)
		delete s[i];
	delete []s;
}

int Group::shapes() const
{
	int total = 0;
	for(int i = 0; i < count; i++)
		total += s[i]->shapes();
	return total;
}
	
void Group::shapes(int n, Shape * list[])
{
	for(int i = 0; i < count; i++)
		delete s[i];
	delete []s;
	
	count = n;
	s = new Shape*[count];
	for(int i = 0; i < count; i++)
		s[i] = list[i];	
	color(Shape::color());
}
	
double Group::area() const
{
	double total = 0;
	for(int i = 0; i < count; i++)
		total += s[i]->area();
	return total;
}

double Group::perimeter() const
{
	double total = 0;
	for(int i = 0; i < count; i++)
		total += s[i]->perimeter();
	return total;	
}

void Group::render(std::ostream &os) const
{
	os << "Group(" << names[color()] << "," << count;
		
	for(int i = 0; i < count; i++)
	{
		os << ",";
		s[i]->render(os);
	}
		
	os << ")";
}

void Group::move(double dx, double dy)
{
	for(int i = 0; i < count; i++)
		s[i]->move(dx,dy);
}

bool Group::inside(double x, double y) const
{
	for(int i = 0; i < count; i++)
		if(s[i]->inside(x,y))
			return true;
	return false;
}