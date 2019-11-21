//
#include <cassert>
#include <cstddef>
#include <cmath>

template <class T>
class TupleData {
public:
    TupleData(int n);
    TupleData(const TupleData &other);
    TupleData(const T *list,int n);
    ~TupleData()                               { if (items) delete [] items; }
    
    T &         operator[](int i)               { return items[i]; }
    const T &   operator[](int i)       const   { return items[i]; }
    
    int         size()                  const   { return cardinality; }
    int         useCount()              const   { return uses; }
    void        incrementUse()                  { uses++; }
    void        decrementUse()                  { uses--; if (uses==0) delete this; }
    
private:
    T *         create_copy(int target,const T *other) const;
    
    int         cardinality;
    int         uses;
    T *         items;
};


template <class T>
class Tuple {
public:
    Tuple(int n)               : data(new TupleData<T>(n))    { /*cout << "dfltctor: " << data << "\n";*/ data->incrementUse(); }
    Tuple(const Tuple &other)  : data(other.data)             { /*cout << "copyctor: " << data << "\n";*/ data->incrementUse(); }
    Tuple(const T *p,int n)    : data(new TupleData<T>(p,n))  { data->incrementUse(); }
    ~Tuple()                                                  { data->decrementUse(); }
    
    const Tuple &   operator=(const Tuple &other);
    T &             operator[](int i);
    const T &       operator[](int i)              const;
    bool            operator==(const Tuple &other) const;
    bool            operator!=(const Tuple &other) const   { return !(*this==other); }
    
    int             size()                         const   { return data->size(); }
    T               magnitude()                    const   { return sqrt((*this) * (*this)); }
    
    #ifdef DEBUG
        TupleData<T> & implementation()            const   { return *data; }
    #endif

private:
    static T        dummy;
    TupleData<T> * data;
};

template <class T>       Tuple<T>   operator+ (const Tuple<T> & a,const Tuple<T> & b);
template <class T> const Tuple<T> & operator+=(      Tuple<T> & a,const Tuple<T> & b) { return a = (a+b); }
template <class T>       T          operator* (const Tuple<T> & a,const Tuple<T> & b);
template <class T>       Tuple<T>   operator* (const Tuple<T> & a,const T &         b);
template <class T>       Tuple<T>   operator* (const T &         a,const Tuple<T> & b) { return b * a; }
template <class T> const Tuple<T> & operator*=(      Tuple<T> & a,const T &         b);

// -------------------------- implementation below here ------------------------

// default construction: create an empty ScatterData
template <class T> inline TupleData<T>::TupleData(int n) 
: cardinality(n), 
  uses(0), 
  items(new T[n])
{
    for (int i=0; i<n; i++)
        items[i] = 0;
}

// copy constructor: duplicate state of other ScatterData
template <class T> inline TupleData<T>::TupleData(const TupleData<T> &other) 
: cardinality(other.cardinality), 
  uses(0), 
  items(create_copy(other.cardinality,other.items))
{
}

// constructor: create from an array of Y
template <class T> inline TupleData<T>::TupleData(const T array[],int n) 
: cardinality(n), 
  uses(0), 
  items(create_copy(n,array))
{
    assert(n>=0);
    assert(array || n==0);
}

// creates and returns pointer to target items
// and copies n items from *other
template <class T> inline T * TupleData<T>::create_copy(int target,const T *other) const 
{
    assert(target>=0);
    assert(other || target==0);
    
    if (target <= 0)
        return NULL;
    T * p = new T[target];
    for (int i=0; i<target; i++)
        p[i] = other[i];

    return p;
}

template <class T> T Tuple<T>::dummy = 0;

// assignment operator (shallow copy)
template <class T> inline const Tuple<T> & Tuple<T>::operator=(const Tuple &other)
{
    if (this != &other) {
        data->decrementUse();
        data = other.data;
        data->incrementUse();
    }
    return *this;
}

template <class T> inline const T & Tuple<T>::operator[](int i) const
{
    if (i<0 || i>=data->size()) {
        dummy = 0;
        return dummy;
    }
    return (*data)[i];
}

template <class T> inline T & Tuple<T>::operator[](int i)
{
    if (i<0 || i>=data->size()) {
        dummy = 0;
        return dummy;
    }
    if (data->useCount() > 1) {
        TupleData<T> * temp = new TupleData<T>(*data);
        //cout << "copyonwr: " << temp << "\n"; 
        data->decrementUse();
        data = temp;
        data->incrementUse();
    }
    return (*data)[i];
}

template <class T> inline bool Tuple<T>::operator==(const Tuple &other) const
{
    int m = size()>other.size() ? size() : other.size();
    for (int i=0; i<m; i++)
        if ((*this)[i] != other[i])
            return false;
    return true;
}

template <class T>       Tuple<T>   operator+ (const Tuple<T> & a,const Tuple<T> & b)
{
    int m = a.size()>b.size() ? a.size() : b.size();
    Tuple<T> r(m);
    for (int i=0; i<m; i++)
        r[i] = a[i]+b[i];
    return r;
}


template <class T> T operator*(const Tuple<T> & a,const Tuple<T> & b)
{
    T total = 0;
    int m = a.size()<b.size() ? a.size() : b.size();
    for (int i=0; i<m; i++)
        total += a[i] * b[i];
    return total;
}

template <class T> Tuple<T> operator* (const Tuple<T> & a,const T & b)
{
    Tuple<T> r(a);
    for (int i=0; i<r.size(); i++)
        r[i] *= b;
    return r;
}

template <class T> const Tuple<T> & operator*=(Tuple<T> & a,const T & b)
{
    for (int i=0; i<a.size(); i++)
        a[i] *= b;
    return a;    
}
