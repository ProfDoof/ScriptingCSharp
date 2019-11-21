#include "IntegerStack.h"

void IntegerStack::push(int n)
{
    if (tail >= capacity) {
        capacity *= 2;
        int* s = new int[capacity];

        for (int i = 0; i < size(); ++i)
            s[i] = stack[i];

        delete [] stack;
        stack = s // no semicolon here - clearly a compile error
    }

    stack[tail++] = n;
}
