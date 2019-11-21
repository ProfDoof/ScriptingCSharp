#include <iostream>
#include <string>

enum						Color { BLACK, RED, GREEN, YELLOW, BLUE, MAGENTA, CYAN, WHITE };

class Shape {
public:
	Shape()												{ }
	Shape(Color c) : colorVal(c) { }
	Shape(const Shape &other)							{ color(other.colorVal); }
	virtual ~Shape()											{ }
	
	virtual void			move(double a, double b) = 0;
	virtual double			area() const = 0;
	virtual double			perimeter() const = 0;
	virtual void			render(std::ostream& os) const = 0;
	
	Color				 	color() const				{ return colorVal; }
	void 					color(Color change)			{ colorVal = change; }
	
protected:
	Color colorVal;
};


class Box : public Shape {
public:
	Box(Color c, double l, double t, double r, double b) : Shape(c), leftVal(l), topVal(t), rightVal(r), bottomVal(b) { }
	
	void					move(double a, double b)	{ leftVal += a; rightVal += a; topVal += b; bottomVal += b; }
	double					area() const;
	double					perimeter() const;
	void					render(std::ostream& os) const;
	
		// getting values
	double					left() const				{ return leftVal; }
	double					top() const					{ return topVal; }
	double					right() const				{ return rightVal; }
	double					bottom() const				{ return bottomVal; }
		// setting values
	void					left(double l)				{ leftVal = l; }
	void					top(double t)				{ topVal = t; }
	void					right(double r)				{ rightVal = r; }
	void					bottom(double b)			{ bottomVal = b; }
	
private:
	double leftVal, topVal, rightVal, bottomVal;
	Box(const Box &other);
	const Box& operator= (const Box &other);
};


class Circle : public Shape {
public:
	Circle(Color c, double cX, double cY, double rds) : Shape(c), centerXVal(cX), centerYVal(cY), radiusVal(rds) { }
	
	void					move(double a, double b)	{ centerXVal += a; centerYVal += b; }
	double					area() const;
	double					perimeter() const;
	void					render(std::ostream& os) const;
	
		// getting values
	double					centerX() const				{ return centerXVal; }
	double					centerY() const				{ return centerYVal; }
	double					radius() const				{ return radiusVal; }
		// setting values
	void					centerX(double x)			{ centerXVal = x; }
	void					centerY(double y)			{ centerYVal = y; }
	void					radius(double r)			{ radiusVal = r; }
	
private:
	double centerXVal, centerYVal, radiusVal;
	Circle(const Circle &other);
	const Circle& operator= (const Circle &other);

};


class Triangle : public Shape {
public:
	Triangle(Color c, double cx1, double cy1, double cx2, double cy2, double cx3, double cy3) : Shape(c), cornerX1Val(cx1), cornerY1Val(cy1), cornerX2Val(cx2), cornerY2Val(cy2), cornerX3Val(cx3), cornerY3Val(cy3) { }
	
	void					move(double a, double b)			{ cornerX1Val += a; cornerX2Val += a; cornerX3Val += a; cornerY1Val += b; cornerY2Val += b; cornerY3Val += b; }
	double					area()const;
	double					perimeter() const;
	void					render(std::ostream& os) const;
	
		// getting values
	double					cornerX1() const			{ return cornerX1Val; }
	double					cornerX2() const			{ return cornerX2Val; }
	double					cornerX3() const			{ return cornerX3Val; }
	double					cornerY1() const			{ return cornerY1Val; }
	double					cornerY2() const			{ return cornerY2Val; }
	double					cornerY3() const			{ return cornerY3Val; }
		// setting values
	void					cornerX1(double cx1)		{ cornerX1Val = cx1; }
	void					cornerX2(double cx2)		{ cornerX2Val = cx2; }
	void					cornerX3(double cx3)		{ cornerX3Val = cx3; }
	void					cornerY1(double cy1)		{ cornerY1Val = cy1; }
	void					cornerY2(double cy2)		{ cornerY2Val = cy2; }
	void					cornerY3(double cy3)		{ cornerY3Val = cy3; }
	
private:
	double cornerX1Val, cornerY1Val, cornerX2Val, cornerY2Val, cornerX3Val, cornerY3Val;
	Triangle(const Triangle &other);
	const Triangle& operator= (const Triangle &other);
};


class Polygon : public Shape {
public:
	Polygon(Color c, double a[], int p)
		: Shape(c), pointsVal(p)
	{
		pts = &a[0];
		// pts[pointsVal];
		// for (int i = 0; i < pointsVal; i++)
		// {
			// pts[i] = a[i];
		// }
	}
		
	void					move(double a, double b);
	double					area() const;
	double					perimeter() const;
	void					render(std::ostream& os) const;
	
	int						points() const					{ return pointsVal; }
	
		// getting values
	double					vertexX(int i) const;
	double					vertexY(int i) const;
	
		// setting values
	void					vertexX(int i, double value);
	void					vertexY(int i, double value);
	
protected:
	int pointsVal;
	double* pts;
private:
	const Polygon & operator= (const Polygon &other);
	Polygon(const Polygon &other);
};
