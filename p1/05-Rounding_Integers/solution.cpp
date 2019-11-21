#include <iostream>
using namespace std;

// Round number down to nearest multiple of unit
int round(int number, int unit)
{
    return (number - number % unit);
}

int main()
{
	int num;
	cout << "Enter an integer: "; cin >> num;
    cout << endl;
    cout << "Round to ten: " << round(num,10) << endl;
    cout << "Round to hundred: " << round(num,100) << endl;
    cout << "Round to thousand: " << round(num,1000) << endl;
	
}