#include <iostream>
using namespace std;

int main()
{
	int a, b;
	cout << "This program determines the relationship between two input numbers." << endl;
	cout << "Enter the first integer: "; cin >> a;
	cout << "Enter the second integer: "; cin >> b;
	
	if( a > b )
		cout << a << " is greater than " << b << endl;
	else
		cout << a << " is less than " << b << endl;
}


