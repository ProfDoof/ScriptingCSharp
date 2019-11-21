class IntegerStack { 
  public:   
    IntegerStack() { stack = new int[capacity = 10]; tail = 0; }	
    ~IntegerStack() { delete [] stack; }	
    bool empty() { return size() == 0; }
    int size() { return tail; }
    int pop() { return stack[--tail]; }
    void push (int n);

  private:  
    int *stack;
    int capacity;
    int tail;
};
