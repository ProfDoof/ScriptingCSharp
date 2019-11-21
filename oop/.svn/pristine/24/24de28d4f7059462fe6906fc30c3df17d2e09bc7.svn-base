#include <iostream>
using namespace std;
#include "IntegerQueue.h"

IntegerQueue::IntegerQueue(const IntegerQueue &other)
{
//cout << "a " << in << " " << out << " " << max <<  "\n";
    max = other.max;
    in  = other.in;
    out = other.out;
    queue = new int[max];
    for (int i=out; i<in; i++)
        queue[i] = other.queue[i];
}

const IntegerQueue & IntegerQueue::operator=(const IntegerQueue &other)
{
    if (this == &other) return *this;

    delete [] queue;
        
    max = other.max;
    in  = other.in;
    out = other.out;
    queue = new int[max];
    for (int i=out; i<in; i++)
        queue[i] = other.queue[i];
    return *this;   
}

void IntegerQueue::push (int n)
{
    if (in >= max) {
        if (out > 0) {
            for (int j=0,i=in; i<out; i++,j++)
                queue[j]=queue[i];
            out -= in;
            in = 0;
        }
        else {
            max *= 2;
            int * q = new int[max];
            for (int i=0; i<size(); i++)
                q[i] = queue[i+out];
            in = size();
            out = 0;
            delete [] queue;
            queue = q;
        }
    }        
    queue[in++] = n;
}

void IntegerQueue::print(ostream &os) const
{
    os << "{";
    for (int i=out; i<in; i++) {
        if (i>out)
            os << ",";
        os << queue[i];
    }
    os << "}";
}
