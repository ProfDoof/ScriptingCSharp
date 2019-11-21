template <class T>
class Vector {
public:
    Vector() : dx(0),dy(0) {}
    Vector(T a,T b) : dx(a),dy(b) {}
    
    void print(ostream &os) const { os << "[" << dx << "," << dy << "]"; }
    void parse(istream &is) { char c; is >> c >> dx >> c >> dy >> c; }

	T getdx() const {return dx;}
	T setdx(T x) {dx = x;}
	T getdy() const {return dy;}
	T setdy(T y) {dy = y;}
	
private:
    T dx,dy;
};
 
template <class T>
class Point {
public:
    Point() : x(0),y(0) {}
    Point(T a,T b) : x(a),y(b) {}
    Point(const Vector<T> &v) : x(v.dx),y(v.dy) {}
    
    void print(ostream &os) const { os << "(" << x << "," << y << ")"; }
    void parse(istream &is) { char c; is >> c >> x >> c >> y >> c; }
 
    T x,y;
};

// points
template <class T> Point<T> operator+(const Point<T> &p,const Vector<T> &v) { return Point<T>(p.x+v.getdx(),p.y+v.getdy()); }
template <class T> Point<T> operator-(const Point<T> &p,const Vector<T> &v) { return Point<T>(p.x-v.getdx(),p.y-v.getdy()); }
template <class T> Point<T> operator+(const Vector<T> &v,const Point<T> &p) { return Point<T>(p.x+v.getdx(),p.y+v.getdy()); }
template <class T> Vector<T> operator-(const Point<T> &p,const Point<T> &q) { return Vector<T>(p.x-q.x,p.y-q.y); }

template <class T> const Point<T> & operator+=(Point<T> &p,const Vector<T> &v) { p.x+=v.getdx(); p.y+=v.getdy(); return p; }
template <class T> const Point<T> & operator-=(Point<T> &p,const Vector<T> &v) { p.x-=v.getdx(); p.y-=v.getdy(); return p; }

template <class T> bool operator==(const Point<T> &p,const Point<T> &q) { return p.x==q.x && p.y==q.y; }
template <class T> bool operator!=(const T &p,const T &q) { return !(p==q); }

template <class T> ostream & operator<<(ostream &os,const Point<T> &p) { p.print(os); return os; }

// vectors
template <class T> Vector<T> operator+(const Vector<T> &v,const Vector<T> &w) { return Vector<T>(v.getdx()+w.dx,v.getdy()+w.dy); }
template <class T> Vector<T> operator-(const Vector<T> &v,const Vector<T> &w) { return Vector<T>(v.getdx()-w.dx,v.getdy()-w.dy); }

template <class T> const Vector<T> & operator+=(Vector<T> &v,const Vector<T> &w) { v.getdx()+=w.dx; v.getdy()+=w.dy; return v; }
template <class T> const Vector<T> & operator-=(Vector<T> &v,const Vector<T> &w) { v.getdx()-=w.dx; v.getdy()-=w.dy; return v; }

template <class T> Vector<T> operator*(const Vector<T> &v,const T &a) { return Vector<T>(v.getdx()*a,v.getdy()*a); }
template <class T> Vector<T> operator*(const T &a,const Vector<T> &v) { return Vector<T>(v.getdx()*a,v.getdy()*a); }
template <class T> const Vector<T> & operator*=(const Vector<T> &v,const T &a) { v.getdx()*=a; v.getdy()*=a; return v; }

template <class T> bool operator==(const Vector<T> &v,const Vector<T> &w) { return v.getdx()==w.dx && v.getdy()==w.dy; }

template <class T> ostream & operator<<(ostream &os,const Vector<T> &v) { v.print(os); return os; }

