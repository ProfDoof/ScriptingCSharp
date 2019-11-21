#include <cassert>
#include <sstream>
#include <iostream>
using namespace std;
#include <windows.h>
#include "ScatterData.h"

template <class T>
void test() {
#ifndef TEST
    #error TEST must be defined (1..10)
#elif TEST==11
    ScatterData<T> a;
    ScatterData<T> b;
    a.insert(1,2);
    b.insert(1,2);
    a.insert(5,6);
    b.insert(3,4);
    assert(!(a==b));
#elif TEST==12
    ScatterData<T> a;
    ScatterData<T> b;
    a.insert(1,2);
    b.insert(1,2);
    a.insert(3,4);
    b.insert(3,4);
    a.insert(5,6);
    b.insert(3,4);
    
    assert(!(a==b));
#elif TEST==13
    ScatterData<T> a;
    typename ScatterData<T>::Point p[3] = {
        typename ScatterData<T>::Point(1,2),
        typename ScatterData<T>::Point(3,4),
        typename ScatterData<T>::Point(5,6) };
    ScatterData<T> c(p,3);
    a.insert(1,2);
    a.insert(3,4);
    a.insert(5,6);
    assert(a==c);
#elif TEST==1
    ScatterData<T> a;
    ScatterData<T> b;
    typename ScatterData<T>::Point p[3] = {
        typename ScatterData<T>::Point(1,2),
        typename ScatterData<T>::Point(3,4),
        typename ScatterData<T>::Point(5,6) };
    ScatterData<T> c(p,3);
    
    assert(a==b);
    assert(b==a);
    
    a.insert(p[0]);
    b.insert(1,2);
    
    assert(a==b);
    assert(b==a);

    a.insert(p[1]);
    b.insert(3,4);
    
    assert(a==b);
    assert(b==a);

    a.insert(p[2]);
    b.insert(3,4);
    
    assert(!(a==b));
    assert(!(b==a));
    assert(a==c);
    assert(c==a);
    assert(!(c==b));
    assert(!(b==c));
#elif TEST==21
    ScatterData<T> a;
    ScatterData<T> b;
    a.insert(1,2);
    b.insert(1,3);
    assert(a!=b);
#elif TEST==2
    ScatterData<T> a;
    ScatterData<T> b;
    typename ScatterData<T>::Point p[3] = {
        typename ScatterData<T>::Point(1,2),
        typename ScatterData<T>::Point(3,4),
        typename ScatterData<T>::Point(5,6) };
    ScatterData<T> c(p,3);
    
    assert(!(a!=b));
    assert(!(b!=a));
    
    a.insert(p[0]);
    b.insert(1,3);
    
    assert(a!=b);
    assert(b!=a);

    a.insert(p[1]);
    b.insert(3,4);
    
    assert(a!=b);
    assert(b!=a);

    a.insert(p[2]);
    b.insert(3,4);
    
    assert(a!=b);
    assert(b!=a);
    assert(!(a!=c));
    assert(!(c!=a));

#elif TEST==31
    ScatterData<T> a("[(1,2)]");
    ScatterData<T> b("[(1,2),(3,4),(5,6)]");
    stringstream ss2(" [ ( 1 , 2 ) , ( 3 , 4 ) , ( 5 , 6 ) ] ");
    ss2 >> a;
    assert(a==b);

#elif TEST==3
    ScatterData<T> a("[(1,2),(3,4),(5,6)]");
    typename ScatterData<T>::Point p[3] = {
        typename ScatterData<T>::Point(1,2),
        typename ScatterData<T>::Point(3,4),
        typename ScatterData<T>::Point(5,6) };
    ScatterData<T> b(p,3);
    ScatterData<T> c;
    
    assert(a==b);
    stringstream ss(" [ ( 1 , 2 ) , ( 3 , 4 ) , ( 5 , 6 ) ] ");
    ss >> c;
    assert(c==b);
    
    stringstream ss2(" [ ( 1 , 2 ) , ( 3 , 4 ) , ( 5 , 6 ) ] ");
    ss2 >> a;
    assert(a==b);

#elif TEST==4
    ScatterData<T> a("[(1,2),(3,4),(5,6)]");
    typename ScatterData<T>::Point p[3] = {
        typename ScatterData<T>::Point(1,2),
        typename ScatterData<T>::Point(3,4),
        typename ScatterData<T>::Point(5,6) };
    ScatterData<T> b(p,3);

    assert(a==b);
    stringstream ss;
    ss << a;
    assert(ss.str()=="[(1,2),(3,4),(5,6)]");

#elif TEST==5
    ScatterData<T> a("[(1,2),(3,4),(5,6)]");
    ScatterData<T> b(a);
    ScatterData<T> c;
    
    assert(a.count() == 3);
    assert(b.count() == 3);
    assert(c.count() == 0);
    
    assert(a.count(1,2) == 1);
    assert(b.count(1,2) == 1);
    assert(c.count(1,2) == 0);
    
    assert(a.count(4,5) == 0);
    assert(b.count(4,5) == 0);
    assert(c.count(4,5) == 0);
    
    assert(a.count(5,6) == 1);
    assert(b.count(5,6) == 1);
    assert(c.count(5,6) == 0);
    
    c = a + b;
    
    assert(a.count() == 3);
    assert(b.count() == 3);
    assert(c.count() == 6);
    
    assert(a.count(1,2) == 1);
    assert(b.count(1,2) == 1);
    assert(c.count(1,2) == 2);
    
    assert(a.count(4,5) == 0);
    assert(b.count(4,5) == 0);
    assert(c.count(4,5) == 0);
    
    assert(a.count(5,6) == 1);
    assert(b.count(5,6) == 1);
    assert(c.count(5,6) == 2);
    
#elif TEST==6
    ScatterData<T> a("[(1,2),(3,4),(5,6)]");
    ScatterData<T> b(a);
    ScatterData<T> c;
    
    c += a;
    b += c;

    assert(a==ScatterData<T>("[(1,2),(3,4),(5,6)]"));
    assert(c==a);
    assert(b==ScatterData<T>("[(1,2),(3,4),(5,6),(1,2),(3,4),(5,6)]"));
    
#elif TEST==7
    ScatterData<T> a("[(1,2),(3,4),(5,6)]");
    typename ScatterData<T>::Point p(7,8);
    ScatterData<T> c;
    
    c = p+a;
    assert(c==ScatterData<T>("[(7,8),(1,2),(3,4),(5,6)]"));
    
    c = a+p;
    assert(c==ScatterData<T>("[(1,2),(3,4),(5,6),(7,8)]"));
    
    a += p;
    assert(c==a);
    
#elif TEST==81
    ScatterData<T> a("[(1,2),(3,4),(5,6)]");

    a[0].x = 7;
    a[1].y = 9;
    
    assert(a==ScatterData<T>("[(7,2),(3,9),(5,6)]"));

#elif TEST==8
    ScatterData<T> a("[(1,2),(3,4),(5,6)]");

    assert(a[0].x == 1);
    assert(a[0].y == 2);
    assert(a[1].x == 3);
    assert(a[1].y == 4);
    assert(a[2].x == 5);
    assert(a[2].y == 6);

    a[0].x = 7;
    a[1].y = 9;
    
    assert(a==ScatterData<T>("[(7,2),(3,9),(5,6)]"));

#elif TEST==9
    ScatterData<T> a("[(1,2),(3,4),(5,6),(1,2),(3,4),(5,6),(1,2),(3,4),(5,6),(1,2),(3,4),(5,6)]");

    assert(a.count(1,2)==4);
    assert(a.count(3,4)==4);
    assert(a.count(5,6)==4);
    assert(a.count(7,8)==0);
    assert(a.count()==12);
    
    a.remove(1,2);
    assert(a.count(1,2)==0);
    assert(a.count(3,4)==4);
    assert(a.count(5,6)==4);
    assert(a.count(7,8)==0);
    assert(a.count()==8);
    
    a.remove(typename ScatterData<T>::Point(1,2));
    assert(a.count(1,2)==0);
    assert(a.count(3,4)==4);
    assert(a.count(5,6)==4);
    assert(a.count(7,8)==0);
    assert(a.count()==8);
    
    a.remove(typename ScatterData<T>::Point(5,6));
    assert(a.count(typename ScatterData<T>::Point(1,2))==0);
    assert(a.count(typename ScatterData<T>::Point(3,4))==4);
    assert(a.count(typename ScatterData<T>::Point(5,6))==0);
    assert(a.count(typename ScatterData<T>::Point(7,8))==0);
    assert(a.count()==4);
    
#elif TEST==10
    ScatterData<T> a("[(1,2),(3,4),(5,6),(1,2),(3,4),(5,6),(1,2),(3,4),(5,6),(1,2),(3,4),(5,6)]");

    assert(a.topLeft().x == 1);
    assert(a.topLeft().y == 6);
    assert(a.bottomRight().x == 5);
    assert(a.bottomRight().y == 2);
    
    a.insert(-100000000,-20000000);
    assert(a.topLeft().x == -100000000);
    assert(a.topLeft().y == 6);
    assert(a.bottomRight().x == 5);
    assert(a.bottomRight().y == -20000000);
#else
    #error TEST must be defined (1..10)
#endif
}

int main()
{
    DWORD dwMode = SetErrorMode(SEM_NOGPFAULTERRORBOX);
    SetErrorMode(dwMode | SEM_NOGPFAULTERRORBOX);
    
    test<int>();
    test<double>();
    test<long>();
}
