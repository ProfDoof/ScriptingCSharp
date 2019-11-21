template <class T>
class Point {
public:
    Point() : x(0),y(0);
    Point(T a,T b) : x(a),y(b) {}
    Point(const Vector<T> &v) : x(v.dx),y(v.dy) {}
    
    void print(ostream &os) const { os << "(" << x << "," << y << ")"; }
    void parse(istream &is) { char c; is >> c >> x >> c >> y >> c; }
    
    T x,y;
};

template <class T>
class Vector {
public:
    Vector() : dx(0),dy(0);
    Vector(T a,T b) : dx(a),dy(b) {}
    
    void print(ostream &os) const { os << "[" << dx << "," << dy << "]"; }
    void parse(istream &is) { char c; is >> c >> dx >> c >> dy >> c; }

};

// points
template <class T> Point<T> operator+(const Point<T> &p,const Vector<T> &v) { return Point(p.x+v.dx,p.y+v.dy); }
template <class T> Point<T> operator-(const Point<T> &p,const Vector<T> &v) { return Point(p.x-v.dx,p.y-v.dy); }
template <class T> Point<T> operator+(const Vector<T> &v,const Point<T> &p) { return Point(p.x+v.dx,p.y+v.dy); }
template <class T> Vector<T> operator-(const Point<T> &p,const Point<T> &q) { return Vector(p.x-q.x,p.y-q.y); }

template <class T> const Point<T> & operator+=(Point<T> &p,const Vector<T> &v) { p+=v.dx; p+=v.dy; return *this; }
template <class T> const Point<T> & operator-=(Point<T> &p,const Vector<T> &v) { p-=v.dx; p-=v.dy; return *this; }

template <class T> bool operator==(const Point<T> &p,const Point<T> &q) { return p.x==q.x && p.y==q.y; }
template <class T> bool operator!=(const T &p,const T &q) { return !(p==q); }

template <class T> ostream & operator<<(ostream &os,const Point<T> &p) { p.print(os); return os; }

// vectors
template <class T> Vector<T> operator+(const Vector<T> &v,const Vector<T> &w) { return Vector(v.dx+w.dx,v.dy+w.dy); }
template <class T> Vector<T> operator-(const Vector<T> &v,const Vector<T> &w) { return Vector(v.dx-w.dx,v.dy-w.dy); }

template <class T> const Vector<T> & operator+=(const Vector<T> &v,const Vector<T> &w) { v.dx+=w.dx; v.dy+=w.dy; return *this; }
template <class T> const Vector<T> & operator-=(const Vector<T> &v,const Vector<T> &w) { v.dx-=w.dx; v.dy-=w.dy; return *this; }

template <class T> bool operator==(const Vector<T> &v,const Vector<T> &w) { return v.dx==w.dx && v.dy==w.dy; }

template <class T> ostream & operator<<(ostream &os,const Vector<T> &v) { v.print(os); return os; }

