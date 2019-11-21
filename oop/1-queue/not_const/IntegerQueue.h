class IntegerQueue{ 
  public:   
    IntegerQueue() { queue=new int[max=10]; in=out=0; }	
    ~IntegerQueue() { delete [] queue; }	
    bool empty() { return size()==0; }
    int size() { return in-out; }
    int pop() { return queue[out++]; }
    void push (int n);

  private:  
    int *queue;
    int max;
    int in;
    int out;
};
