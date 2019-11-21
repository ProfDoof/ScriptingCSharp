#include <iostream>
using namespace std;
class IntegerQueue{ 
  public:   
    IntegerQueue() { queue=new int[max=10]; in=out=0; }		
    bool empty() const { return size()==0; }
    int size() const { return in-out; }
    int pop() { return queue[out++]; }
    void push (int n);

  private:  
    int *queue;
    int max;
    int in;
    int out;
};
