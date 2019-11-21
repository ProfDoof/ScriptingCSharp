#include <cassert>
#include <cstdlib>
#include "IntegerSet.h"

int main()
{
   IntegerSet s;

   assert(s.empty());
   assert(!s.contains(3));
   assert(!s.contains(4));
   s.insert(3);
   assert(s.contains(3));
   assert(!s.contains(4));
   s.insert(4);
   assert(s.contains(3));
   assert(s.contains(4));
   s.remove(2);
   s.remove(3);
   assert(!s.contains(3));
   assert(s.contains(4));
   assert(s.size()==1);
   assert(!s.contains(3));

   IntegerSet x,y,z;
   for (int i=0; i<1000; i++) {
      x.insert(i);
      assert(x.size() == i+1);
      
      y.insert(i);
      assert(y.size()==1);
      y.remove(i);
      assert(y.empty());
      
      z.insert(std::rand());
      z.remove(std::rand());
    }
}
