class IntegerStack { 
  public:   
    IntegerStack() { stack = new int[capacity = 10]; tail = 0; }	
    bool empty() const { return size() == 0; }
    int size() const { return tail; }
    int pop() { return stack[--tail]; }
    void push (int n);

  private:  
    int *stack;
    int capacity;
    int tail;
};
