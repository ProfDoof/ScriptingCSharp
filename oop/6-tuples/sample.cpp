#include <cassert>
#include "Tuple.h"

Tuple<int> foo(Tuple<int> a,Tuple<int> b,Tuple<int> c)
{
   return a+b+c;     // allocation for 2 anonymous objects, deallocation for 1 of them
}

int main()
{
   Tuple<int> x(3);  // allocation of 3 ints
   assert(x.size() == 3);
   Tuple<int> y(5);  // allocation of 5 ints
   assert(y.size() == 5);
   x[0] = 2;         // no allocation, only one "user" of data
   x[1] = 3;
   x[2] = 7;
   y = x;            // deallocation of 5 ints, share 3 ints
   assert(y == x);
   Tuple<int> z(x);  // no allocation, another shared copy
   assert(y == x && y == z);
   z[3] = 9;         // no allocation, z[3] is undefined so its a no-op
   assert(z.size() == 3 && z == y);
   z[1] = 5;         // copy/split occurs before write to element 1
   assert(z != y);
   z += x;           // no allocation, z has its own copy
   assert(z[0] == 4 && z[1] == 8 && z[2] == 14);
   x = foo(x,y,x);   // no memory allocation should occur due to copy construction
   assert(x[0] == 6 && x[1] == 9 && x[2] == 21);
}
