#include <iostream>
using namespace std;

int pow(int x, int y)
{
	if( y >= 1 )
		return x * pow(x,y-1);
	return 1;
}

int main()
{
	int base;
	int power;
	
	cout << "This program calculates exponential values.\n";
	cout << "Enter the base:  "; cin >> base;
	cout << "Enter the power: "; cin >> power;
	cout << endl;
	cout << base << "^" << power << " = " << pow(base,power) << endl;
}

