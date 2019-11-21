//IntegerSet.h

class IntegerSet{ 
  public:   
    IntegerSet();	
    ~IntegerSet();	
    bool contains( int n );	
    void insert( int n );	
    void remove( int n );	
    bool empty();		
    int size();	
  
  private:  
    int * a;		
    int num;
};
