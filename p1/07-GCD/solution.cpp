#include <iostream>
using namespace std;

// Greatest common divisor
int gcd(int m, int n)
{
	if( n == 0 )
		return m;
	return gcd(n, m % n);	
}          

int main()
{
	int a, b;
	cout << "This program calculates the greatest common divisor (GCD) for two integers.\n\n";

	cout << "Enter a number: "; cin >> a;
	cout << "Enter another: "; cin >> b;
	
	cout << "\nGCD = " << gcd(a,b) << endl;
}

