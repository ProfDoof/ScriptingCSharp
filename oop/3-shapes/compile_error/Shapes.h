#include <cassert>
#include <cmath>
#include <ostream>

enum Color { BLACK, RED, GREEN, YELLOW, BLUE, MAGENTA, CYAN, WHITE };

class Shape {
public:
    Shape(Color c) : hue(c) { }
    virtual ~Shape() {}
    
    Color           color() const           { return hue; }
    void            color(Color c)          { hue = c; }
    
    virtual double  area() const            = 0;
    virtual double  perimeter() const       = 0;
    
    virtual void    move(double dx,double dy) = 0;
    virtual void    render(std::ostream &os) const = 0;
private:
    Color           hue;
};

class Box : public Shape {
public:
    Box(Color c,double _left,double _top,double _right,double _bottom)
        : Shape(c), l(_left), t(_top), r(_right), b(_bottom) {}
        
    double          left() const            { return l; }
    double          top() const             { return t; }
    double          right() const           { return r; }
    double          bottom() const          { return b; }
    
    void            left(double _left)      { l = _left; }
    void            top(double _top)        { t = _top; }
    void            right(double _right)    { r = _right; }
    void            bottom(double _bottom)  { b = _bottom; }
    
 
    double          perimeter() const;
    void            move(double dx,double dy);
    void            render(std::ostream &os) const;
private:
    double          l,t,r,b;
};

class Circle : public Shape {
public:
    Circle(Color c,double _centerx,double _centery,double _radius)
        : Shape(c), cx(_centerx), cy(_centery), r(_radius) {}
        
    void            centerX(double _centerX) { cx = _centerX; }
    void            centerY(double _centerY) { cy = _centerY; }
    void            radius(double _radius)   { r = _radius; }
    
    double          centerX() const          { return cx; }
    double          centerY() const          { return cy; }
    double          radius() const           { return r; }
    
    double          area() const;
    double          perimeter() const;
    void            move(double dx,double dy);
    void            render(std::ostream &os) const;
private:
    double          cx,cy,r;
};

class Triangle : public Shape {
public:
    Triangle(Color c,double cx1,double cy1,double cx2,double cy2,double cx3,double cy3)
        : Shape(c), x1(cx1),y1(cy1),x2(cx2),y2(cy2),x3(cx3),y3(cy3) {}

    double          cornerX1() const        { return x1; }
    double          cornerX2() const        { return x2; }
    double          cornerX3() const        { return x3; }
    double          cornerY1() const        { return y1; }
    double          cornerY2() const        { return y2; }
    double          cornerY3() const        { return y3; }
    
    void            cornerX1(double cx1)    { x1=cx1; }
    void            cornerX2(double cx2)    { x2=cx2; }
    void            cornerX3(double cx3)    { x3=cx3; }
    void            cornerY1(double cy1)    { y1=cy1; }
    void            cornerY2(double cy2)    { y2=cy2; }
    void            cornerY3(double cy3)    { y3=cy3; }

    double          area() const;
    double          perimeter() const;
    void            move(double dx,double dy);
    void            render(std::ostream &os) const;
private:
    static double   dist(double dx,double dy);
    
    double          x1,y1,x2,y2,x3,y3;
};

class Polygon : public Shape {
public:
    Polygon(Color c,double *pts,int n);
    ~Polygon() { delete [] p; }
    
    int             points() const          { return N; }
    
    double          vertexX(int i) const    { assert(i>=0 && i<N); return p[i*2]; }
    double          vertexY(int i) const    { assert(i>=0 && i<N); return p[i*2+1]; }
    
    void            vertexX(int i,double x) { assert(i>=0 && i<N); p[i*2] = x; }
    void            vertexY(int i,double y) { assert(i>=0 && i<N); p[i*2+1] = y; }

    double          area() const;
    double          perimeter() const;
    void            move(double dx,double dy);
    void            render(std::ostream &os) const;
private:
    int             N;
    double *        p;
};

