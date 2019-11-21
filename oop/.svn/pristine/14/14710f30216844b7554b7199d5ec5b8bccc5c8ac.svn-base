#include <iostream>
#include "IntegerQueue.h"

// Sets all variables to initial values, dynamically allocates an memory for "data"
IntegerQueue::IntegerQueue()
{
    arSize = 2;
    data = new int[arSize];
    tail = head = count = 0;
}

std::ostream& operator<<(std::ostream& output, const IntegerQueue q)
{
    output<<'{';
    int i = 0;

    // Dumping the first value from "q" into "output"
    if(q.count != 0)
        output<<q.data[(q.head+i++)%q.arSize];

    // Dumping the rest of the values and commas to separate them
    while(i < q.count)
        output<<','<<q.data[(q.head+i++)%q.arSize];

    output<<'}';
    return output;
}

IntegerQueue::IntegerQueue(const IntegerQueue &q2)
{
    // Duplicating the data members from "q2"
    arSize = q2.arSize;
    data = new int[q2.arSize];
    head = tail = count = 0;

    // Copying queue values over to the new IntegerQueue
    for(int i = 0; i < q2.count; i++)
        push(q2.data[(q2.head+i)%q2.arSize]);
}

// Returns the "index"th oldest value in the IntegerQueue
int IntegerQueue::operator[]( const int index)
{
    return data[(head+index)%arSize];
}

// Deallocates dynamically allocated memory
IntegerQueue::~IntegerQueue()
{
    delete [] data;
}

// Returns the number of values in the integerQueue
int IntegerQueue::size() const
{
    return count;
}

// IntegerQueue is empty if "count" is equal to zero
bool IntegerQueue::empty() const
{
    return count == 0;
}

void IntegerQueue::push(int value)
{
    // Incrementing counter and expanding the reallocating contents to new array if necessary
    count++;
    if (count == arSize)
    {
        int* A = new int[arSize *= 2];
        for(int i = 0; i < count; i++)
            A[i] = data[i];
        delete [] data;
        data = A;
    }
    // Wrapping tail if necessary
    tail %= arSize;

    // Placing "value" in the proper index and incrementing "tail"
    data[tail++] = value;
}

int IntegerQueue::pop()
{
    // Decrementing value counter and wrapping the head if necessary
    count--;
    head %= arSize;

    // Returns the oldest value in the IntegerQueue sets head accordingly
    return data[head++];
}

IntegerQueue IntegerQueue::operator=(const IntegerQueue q2)
{
    // Resetting member data
    delete [] data;
    data = new int[q2.arSize];
    count = head = tail = 0;

    // Duplicating contents of "q2" into "*this"
    for(int i = 0; i < q2.count; i++)
        push(q2.data[(q2.head+i)%q2.arSize]);
    return *this;
}

