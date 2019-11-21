#include <iostream>
using namespace std;

int fact(int n)
{
	if( n <= 1 )
		return 1;
	
	return n * fact(n-1);
}

int main()
{
	int f;
	cout << "This program calculates factorials.\n";
	cout << "Enter an integer: ";
	cin >> f;
	cout << endl;
	cout << f << "! = " << fact(f) << endl;
}

