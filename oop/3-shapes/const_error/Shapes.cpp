#include <string>
#include "Shapes.h"

std::string names[] = {"BLACK", "RED", "GREEN", "YELLOW", "BLUE", "MAGENTA", "CYAN", "WHITE" };


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
