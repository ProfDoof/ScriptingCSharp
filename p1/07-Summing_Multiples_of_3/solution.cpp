#include <iostream>
using namespace std;

int sum3s( int n )
{
	if( n <= 0 )
		return 0;
	n = 3 * ( n / 3 );
	return n + sum3s(n-3);
}

int main()
{
	int number;
	
	cout << "Enter number: ";
	cin >> number;
	
	cout << endl << "The sum is " << sum3s(number) << ".\n";
}

