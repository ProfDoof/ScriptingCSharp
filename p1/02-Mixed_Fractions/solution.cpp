// Mixed fractions

#include <iostream>
using namespace std;

int main()
{
    int num, den;
    cout << "Enter fraction: ";
    cin >> num >> den;
    
    cout << num/den << " " << num % den << "/" << den << endl;
}

