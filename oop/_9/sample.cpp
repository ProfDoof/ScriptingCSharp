#include <cassert>
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
}