#include "IntegerStack.h"

void IntegerStack::push(int n)
{
    if (tail >= capacity) {
        capacity *= 2;
        int* s = new int[capacity];

        for (int i = 0; i < size(); ++i)
            s[i] = stack[i];

        // stack should be removed with delete [] here
        stack = s;
    }

    stack[tail++] = n;
}
