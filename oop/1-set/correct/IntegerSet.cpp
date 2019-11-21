#include "IntegerSet.h"

IntegerSet::IntegerSet()
{
	arraySize = 10;
  current = 0;
  set = new int[arraySize];
}

IntegerSet::~IntegerSet()
{
	delete [] set;
}

bool IntegerSet::contains(int i)
{
	int x = 0;
  while(set[x] != i && x != current)
		x++;

 	if(x == current)
	 	return false;
	else
		return true;
}

void IntegerSet::insert(int i)
{
  if(current == arraySize)
 	{
 		arraySize = arraySize*2;
   	int* temp = new int[arraySize];

   	for(int x = 0; x<arraySize/2; x++)
		{
			temp[x] = set[x];
		}

 		delete [] set;
 		set = temp;
	}

	if(!contains(i))
	{
		set[current] = i;
		current++;
	}
}

void IntegerSet::remove(int i)
{
	if(contains(i))
	{
		int x = 0;
		while(i != set[x])
		{
			x++;
		}

		while(x+1 != arraySize)
		{
			set[x] = set[x+1];
			x++;
		}
		current--;
	}
}

bool IntegerSet::empty()
{
  if(current == 0)
  	return true;
 	else
 		return false;
}
int IntegerSet::size()
{
  return current;
}
