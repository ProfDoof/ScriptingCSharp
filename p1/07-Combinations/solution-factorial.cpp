#include <iostream>
using namespace std;


//overflows when calcualting factorial( n > 12 )
int Factorial(int n)
{
	if( n <= 1 )
		return 1;
	return n * Factorial(n-1);
}

int Combinations(int n, int k)
{
    return Factorial(n)/ ( Factorial(k)*Factorial(n-k) );
}

int main()
{
    int n, k;
    cout << "Enter an Integer: ";
    cin >> n;
    cout << "Enter another Integer: ";
    cin >> k;
    
    int output = Combinations(n,k);
    // cout << "Factorial(" << n << ") = " << Factorial(n) << endl;
    // cout << "Factorial(" << k << ") = " << Factorial(k) << endl; 
    
    cout << "Combinations(" << n << "," << k << ") = ";
    cout << output << endl;
}

