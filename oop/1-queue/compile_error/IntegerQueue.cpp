#include "IntegerQueue.h"

void IntegerQueue::push (int n)
{
    if (in >= max) {
        if (out > 0) {
            for (int j=0,i=out; i<in; i++,j++)
                queue[j]=queue[i];
            out -= in;
            in = 0
        }
        else {
            int * q = new int[max*2];
            for (int i=0; i<out-in; i++)
                q[i] = queue[i+out];
            out -= in;
            in = 0;
            max *= 2;
        }
    }        
    queue[in++] = n;
}
