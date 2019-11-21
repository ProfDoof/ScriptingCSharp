#include <cstddef>
#include <iostream>

// A integer only wrapping queue, stored in a dynamically allocated array
class IntegerQueue
{
    private:
        int* data;      // The array in which the queue's values are stored
        int arSize;     // The size of array
        int head;       // Index from which the next value will be read (on Pop)
        int tail;       // Index at which the next value will be stored
        int count;      // The number of values in the IntegerQueue
    public:
        IntegerQueue(); // The constructor for the IntegerQueue
        ~IntegerQueue();// The destructor for the IntegerQueue
        int pop();      // Pops the oldest value out of the IntegerQueue
        int  size() const;        // Returns the size of the IntegerQueue
        bool empty() const;       // Returns whether or not the IntegerQueue is empty
        void push(int value);     // Pushes an integer into the IntegerQueue
        int operator[](int index);// Returns the "index"th oldest value in the IntegerQueue
        IntegerQueue(const IntegerQueue &q2);          // Constructor to construct off of a given IntegerQueue
        IntegerQueue operator=(const IntegerQueue q2); // Sets the IntegerQueue equal to the
        friend std::ostream& operator<<(std::ostream& output, const IntegerQueue q); // Outputs the values in the IntegerQueue in a bracketed comma-separated list
};













