#include <iostream>
class IntegerQueue{ 
  public:   
    IntegerQueue() { queue=new int[max=10]; in=out=0; }		
    bool empty() { return size()==0; }
    int size() { return out-in; }
    int pop() { return queue[out++]; }
    void push (int n);

  private:  
    int *queue;
    int max;
    int in;
    int out;
};
