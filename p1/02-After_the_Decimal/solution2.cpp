#include <iostream>
using namespace std;

double digitsAfter(double number)
{
    int x = (number);
    return number- x;
}

int main ()
{
    float y;
    cout << "Enter number: ";
    cin >> y;
    cout << endl;
    cout << "After the decimal: " << digitsAfter(y) << endl;
}
    
