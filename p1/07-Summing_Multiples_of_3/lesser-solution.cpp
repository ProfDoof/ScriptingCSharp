#include <iostream>
using namespace std;

int sum3s( int n )
{
	if( n <= 0 )
		return 0;
	else if( n % 3 == 0 )
		return n + sum3s(n-1);
	else
		return sum3s(n-1);
}

int main()
{
	int number;
	
	cout << "Enter number: ";
	cin >> number;
	
	cout << endl << "The sum is " << sum3s(number) << ".\n";
}


