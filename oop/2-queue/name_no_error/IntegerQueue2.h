#include <iostream>

class IntegerQueue{
  public:
    IntegerQueue() { queue=new int[max=10]; in=out=0; }
    IntegerQueue(const IntegerQueue &other);
    const IntegerQueue & operator=(const IntegerQueue &other);
    ~IntegerQueue() { delete [] queue; }
    bool empty() const { return size()==0; }
    int size() const { return in-out; }
    int pop() { return queue[out++]; }
    void push (int n);
    void print(std::ostream &os) const;

  private:
    int *queue;
    int max;
    int in;
    int out;
};

inline std::ostream & operator<<(std::ostream &os,const IntegerQueue &q) {
    q.print(os);
    return os;
}
