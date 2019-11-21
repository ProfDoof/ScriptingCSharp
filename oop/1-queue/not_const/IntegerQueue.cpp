#include "IntegerQueue.h"

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
