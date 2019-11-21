#include <iostream>
using namespace std;
#include "Geometry.h"

int main()
{ 
    Point<int> p(3,4),q(10,3);
    Vector<int> v(1,2),w(-1,-5);
    
    q = p + w - v;
    p += w;
    p -= w;
    
    w = v + w;
    v -= w;
    w = v*2;
    
    v = q - p;
    
    if (p == q) cout << "p equals q\n";
    if (v != w) cout << "v does not equal w\n";
    
    cout << "p = " << p << endl;
    cout << "q = " << q << endl;
    cout << "v = " << v << endl;
    cout << "w = " << w << endl;
}
