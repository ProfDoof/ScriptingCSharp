#include "IntegerSet.h"

  IntegerSet::IntegerSet(){
    a = new int[num];   	
  }
  
  //returns true iff n is one of the ints contained in array 'a', else returns false
  bool IntegerSet::contains(int n){  
    for( int i=0; i<num; i++ ){	
      if( a[i] == n )	  
		return true;
    }	
    return false;	
  }

  //adds n to the set of ints contained in array 'a'
  void IntegerSet::insert(int n){
  
    int *temp;			// Declare a new array with one more element
    temp = new int[num+1];	// than has the IntegerSet object's array 'a'.	
    for(int i=0; i<num; i++)	// Copy 'a' into 'temp'.
      temp[i] = a[i];		// <-'	  
    temp[num] = n;		// Assign passed int 'n' to the last element of 'temp'.	
    num++;			// Increment 'size'.	
    delete[] a;			// Delete old array 'a'.	
    int *a;			// Declare a new int pointer 'a'	
    a=temp;			// that points to what temp points to.	    
  }

  //removes n from the set of ints contained in array 'a'
  void IntegerSet::remove(int n){  
	if( contains(n) ){	
		for( int i=0; i<num; i++ ){		
			if( a[i] == n ){			
				a[i] == a[ num-1 ];	//cut off last element of array 'a'
				num--;	
			}
		}
	int *temp;			// Declare a new array 
    	temp = new int[num];			
    	for(int i=0; i<num; i++)	// Copy 'a' into 'temp'.
     	  temp[i] = a[i];		// <-'	  
    	delete[] a;			// Delete old array 'a'.	
    	int *a;				// Declare a new int pointer 'a'	
    	a=temp;
	}      
  }

  //returns true iff the set is empty
  bool IntegerSet::empty(){  
    if( num < 1 )	
      return true;	  
  }

  //returns the cardinality of the set
  int IntegerSet::size(){
    return num;
  }

