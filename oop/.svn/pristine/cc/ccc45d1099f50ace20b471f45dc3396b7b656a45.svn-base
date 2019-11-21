#include <iostream>
INCLUDE_INTEGER_QUEUE

IntegerQueue dup(IntegerQueue a)
{
    IntegerQueue result;
    while (!a.empty()) {
        int x = a.pop();
        result.push(x);
        result.push(x);
    }
    return result;
}

void print(IntegerQueue *a,int n)
{
    for (int i=0; i<n; i++)
        std::cout << (a[i]) << std::endl;
}

int main()
{
    IntegerQueue q;
    for (int i=0; i<10; i++)
        q.push(i);

    IntegerQueue r = q;
    IntegerQueue s(q);
    IntegerQueue t;
    t = q;

    IntegerQueue a[10];

    for (int i=0; i<10; i++)
        for (int j=0; j<i; j++)
            a[i].push(j);

    s = dup(a[9]);
    t = dup(a[4]);
    a[7] = IntegerQueue();

    IntegerQueue b[8] = {q,r,s,t,a[1],a[3],dup(a[5])};

    std::cout << "q = " << q << std::endl;
    std::cout << "r = " << r << std::endl;
    std::cout << "s = " << s << std::endl;
    std::cout << "t = " << t << std::endl;

    print(a,10);
    print(b,8);
}
